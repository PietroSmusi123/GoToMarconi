<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id']) || !isset($_GET['id_richiesta'])) {
    header("Location: autista.php");
    exit();
}

$id_richiesta = intval($_GET['id_richiesta']);

$stmt = $conn->prepare("
    SELECT u.Nome, u.Cognome, u.Email, u.Telefono, u.Citta,
           v.Partenza, v.Data, v.Note
    FROM Prenotazione p
    JOIN Utente u ON u.ID = p.ID_Passeggero
    JOIN Viaggio v ON v.ID = p.ID_Viaggio
    WHERE p.ID = ?
");
$stmt->bind_param("i", $id_richiesta);
$stmt->execute();
$result = $stmt->get_result();
$dettagli = $result->fetch_assoc();
$stmt->close();

if (!$dettagli) {
    echo "<h2 style='text-align:center;color:red;'>Richiesta non trovata.</h2>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Dettagli Richiesta</title>
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
      max-width: 500px;
      width: 100%;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }

    h2 {
      color: #2f6f75;
      text-align: center;
      margin-bottom: 10px;
    }

    h3 {
      color: #444;
      border-bottom: 1px solid #ddd;
      padding-bottom: 5px;
    }

    p {
      margin: 8px 0;
    }

    .btn-azione {
      padding: 10px 20px;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin: 0 10px;
    }

    .btn-accetta {
      background-color: #2f6f75;
      color: white;
    }

    .btn-accetta:hover {
      background-color: #24585e;
    }

    .btn-rifiuta {
      background-color: #e74c3c;
      color: white;
    }

    .btn-rifiuta:hover {
      background-color: #c0392b;
    }

    #overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background-color: rgba(0,0,0,0.5);
      z-index: 999;
    }

    #modaleConferma {
      display: none;
      position: fixed;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
      z-index: 1000;
      text-align: center;
    }

    #modaleConferma button {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      margin: 0 10px;
    }

    #modaleConferma button:first-child {
      background: #28a745;
      color: white;
    }

    #modaleConferma button:last-child {
      background: #dc3545;
      color: white;
    }

    #messaggio-risposta {
      margin-top: 20px;
      text-align: center;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="navbar">
  <div class="navbar-left">
    <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
    <a href="autista.php" class="dashboard-button">⬅ Torna alla dashboard</a>
  </div>
</div>

<div class="main">
  <div class="card">
    <h2><?= htmlspecialchars($dettagli['Nome'] . ' ' . $dettagli['Cognome']) ?></h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($dettagli['Email']) ?></p>
    <p><strong>Telefono:</strong> <?= htmlspecialchars($dettagli['Telefono']) ?></p>
    <p><strong>Città:</strong> <?= htmlspecialchars($dettagli['Citta']) ?></p>

    <hr>
    <h3>Dettagli del viaggio richiesto</h3>
    <p><strong>Partenza:</strong> <?= htmlspecialchars($dettagli['Partenza']) ?></p>
    <p><strong>Data:</strong> <?= htmlspecialchars($dettagli['Data']) ?></p>
    <p><strong>Note:</strong> <?= htmlspecialchars($dettagli['Note']) ?: 'Nessuna' ?></p>

    <div id="messaggio-risposta"></div>

    <div style="margin-top: 30px; text-align: center;">
      <button class="btn-azione btn-accetta" type="button"
        onclick="apriConferma('accetta', '<?= $dettagli['Nome'] . ' ' . $dettagli['Cognome'] ?>')">
        ✅ Accetta
      </button>
      <button class="btn-azione btn-rifiuta" type="button"
        onclick="apriConferma('rifiuta', '<?= $dettagli['Nome'] . ' ' . $dettagli['Cognome'] ?>')">
        ❌ Rifiuta
      </button>
    </div>
  </div>
</div>

<div id="overlay"></div>
<div id="modaleConferma">
  <p id="messaggioConferma"></p>
  <button onclick="confermaFinale()">Conferma</button>
  <button onclick="chiudiModale()">Annulla</button>
</div>

<script>
function gestisciRichiesta(azione) {
  fetch("api/gestisci_richiesta.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      id_richiesta: <?= $id_richiesta ?>,
      azione: azione
    })
  })
  .then(res => res.json())
  .then(data => {
    const box = document.getElementById("messaggio-risposta");
    box.textContent = data.message || "Operazione eseguita";
    box.style.color = data.success ? "green" : "red";
  })
  .catch(() => {
    document.getElementById("messaggio-risposta").textContent = "❌ Errore di rete.";
  });
}

let azioneScelta = "";

function apriConferma(tipo, nomePasseggero) {
  azioneScelta = tipo;
  const messaggio = tipo === "accetta"
    ? `Sei sicuro di voler accettare la richiesta di ${nomePasseggero}?`
    : `Sei sicuro di voler rifiutare la richiesta di ${nomePasseggero}?`;

  document.getElementById("messaggioConferma").innerText = messaggio;
  document.getElementById("overlay").style.display = "block";
  document.getElementById("modaleConferma").style.display = "block";
}

function chiudiModale() {
  document.getElementById("overlay").style.display = "none";
  document.getElementById("modaleConferma").style.display = "none";
}

function confermaFinale() {
  chiudiModale();
  gestisciRichiesta(azioneScelta);
}
</script>

</body>
</html>
