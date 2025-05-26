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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ruolo = $_POST['ruolo'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE Utente SET Ruolo = ? WHERE ID = ?");
    $stmt->bind_param("si", $ruolo, $user_id);
    $stmt->execute();
    $stmt->close();

    if ($ruolo === "Autista") {
        header("Location: autista.php");
    } else {
        header("Location: passeggero.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Seleziona Ruolo - GoToMarconi</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #fdfcfb, #e2d1c3);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .navbar {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
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

    .role-box {
      background-color: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
      width: 400px;
      text-align: center;
      margin-top: 80px;
    }

    h2 {
      color: #2f6f75;
      margin-bottom: 30px;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #2f6f75;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      font-size: 1em;
      margin: 10px 0;
      cursor: pointer;
    }

    button:hover {
      background-color: #224c50;
    }
  </style>
</head>
<body>

<div class="navbar">
  <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
</div>

<div class="role-box">
  <h2>Con quale ruolo vuoi accedere?</h2>
  <form method="post">
    <input type="hidden" name="ruolo" value="Autista" />
    <button type="submit">🚗 Accedi come Autista</button>
  </form>
  <form method="post">
    <input type="hidden" name="ruolo" value="Passeggero" />
    <button type="submit">🧍‍♂️ Accedi come Passeggero</button>
  </form>
</div>

</body>
</html>
