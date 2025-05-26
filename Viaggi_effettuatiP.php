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

$stmt = $conn->prepare("
	SELECT v.Data, v.Partenza, v.ID_Autista, u.Nome, u.Cognome
    FROM Prenotazione p
    JOIN Viaggio v ON v.ID = p.ID_Viaggio
    JOIN Utente u ON u.ID = v.ID_Autista
    WHERE p.ID_Passeggero = ? AND p.Stato = 'Accettata' AND v.Completato = 1
    ORDER BY v.Data DESC
");


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
      background-color: #f4f4f4;
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

    .box {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      width: 100%;
      max-width: 700px;
    }

    h2 {
      text-align: center;
      color: #2f6f75;
      margin-bottom: 20px;
    }

    ul {
      list-style: none;
      padding: 0;
    }

    li {
      padding: 12px 0;
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
    <a href="passeggero.php" class="dashboard-button">⬅ Torna alla dashboard</a>
  </div>
</div>

<div class="main">
  <div class="box">
    <h2>Viaggi Effettuati</h2>
    <?php if (count($viaggi) > 0): ?>
      <ul>
        <?php foreach ($viaggi as $v): ?>
          <li>
            <strong><?= htmlspecialchars(date('Y-m-d', strtotime($v['Data']))) ?></strong> – 
            <?= htmlspecialchars($v['Partenza']) ?> <br>
            Autista: <?= htmlspecialchars($v['Nome'] . ' ' . $v['Cognome']) ?><br>
            Note: <?= htmlspecialchars($v['Note'] ?: 'Nessuna') ?>
			<form action="recensisci.php" method="POST">
			  <input type="hidden" name="id_utente" value="<?= $v['ID_Autista'] ?? $v['ID_Passeggero'] ?>"> <!-- cambia a seconda se è passeggero o autista -->
			  
			  <label for="voto">Voto:</label>
			  <select name="voto" required>
				<option value="5">5 ⭐</option>
				<option value="4">4 ⭐</option>
				<option value="3">3 ⭐</option>
				<option value="2">2 ⭐</option>
				<option value="1">1 ⭐</option>
			  </select><br>

			  <label for="commento">Commento:</label><br>
			  <textarea name="commento" rows="3" cols="50" required></textarea><br>

			  <button type="submit">Lascia recensione</button>
			</form>

          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="no-data">Nessun viaggio effettuato.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
