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
 
$stmt = $conn->prepare("SELECT Nome, Cognome FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
$stmt->close();
 
$stmt = $conn->prepare("SELECT Partenza, Data FROM Viaggio WHERE ID_Autista = ? AND Completato = 1 ORDER BY Data DESC");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$viaggi = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
 
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Viaggi Effettuati</title>
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
      justify-content: center;
      padding: 60px 20px;
    }
 
    .container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      width: 100%;
      max-width: 600px;
    }
 
    h2 {
      color: #2f6f75;
      text-align: center;
    }
 
    ul {
      list-style: none;
      padding: 0;
    }
 
    li {
      padding: 10px 0;
      border-bottom: 1px solid #ddd;
    }
 
    .no-data {
      text-align: center;
      color: #777;
      padding: 20px 0;
    }
  </style>
</head>
<body>
 
<div class="navbar">
  <div class="navbar-left">
    <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
    <a href="autista.php" class="dashboard-button">⬅ Torna alla dashboard</a>
  </div>
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
        <a href="Viaggi_effettuatiA.php">📋 Viaggi effettuati</a>
        <a href="logout.php" style="color: red;">🚪 Disconnetti</a>
      </div>
    </div>
  </div>
</div>
 
<div class="main">
  <div class="container">
    <h2>Viaggi Effettuati di <?= htmlspecialchars($utente['Nome'] . ' ' . $utente['Cognome']) ?></h2>
    <?php if (count($viaggi) > 0): ?>
      <ul>
        <?php foreach ($viaggi as $v): ?>
          <li><strong><?= htmlspecialchars($v['Partenza']) ?></strong> – <?= htmlspecialchars(date('Y-m-d', strtotime($v['Data']))) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="no-data">Nessun viaggio effettuato.</p>
    <?php endif; ?>
  </div>
</div>
 
</body>
</html>
 