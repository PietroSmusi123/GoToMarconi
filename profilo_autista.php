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
 
$id = $_GET['id'] ?? $_SESSION['user_id'];
$from = $_GET['from'] ?? 'passeggero';
 
$stmt = $conn->prepare("SELECT Nome, Cognome, Email, Telefono, Citta FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
$stmt->close();
 
if (!$utente) {
    echo "<h2 style='color:red;text-align:center;'>❌ Utente non trovato.</h2>";
    exit();
}
 
$stmt = $conn->prepare("SELECT ROUND(AVG(Voto), 1) as Media FROM Recensione WHERE ID_Utente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$media_voto = $row['Media'] ?? null;
$stmt->close();
 
$stmt = $conn->prepare("SELECT ID, Data, Posti FROM Viaggio WHERE ID_Autista = ? ORDER BY Data DESC LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$viaggio = $res->fetch_assoc();
$stmt->close();
 
$stmt = $conn->prepare("SELECT Marca, Modello, Colore, Targa, Posti FROM Veicolo WHERE ID_Proprietario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$auto = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
 
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Profilo Utente</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #fdfcfb, #e2d1c3);
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
 
    .profile-box {
      background-color: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
      width: 100%;
      max-width: 450px;
    }
 
    h2 {
      text-align: center;
      color: #2f6f75;
      margin-bottom: 5px;
    }
 
    .field {
      margin-bottom: 15px;
    }
 
    .field label {
      font-weight: bold;
      color: #555;
    }
 
    .field p {
      margin: 5px 0 0;
    }
 
    .button-container {
      text-align: right;
      margin-top: 20px;
    }
 
    .button-container button {
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>
<body>
 
<div class="navbar">
  <div class="navbar-left">
    <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
    <a href="<?= htmlspecialchars($from) ?>.php" class="dashboard-button">⬅ Torna alla dashboard</a>
  </div>
</div>
 
<div class="main">
  <div class="profile-box">
    <h2>
      <?= htmlspecialchars($utente['Nome'] . ' ' . $utente['Cognome']) ?>
      <?php if ($media_voto): ?>
        <br><small style="font-size: 0.8em; color: #666;">⭐ Media recensioni: <?= $media_voto ?> / 5</small>
      <?php endif; ?>
    </h2>
 
    <div class="field"><label>Nome:</label><p><?= htmlspecialchars($utente['Nome']) ?></p></div>
    <div class="field"><label>Cognome:</label><p><?= htmlspecialchars($utente['Cognome']) ?></p></div>
    <div class="field"><label>Email:</label><p><?= htmlspecialchars($utente['Email']) ?></p></div>
    <div class="field"><label>Telefono:</label><p><?= htmlspecialchars($utente['Telefono']) ?></p></div>
    <div class="field"><label>Città:</label><p><?= htmlspecialchars($utente['Citta']) ?></p></div>
 
    <?php if ($auto && $from === 'autista'): ?>
      <hr>
      <h3 style="text-align:center; color:#2f6f75;">Dettagli Auto</h3>
      <div class="field"><label>Marca:</label><p><?= htmlspecialchars($auto['Marca']) ?></p></div>
      <div class="field"><label>Modello:</label><p><?= htmlspecialchars($auto['Modello']) ?></p></div>
      <div class="field"><label>Colore:</label><p><?= htmlspecialchars($auto['Colore']) ?></p></div>
      <div class="field"><label>Targa:</label><p><?= htmlspecialchars($auto['Targa']) ?></p></div>
      <div class="field"><label>Posti:</label><p><?= htmlspecialchars($auto['Posti']) ?></p></div>
    <?php endif; ?>
 
    <?php if ($from === 'passeggero' && isset($_SESSION['user_id']) && $_SESSION['user_id'] != $id && $viaggio && $viaggio['Posti'] > 0): ?>
      <div class="button-container">
        <button onclick="inviaRichiesta(<?= $viaggio['ID'] ?>)">
          Richiedi Passaggio
        </button>
      </div>
    <?php endif; ?>
    <div style="margin-top: 30px; text-align: center;">
  <a href="conferma_elimina_account.php" style="background-color: #dc3545; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold;">
    ❌ Elimina Account
  </a>
</div>

  </div>
</div>
 
<script>
function inviaRichiesta(idViaggio) {
    fetch("api/richiesta_viaggio.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_viaggio: idViaggio })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ " + data.message);
        } else {
            alert("❌ " + data.message);
        }
    })
    .catch(() => {
        alert("⚠️ Errore nella richiesta.");
    });
}
</script>
 
</body>
</html>