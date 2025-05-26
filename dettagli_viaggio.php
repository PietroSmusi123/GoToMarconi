<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
if (!isset($_SESSION['user_id']) || !isset($_GET['id_viaggio'])) {
    header("Location: passeggero.php");
    exit();
}
$id_viaggio = intval($_GET['id_viaggio']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
	SELECT v.ID_Autista, v.Partenza, v.Data, v.Posti, v.Note, u.Nome, u.Cognome, u.Email, u.Telefono
    FROM Viaggio v
    JOIN Utente u ON u.ID = v.ID_Autista
    WHERE v.ID = ?
");
$stmt->bind_param("i", $id_viaggio);
$stmt->execute();
$result = $stmt->get_result();
$dettagli = $result->fetch_assoc();
$stmt->close();

if (!$dettagli) {
    echo "<h2>Viaggio non trovato.</h2>";
    exit;
}

$stmt = $conn->prepare("
    SELECT Stato
    FROM Prenotazione
    WHERE ID_Viaggio = ? AND ID_Passeggero = ?
");
$stmt->bind_param("ii", $id_viaggio, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$prenotazione = $result->fetch_assoc();
$stmt->close();

$mostra_auto = ($prenotazione && strtolower($prenotazione['Stato']) === 'accettata');

$richiesta_esistente = ($prenotazione !== null);

if ($mostra_auto) {
    $stmt = $conn->prepare("
        SELECT Marca, Modello, Colore, Targa, Posti
        FROM Veicolo
        WHERE ID_Proprietario = (
            SELECT ID_Autista FROM Viaggio WHERE ID = ?
        )
    ");
    $stmt->bind_param("i", $id_viaggio);
    $stmt->execute();
    $result = $stmt->get_result();
    $veicolo = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Dettagli Viaggio</title>
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

    .card {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
      width: 100%;
      max-width: 600px;
    }

    h2 {
      text-align: center;
      color: #2f6f75;
      margin-bottom: 20px;
    }

    h3 {
      color: #444;
      border-bottom: 1px solid #ddd;
      padding-bottom: 5px;
    }

    p {
      margin: 8px 0;
    }

    button {
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }

    button:hover {
      background-color: #0056b3;
    }

    .info {
      text-align: center;
      color: #555;
      font-style: italic;
      margin-top: 20px;
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
  <div class="card">
    <h2>Dettagli Autista</h2>
    <p><strong>Nome:</strong> <?= htmlspecialchars($dettagli['Nome']) ?></p>
    <p><strong>Cognome:</strong> <?= htmlspecialchars($dettagli['Cognome']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($dettagli['Email']) ?></p>
    <p><strong>Telefono:</strong> <?= htmlspecialchars($dettagli['Telefono']) ?></p>

    <hr>
    <h3>Dettagli Viaggio</h3>
    <p><strong>Partenza:</strong> <?= htmlspecialchars($dettagli['Partenza']) ?></p>
    <p><strong>Data:</strong> <?= htmlspecialchars($dettagli['Data']) ?></p>
    <p><strong>Posti Disponibili:</strong> <?= htmlspecialchars($dettagli['Posti']) ?></p>
    <p><strong>Note:</strong> <?= htmlspecialchars($dettagli['Note']) ?: 'Nessuna' ?></p>

    <?php if ($mostra_auto && $veicolo): ?>
      <hr>
      <h3>Dettagli Auto</h3>
      <p><strong>Marca:</strong> <?= htmlspecialchars($veicolo['Marca']) ?></p>
      <p><strong>Modello:</strong> <?= htmlspecialchars($veicolo['Modello']) ?></p>
      <p><strong>Colore:</strong> <?= htmlspecialchars($veicolo['Colore']) ?></p>
      <p><strong>Targa:</strong> <?= htmlspecialchars($veicolo['Targa']) ?></p>
      <p><strong>Posti Totali:</strong> <?= htmlspecialchars($veicolo['Posti']) ?></p>
    <?php endif; ?>

    <?php if (!$richiesta_esistente && $dettagli['Posti'] > 0 && $user_id != $dettagli['ID_Autista']): ?>
      <div style="text-align:center; margin-top: 20px;">
        <button onclick="inviaRichiesta(<?= $id_viaggio ?>)">Richiedi Passaggio</button>
      </div>

      <script>
        function inviaRichiesta(id_viaggio) {
          fetch('api/richiesta_viaggio.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_viaggio })
          })
          .then(res => res.json())
          .then(data => {
            alert(data.message);
            if (data.success) location.reload();
          })
          .catch(() => alert('Errore di rete'));
        }
      </script>
    <?php elseif ($richiesta_esistente && $prenotazione['Stato'] === 'In attesa'): ?>
      <p class="info">Hai già richiesto questo passaggio. In attesa di conferma.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
