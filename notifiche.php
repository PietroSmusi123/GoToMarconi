<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) die("Connessione fallita");

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}
$utente_id = $_SESSION['user_id'];

// Segna le notifiche come lette
$conn->query("UPDATE Prenotazione SET Notificata = 1 WHERE ID_Passeggero = $utente_id AND Stato IN ('Accettata', 'Rifiutata')");

// Richieste in attesa
$stmt = $conn->prepare("
	SELECT p.ID, v.ID AS ID_Viaggio, u.Nome, u.Cognome, v.Data, v.Partenza, v.Posti, p.Stato
    FROM Prenotazione p
    JOIN Viaggio v ON v.ID = p.ID_Viaggio
    JOIN Utente u ON u.ID = v.ID_Autista
    WHERE p.ID_Passeggero = ? AND p.Stato = 'In attesa'
    ORDER BY v.Data DESC
");
$stmt->bind_param("i", $utente_id);
$stmt->execute();
$in_attesa = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Risposte (accettate/rifiutate)
$stmt = $conn->prepare("
    SELECT p.ID, v.ID AS ID_Viaggio, u.Nome, u.Cognome, v.Data, v.Partenza, v.Posti, p.Stato
    FROM Prenotazione p
    JOIN Viaggio v ON v.ID = p.ID_Viaggio
    JOIN Utente u ON u.ID = v.ID_Autista
    WHERE p.ID_Passeggero = ? AND p.Stato IN ('Accettata', 'Rifiutata')
    ORDER BY v.Data DESC
");
$stmt->bind_param("i", $utente_id);
$stmt->execute();
$risposte = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Le tue notifiche</title>
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

    .navbar-left {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .navbar img.logo {
      height: 50px;
    }

    .dashboard-button {
      text-decoration: none;
      color: white;
      background-color: #ffc107;
      padding: 8px 15px;
      border-radius: 8px;
      font-weight: bold;
    }

    .main {
      display: flex;
      justify-content: center;
      padding: 60px 20px;
    }

    .container {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      width: 100%;
      max-width: 800px;
    }

    h2 {
      color: #2f6f75;
      margin-top: 30px;
    }

    .richiesta-box {
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      transition: background 0.3s;
    }

    .richiesta-box:hover {
      background-color: #f0f0f0;
    }

    .card-accettata {
      background-color: #2f6f75;
      color: white;
    }

    .card-rifiutata {
      background-color: #f8d7da;
      color: #721c24;
    }

    .no-data {
      text-align: center;
      color: #777;
      padding: 10px 0;
    }

    a.card-link {
      text-decoration: none;
      color: inherit;
      display: block;
    }

    hr {
      margin: 40px 0;
      border: none;
      border-top: 2px solid #ccc;
    }
  </style>
</head>
<body>

<div class="navbar">
  <div class="navbar-left">
    <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
    <a href="passeggero.php" class="dashboard-button">⬅ Torna alla dashboard</a>
  </div>
</div>

<div class="main">
  <div class="container">

    <h2>Risposte alle tue richieste</h2>
    <?php if (count($risposte) > 0): ?>
      <?php foreach ($risposte as $r): ?>
        <?php
          $classe_card = '';
          if ($r['Stato'] === 'Accettata') $classe_card = 'card-accettata';
          elseif ($r['Stato'] === 'Rifiutata') $classe_card = 'card-rifiutata';
        ?>
        <a href="dettagli_viaggio.php?id_viaggio=<?= $r['ID_Viaggio'] ?>" class="card-link">
          <div class="richiesta-box <?= $classe_card ?>">
            <strong><?= htmlspecialchars($r['Nome'] . ' ' . $r['Cognome']) ?></strong><br>
            Data: <?= htmlspecialchars($r['Data']) ?> – Posti: <?= htmlspecialchars($r['Posti']) ?><br>
            Stato: <?= htmlspecialchars($r['Stato']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-data">Nessuna risposta ricevuta.</p>
    <?php endif; ?>

    <hr>

    <h2>Richieste inviate</h2>
    <?php if (count($in_attesa) > 0): ?>
      <?php foreach ($in_attesa as $r): ?>
        <a href="dettagli_viaggio.php?id_viaggio=<?= $r['ID_Viaggio'] ?>" class="card-link">
          <div class="richiesta-box">
            <strong><?= htmlspecialchars($r['Nome'] . ' ' . $r['Cognome']) ?></strong><br>
            Data: <?= htmlspecialchars($r['Data']) ?> – Posti: <?= htmlspecialchars($r['Posti']) ?><br>
            Stato: In attesa
          </div>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-data">Nessuna richiesta inviata.</p>
    <?php endif; ?>

  </div>
</div>

</body>
</html>
