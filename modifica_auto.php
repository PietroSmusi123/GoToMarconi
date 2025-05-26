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
$user_id = $_SESSION['user_id'];
$errore = "";
 
// Salvataggio
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $marca = $_POST['marca'];
    $modello = $_POST['modello'];
    $colore = $_POST['colore'];
    $targa = strtoupper(trim($_POST['targa']));
    $posti = $_POST['posti'];
 
    if (!preg_match('/^[A-Z]{2}[0-9]{3}[A-Z]{2}$/', $targa)) {
        $errore = "Formato targa non valido. Deve essere nel formato AA123BB.";
    }
 
    if ($errore === "") {
        $stmt = $conn->prepare("SELECT * FROM Veicolo WHERE ID_Proprietario = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $stmt = $conn->prepare("UPDATE Veicolo SET Marca = ?, Modello = ?, Colore = ?, Targa = ?, Posti = ? WHERE ID_Proprietario = ?");
            $stmt->bind_param("ssssii", $marca, $modello, $colore, $targa, $posti, $user_id);
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO Veicolo (Marca, Modello, Colore, Targa, Posti, ID_Proprietario) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssii", $marca, $modello, $colore, $targa, $posti, $user_id);
        }
 
        if ($stmt->execute()) {
            header("Location: autista.php");
            exit();
        } else {
            $errore = "Errore nel salvataggio dei dati.";
        }
 
        $stmt->close();
    }
}
 
// Recupero dati auto
$stmt = $conn->prepare("SELECT Marca, Modello, Colore, Targa, Posti FROM Veicolo WHERE ID_Proprietario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$auto = $result->fetch_assoc();
$stmt->close();
?>
 
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Modifica Dati Auto</title>
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
      padding: 50px 20px;
    }
 
    .box {
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
 
    .errore {
      color: red;
      text-align: center;
    }
 
    .back-link {
      text-decoration: none;
      color: white;
      background-color: #ffc107;
      padding: 8px 15px;
      border-radius: 8px;
      font-weight: bold;
    }
    .dashboard-button {
  text-decoration: none;
  color: white;
  background-color: #ffc107;
  padding: 8px 15px;
  border-radius: 8px;
  font-weight: bold;
}
 
  </style>
</head>
<body>
 
<div class="navbar">
  <div style="display: flex; align-items: center; gap: 20px;">
    <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
    <a href="autista.php" class="dashboard-button">⬅ Torna alla dashboard</a>
  </div>
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
 
 
<div class="main">
  <div class="box">
    <h2>Modifica Dettagli Auto</h2>
    <?php if ($errore): ?>
      <p class="errore"><?= $errore ?></p>
    <?php endif; ?>
    <form method="post">
      <input type="text" name="marca" placeholder="Marca" value="<?= htmlspecialchars($auto['Marca'] ?? '') ?>" required>
      <input type="text" name="modello" placeholder="Modello" value="<?= htmlspecialchars($auto['Modello'] ?? '') ?>" required>
      <input type="text" name="colore" placeholder="Colore" value="<?= htmlspecialchars($auto['Colore'] ?? '') ?>" required>
      <input type="text" name="targa" placeholder="Targa" value="<?= htmlspecialchars($auto['Targa'] ?? '') ?>" required>
      <input type="number" name="posti" placeholder="Numero di posti" value="<?= htmlspecialchars($auto['Posti'] ?? '') ?>" required min="1">
      <button type="submit">Salva dettagli auto</button>
    </form>
  </div>
</div>
 
<script>
  const targaInput = document.querySelector('input[name="targa"]');
  targaInput.addEventListener('input', () => {
    let val = targaInput.value.trim();
    targaInput.value = val.toUpperCase();
    const regex = /^[A-Z]{2}[0-9]{3}[A-Z]{2}$/;
    if (!regex.test(targaInput.value)) {
      targaInput.setCustomValidity("La targa deve essere nel formato AA123BB.");
    } else {
      targaInput.setCustomValidity("");
    }
  });
</script>
 
</body>
</html>