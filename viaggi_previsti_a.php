<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) die("Connessione fallita");

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$id_autista = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT * FROM Viaggio 
    WHERE ID_Autista = ? 
      AND Data >= CURDATE()
      AND Completato = 0
    ORDER BY Data ASC
");
$stmt->bind_param("i", $id_autista);
$stmt->execute();
$result = $stmt->get_result();
$viaggi = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Viaggi Previsti</title>
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
      max-width: 700px;
    }

    h2 {
      color: #2f6f75;
      margin-bottom: 20px;
      text-align: center;
    }

    .viaggio-box {
      background: #fefefe;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .viaggio-box strong {
      color: #333;
    }

    .viaggio-box form {
      display: inline-block;
      margin-top: 10px;
      margin-right: 10px;
    }

    button {
      padding: 8px 15px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }

    .btn-annulla {
      background-color: #dc3545;
      color: white;
    }

    .btn-conferma {
      background-color: #007bff;
      color: white;
    }

    .no-data {
      text-align: center;
      color: #777;
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
  <div class="container">
    <h2>I tuoi viaggi programmati</h2>

    <?php if (count($viaggi) === 0): ?>
      <p class="no-data">Non hai viaggi programmati al momento.</p>
    <?php else: ?>
      <?php foreach ($viaggi as $v): ?>
        <div class="viaggio-box">
          <strong>Data:</strong> <?= htmlspecialchars(date('Y-m-d', strtotime($v['Data']))) ?><br>
          <strong>Partenza:</strong> <?= htmlspecialchars($v['Partenza']) ?><br>
          <strong>Note:</strong> <?= htmlspecialchars($v['Note']) ?: 'Nessuna' ?><br>

          <form method="POST" action="annulla_viaggio.php" onsubmit="return confirm('Sei sicuro di voler annullare questo viaggio?');">
            <input type="hidden" name="id_viaggio" value="<?= $v['ID'] ?>">
            <button class="btn-annulla" type="submit">❌ Annulla viaggio</button>
          </form>

          <form method="POST" action="conferma_arrivo.php" onsubmit="return confirm('Confermare che il viaggio è stato completato?');">
            <input type="hidden" name="id_viaggio" value="<?= $v['ID'] ?>">
            <button class="btn-conferma" type="submit">✅ Conferma arrivo</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
