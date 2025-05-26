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
$utente_id = $_SESSION['user_id'];
$errore = "";
$from = $_GET['from'] ?? 'autista';
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $citta = trim($_POST['citta']);
 
    $check = $conn->prepare("SELECT ID FROM Utente WHERE (Email = ? OR Telefono = ? OR (Nome = ? AND Cognome = ?)) AND ID != ?");
    $check->bind_param("ssssi", $email, $telefono, $nome, $cognome, $utente_id);
    $check->execute();
    $check->store_result();
 
    if ($check->num_rows > 0) {
        $errore = "Alcuni dati sono già in uso da un altro utente.";
    } else {
        $stmt = $conn->prepare("UPDATE Utente SET Nome = ?, Cognome = ?, Email = ?, Telefono = ?, Citta = ? WHERE ID = ?");
        $stmt->bind_param("sssssi", $nome, $cognome, $email, $telefono, $citta, $utente_id);
        if ($stmt->execute()) {
            header("Location: " . $from . ".php");
            exit();
        } else {
            $errore = "Errore durante l'aggiornamento.";
        }
        $stmt->close();
    }
    $check->close();
}
 
$stmt = $conn->prepare("SELECT Nome, Cognome, Email, Telefono, Citta FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $utente_id);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
$stmt->close();
?>
 
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Modifica Profilo</title>
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
      padding: 80px 20px;
    }
 
    .form-container {
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
    }
 
    input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
 
    button {
      width: 100%;
      padding: 12px;
      background-color: #2f6f75;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      margin-top: 10px;
    }
 
    .error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
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
  <div class="form-container">
    <h2>Modifica il tuo profilo</h2>
    <?php if (!empty($errore)): ?>
      <div class="error"><?= $errore ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="text" name="nome" value="<?= htmlspecialchars($utente['Nome']) ?>" required>
      <input type="text" name="cognome" value="<?= htmlspecialchars($utente['Cognome']) ?>" required>
      <input type="email" name="email" value="<?= htmlspecialchars($utente['Email']) ?>" required>
      <input type="text" name="telefono" value="<?= htmlspecialchars($utente['Telefono']) ?>" required>
      <input type="text" name="citta" value="<?= htmlspecialchars($utente['Citta']) ?>" required>
      <button type="submit">Salva modifiche</button>
    </form>
  </div>
</div>
 
</body>
</html>