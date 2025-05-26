<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$errore = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT ID, Password FROM Utente WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hash);
        $stmt->fetch();

        if (password_verify($password, $hash)) {
            $_SESSION['user_id'] = $id;
            header("Location: seleziona_ruolo.php");
            exit();
        } else {
            $errore = "Password errata.";
        }
    } else {
        $errore = "Utente non trovato.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Login - GoToMarconi</title>
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

    .form-container {
      margin-top: 80px;
      background-color: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
      width: 350px;
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

    .link {
      margin-top: 15px;
      text-align: center;
      font-size: 0.95em;
    }

    .link a {
      color: #007bff;
      text-decoration: none;
    }

    .link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="navbar">
    <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
  </div>

  <div class="form-container">
    <h2>Accedi al tuo account</h2>
    <?php if (!empty($errore)): ?>
      <div class="error"><?= $errore ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <div class="link">
      Non hai un account? <a href="Registrati.php">Registrati qui</a>
    </div>
  </div>

</body>
</html>
