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



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['data'], $_POST['posti_disponibili'], $_POST['destinazioni'])) {
    $data_viaggio = $_POST['data'];
    $posti = intval($_POST['posti_disponibili']);
	$note = $_POST['note'] ?? '';
    $destinazioni = $_POST['destinazioni']; // 🔧 Mancava questa riga
	$partenza = $_POST['partenza'] ?? 'Casa → Lavoro';

    if (strtotime($data_viaggio) < strtotime(date("Y-m-d"))) {
        echo "<script>alert('Errore: la data non può essere nel passato!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO Viaggio (ID_Autista, Partenza, Data, Posti, Note) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $utente_loggato, $partenza, $data_viaggio, $posti, $note);

        if ($stmt->execute()) {
            $id_viaggio = $stmt->insert_id;

            // Salva le destinazioni selezionate
            $stmt_dest = $conn->prepare("INSERT INTO Viaggio_Destinazione (ID_Viaggio, Citta) VALUES (?, ?)");
            foreach ($destinazioni as $citta) {
                $stmt_dest->bind_param("is", $id_viaggio, $citta);
                $stmt_dest->execute();
            }
            $stmt_dest->close();

            echo "<script>alert('✅ Viaggio creato con successo!'); window.location.href = 'autista.php';</script>";
        } else {
            echo "<script>alert('❌ Errore nella creazione del viaggio.');</script>";
        }

        $stmt->close();
    }
}



