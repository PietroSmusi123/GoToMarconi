<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}
$utente_loggato = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT Nome, Cognome, Citta FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
$citta_utente = $utente['Citta'];
$stmt->close();

// Recupera i viaggi futuri accettati dal passeggero
$viaggi_previsti = [];
$stmt = $conn->prepare("
	SELECT v.Data, v.Partenza, v.Note, u.Nome, u.Cognome, u.ID AS ID_Autista
    FROM Prenotazione p
    JOIN Viaggio v ON v.ID = p.ID_Viaggio
    JOIN Utente u ON u.ID = v.ID_Autista
    WHERE p.ID_Passeggero = ? AND p.Stato = 'Accettata' AND v.Data >= CURDATE()
    ORDER BY v.Data ASC
");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$viaggi_previsti = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// Recupera viaggi passati del passeggero
$viaggi_effettuati = [];
$stmt = $conn->prepare("
    SELECT v.Data, v.Partenza, u.Nome, u.Cognome
    FROM Prenotazione p
    JOIN Viaggio v ON v.ID = p.ID_Viaggio
    JOIN Utente u ON u.ID = v.ID_Autista
    WHERE p.ID_Passeggero = ? AND p.Stato = 'Accettata' AND v.Data < CURDATE()
    ORDER BY v.Data DESC
");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $viaggi_effettuati[] = $row;
}
$stmt->close();


$conteggio_notifiche = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS n FROM Prenotazione WHERE ID_Passeggero = ? AND Stato IN ('Accettata', 'Rifiutata') AND Notificata = 0");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $conteggio_notifiche = (int) $row['n'];
}
$stmt->close();


// Viaggi disponibili nella stessa città del passeggero (senza escludere viaggi multipli per autista)
$autisti_disponibili = [];
$stmt = $conn->prepare("
    SELECT u.ID, u.Nome, u.Cognome, v.ID AS ID_Viaggio,
           COALESCE(AVG(r.Voto), 0) AS MediaVoto, COUNT(r.ID) AS TotaleRecensioni
    FROM Viaggio v
    JOIN Utente u ON u.ID = v.ID_Autista
    JOIN Viaggio_Destinazione vd ON vd.ID_Viaggio = v.ID
    LEFT JOIN Recensione r ON r.ID_Utente = u.ID
    WHERE vd.Citta = ?
      AND u.ID != ?
      AND v.Data >= CURDATE()
      AND NOT EXISTS (
			SELECT 1 FROM Prenotazione p
			WHERE p.ID_Viaggio = v.ID AND p.ID_Passeggero = ?
		)
    GROUP BY u.ID, v.ID
    ORDER BY v.Data ASC
");


$stmt->bind_param("sii", $citta_utente, $utente_loggato, $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $autisti_disponibili[] = $row;
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Area Passeggero</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f4f4f4;
    }
    .navbar {
      background-color: #2f6f75;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .navbar img.logo {
      height: 50px;
    }
    .menu-profilo {
      position: relative;
      display: inline-block;
    }
    .menu-profilo button {
      background-color: #5B8FB9;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 50%;
      cursor: pointer;
    }
    .menu-profilo-content {
      display: none;
      position: absolute;
      right: 0;
      background-color: white;
      min-width: 180px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      z-index: 1;
      border-radius: 10px;
      overflow: hidden;
    }
    .menu-profilo-content a {
      color: #333;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
    }
    .menu-profilo-content a:hover {
      background-color: #f0f0f0;
    }
    .menu-profilo:hover .menu-profilo-content {
      display: block;
    }
    .main {
      display: flex;
      margin: 40px;
    }
    .sidebar {
       width: 250px;
       background: #00aaff;
       color: white;
       padding: 20px;
       min-height: 900px; /* <-- aggiunto */
       border-radius: 10px;
       margin-right: 40px;
     } 
    .autista-card {
      background: #0077aa;
      border-radius: 10px;
      padding: 10px;
      margin-bottom: 10px;
    }
    .autista-card a {
      color: white;
      font-weight: bold;
      text-decoration: none;
    }
    .container {
      flex-grow: 1;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .box {
      margin-top: 20px;
      background: #f8f8f8;
      padding: 20px;
      border-radius: 10px;
    }
  </style>
</head>
<body>

<div class="navbar">
  <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
  <div style="display: flex; align-items: center; gap: 10px;">
    <a href="seleziona_ruolo.php" style="text-decoration: none; color: white; background-color: #ffc107; padding: 8px 15px; border-radius: 8px; font-weight: bold;">
      🔄 Cambia ruolo
    </a>
    <div class="menu-profilo">
      <button title="Profilo">👤</button>
      <div class="menu-profilo-content">
        <a href="profilo_autista.php?from=passeggero">👁️ Profilo</a>
        <a href="modifica_profilo.php?from=passeggero">✏️ Modifica profilo</a>
		<a href="notifiche.php" style="position: relative;">
			🔔 Notifiche
			<?php if ($conteggio_notifiche > 0): ?>
				<span style="position: absolute; top: 13px; right: 20px; background: red; color: white; font-size: 12px; padding: 2px 6px; border-radius: 50%;">
			<?= $conteggio_notifiche ?>
				</span>
		  <?php endif; ?>
		</a>
		<a href="viaggi_previsti_p.php">🧾 Viaggi Previsti</a>
		<a href="Viaggi_effettuatiP.php">📋 Viaggi Effettuati</a>
        <a href="logout.php" style="color: red;">🚪 Disconnetti</a>
      </div>
    </div>
  </div>
</div>


<div class="main">
  <div class="sidebar">
    <h3>Utenti che passano per la tua città</h3>
    <?php foreach ($autisti_disponibili as $a): ?>
	  <div class="autista-card">
		<a href="dettagli_viaggio.php?id_viaggio=<?= $a['ID_Viaggio'] ?>" style="color: white; font-weight: bold; text-decoration: none;">
		  <?= htmlspecialchars($a['Nome'] . ' ' . $a['Cognome']) ?>
		</a><br>
		⭐ <?= number_format($a['MediaVoto'], 1) ?> / 5
		<?php if ($a['TotaleRecensioni'] == 0): ?>
		  <br><small>(nessuna recensione)</small>
		<?php endif; ?>

	  </div>
	<?php endforeach; ?>

  </div>

  <div class="container">
    <div class="topbar">
      <h2>Benvenuto, <?= htmlspecialchars($utente['Nome'] . ' ' . $utente['Cognome']) ?>!</h2>
    </div>

    <div class="box">
	  <h3>I tuoi prossimi viaggi</h3>
	  <?php if (count($viaggi_previsti) > 0): ?>
		<ul>
		  <?php foreach ($viaggi_previsti as $v): ?>
			<li>
			  <strong><?= htmlspecialchars(date('Y-m-d', strtotime($v['Data']))) ?></strong> – 
			  <?= htmlspecialchars($v['Partenza']) ?> – 
			  Autista: <?= htmlspecialchars($v['Nome'] . ' ' . $v['Cognome']) ?><br>
			  Note: <?= htmlspecialchars($v['Note'] ?: 'Nessuna') ?>
				<a href="Chat.php?utente_id=<?= $v['ID_Autista'] ?>&from=passeggero">💬 Chatta</a>
			</li>
		  <?php endforeach; ?>
		</ul>
	  <?php else: ?>
		<p>Non hai viaggi confermati al momento.</p>
	  <?php endif; ?>
	</div>


</div>

</body>
</html>