// Recupera dati utente
$stmt = $conn->prepare("SELECT Nome, Cognome, Citta FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
$citta_utente = $utente['Citta'];
$stmt->close();

// Richieste in attesa
$richieste = [];
$stmt = $conn->prepare("
    SELECT p.ID as ID_Richiesta, u.Nome, u.Cognome
    FROM Prenotazione p
    JOIN Viaggio v ON v.ID = p.ID_Viaggio
    JOIN Utente u ON u.ID = p.ID_Passeggero
    WHERE v.ID_Autista = ? AND p.Stato = 'in_attesa'
    ORDER BY p.ID DESC
");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
while ($r = $result->fetch_assoc()) {
    $richieste[] = $r;
}
$stmt->close();

// Recupera posti disponibili da veicolo (posti auto - 1)
$stmt = $conn->prepare("SELECT Posti FROM Veicolo WHERE ID_Proprietario = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$auto = $result->fetch_assoc();
$stmt->close();

$posti_max = isset($auto['Posti']) ? max(1, $auto['Posti'] - 1) : 1;

// Viaggi effettuati
$viaggi_effettuati = [];
$stmt = $conn->prepare("SELECT Partenza, Data FROM Viaggio WHERE ID_Autista = ? ORDER BY Data DESC");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $viaggi_effettuati[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Area Autista</title>
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
	
	.sidebar {
	  width: 250px;
	  background: #00aaff;
	  color: white;
	  padding: 20px;
	  min-height: 150vh;
	  float: left;
	}

	.richiesta-box {
	  background: #0077aa;
	  padding: 10px;
	  margin-bottom: 10px;
	  border-radius: 10px;
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
      min-width: 200px;
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

    .container {
      flex-grow: 1;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      min-height: 600px;
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

    input, select, textarea {
      width: 100%;
      padding: 8px;
      margin-top: 8px;
      margin-bottom: 15px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
      background-color: #2f6f75;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }

    .note {
      font-size: 0.85em;
      color: #777;
      margin-top: -10px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<div class="navbar">
  <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
  <div style="display: flex; align-items: center; gap: 15px;">
    <a href="seleziona_ruolo.php" style="text-decoration: none; color: white; background-color: #ffc107; padding: 8px 15px; border-radius: 8px; font-weight: bold;">
      🔄 Cambia ruolo
    </a>
    <div class="menu-profilo">
      <button title="Profilo">👤</button>
      <div class="menu-profilo-content">
        <a href="profilo_autista.php?from=autista">👁️ Profilo</a>
        <a href="modifica_profilo.php?from=autista">✏️ Modifica profilo</a>
        <a href="modifica_auto.php">🚗 Inserisci/Modifica dettagli auto</a>
		<a href="viaggi_previsti_a.php">🧾 Viaggi previsti</a>
        <a href="Viaggi_effettuatiA.php">📋 Viaggi effettuati</a>
        <a href="logout.php" style="color: red;">🚪 Disconnetti</a>
      </div>
    </div>
  </div>
</div>

<div class="sidebar">
  <h3>Richieste ricevute</h3>
  <?php if (count($richieste) > 0): ?>
    <?php foreach ($richieste as $r): ?>
      <div class="richiesta-box">
        <a href="dettagli_richiesta.php?id_richiesta=<?= $r['ID_Richiesta'] ?>" style="color: white; text-decoration: none;">
          <?= htmlspecialchars($r['Nome'] . ' ' . $r['Cognome']) ?>
        </a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>Nessuna richiesta.</p>
  <?php endif; ?>
 <h3 style="margin-top: 40px;">Richieste gestite</h3>
<?php
while ($r = $result->fetch_assoc()) {
    $richieste_gestite[] = $r;
}
$stmt = $conn->prepare("
    SELECT u.ID AS PasseggeroID, u.Nome, u.Cognome, p.Stato
    FROM Prenotazione p
    JOIN Viaggio v ON v.ID = p.ID_Viaggio
    JOIN Utente u ON u.ID = p.ID_Passeggero
    WHERE v.ID_Autista = ? AND p.Stato IN ('accettata', 'rifiutata')
    ORDER BY p.ID DESC
");

$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$richieste_gestite = [];
while ($r = $result->fetch_assoc()) {
    $richieste_gestite[] = $r;
}
$stmt->close();
?>

<?php if (count($richieste_gestite) > 0): ?>
  <?php foreach ($richieste_gestite as $r): ?>
    <div class="richiesta-box" style="background: #005f87; cursor: default;">
      <?= htmlspecialchars($r['Nome'] . ' ' . $r['Cognome']) ?>
      <div style="font-size: 0.85em; margin-top: 4px;">
        <?php
$stato_clean = strtolower(trim($r['Stato']));
if ($stato_clean === 'accettata') {
    echo '✅ Accettata';
} elseif ($stato_clean === 'rifiutata') {
    echo '❌ Rifiutata';
} else {
    echo 'ℹ️ Stato sconosciuto';
}

?>
<a href="Chat.php?utente_id=<?= $r['PasseggeroID'] ?>&from=autista">💬 Chatta</a>

      </div>
    </div>
	
  <?php endforeach; ?>
<?php else: ?>
  <p>Nessuna richiesta gestita.</p>
<?php endif; ?>

  
</div>

<div class="main">
  <div class="container">
    <div class="topbar">
      <h2>Benvenuto, <?= htmlspecialchars($utente['Nome'] . ' ' . $utente['Cognome']) ?>!</h2>
    </div>

    <div class="box">
      <h3>Organizza un nuovo viaggio</h3>
      <form method="post" action="">
        <label>Data del viaggio:</label>
        <input type="date" name="data" required>

        <label>Posti disponibili:</label>
        <input type="number" name="posti_disponibili" value="<?= $posti_max ?>" min="1" max="<?= $posti_max ?>" required>
        <p class="note">Posti massimi disponibili: <?= $posti_max ?> (auto - 1)</p>

        <div style="display: flex; gap: 15px; align-items: start;">
  <div style="flex-grow: 1;">
    <label>Città disponibili (tieni Ctrl per selezioni multiple):</label>
    <select id="listaCitta" name="destinazioni[]" multiple size="8" required style="width: 100%;">
    <option value="Lari">Lari</option>
	<option value="Pontedera">Pontedera</option>
    <option value="Calcinaia">Calcinaia</option>
	<option value="Ponsacco">Ponsacco</option>
	<option value="Santa Maria a Monte">Santa Maria a Monte</option>
	<option value="Capannoli">Capannoli</option>
	<option value="Montopoli in Val d'Arno">Montopoli in Val d'Arno</option>
	<option value="Cascina">Cascina</option>
	<option value="Palaia">Palaia</option>
	<option value="Casciana Terme Lari">Casciana Terme Lari</option>
	<option value="Vicopisano">Vicopisano</option>
	<option value="Bientina">Bientina</option>
	<option value="Castelfranco di Sotto">Castelfranco di Sotto</option>
	<option value="Crespina Lorenzana">Crespina Lorenzana</option>
	<option value="Peccioli">Peccioli</option>
	<option value="San Miniato">San Miniato</option>
	<option value="Terricciola">Terricciola</option>
	<option value="Pisa">Pisa</option>
	<option value="San Giuliano Terme">San Giuliano Terme</option>
	<option value="Chianni">Chianni</option>
	<option value="Santa Luce">Santa Luce</option>
	<option value="Montaione">Montaione</option>
      <!-- Aggiungi tutte le città che desideri -->
    </select>
  </div>
  <div>
    <label>🔎 Cerca:</label>
    <input type="text" id="filtroCitta" placeholder="Cerca città..." style="width: 160px; padding: 6px; border-radius: 6px; border: 1px solid #ccc;">
  </div>
</div>	
	<label>Tipo di viaggio:</label>
	<div style="display: flex; gap: 20px; margin-top: 8px; margin-bottom: 15px;">
	  <label style="display: flex; align-items: center; gap: 6px;">
		<input type="radio" name="partenza" value="Casa → Lavoro" required>
		Andata
	  </label>
	  <label style="display: flex; align-items: center; gap: 6px;">
		<input type="radio" name="partenza" value="Lavoro → Casa" required>
		Ritorno
	  </label>
	</div>

        <label>Note aggiuntive:</label>
        <textarea name="note" rows="3" placeholder="..."></textarea>

        <button type="submit">Crea viaggio</button>
      </form>
    </div>
  </div>
</div>
<script>
  document.getElementById('filtroCitta').addEventListener('keyup', function () {
    const filtro = this.value.toLowerCase();
    const opzioni = document.getElementById('listaCitta').options;
    for (let i = 0; i < opzioni.length; i++) {
      const testo = opzioni[i].text.toLowerCase();
      opzioni[i].style.display = testo.includes(filtro) ? 'block' : 'none';
    }
  });
</script>

</body>
</html>
