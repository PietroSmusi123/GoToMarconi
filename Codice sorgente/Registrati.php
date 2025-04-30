<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$db = "GoToMarconi";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO Utente (Nome, Cognome, Email, Telefono, Password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nome, $cognome, $email, $telefono, $password);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;  // Salva la sessione per login automatico
        header("Location: home.php");
        exit();
    } else {
        $errore = "Errore: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrati</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: linear-gradient(to right, #9b59b6, #e67e22);
            padding: 40px;
            border-radius: 10px;
            width: 320px;
            color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 12px; margin: 8px 0;
            border: none; border-radius: 5px; font-size: 14px;
        }
        input[type="submit"] {
            background: white; color: #333; font-weight: bold;
            width: 100%; padding: 12px; border-radius: 5px;
            cursor: pointer; font-size: 15px; margin-top: 10px;
        }
        .home-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #ffffffcc;
            color: #333;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<a href="Main.php" class="home-button">Ritorna alla pagina iniziale</a>

<form action="Registrati.php" method="POST" class="form-container">
    <h2>Registrati</h2>
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="text" name="cognome" placeholder="Cognome" required>
    <input type="email" name="email" placeholder="E-mail" required>
    <input type="text" name="telefono" placeholder="Telefono" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="submit" value="Registrati">
    <a href="Login.php">Hai già un account? Accedi</a>
    <?php if (!empty($errore)) echo "<p style='color:red;'>$errore</p>"; ?>
</form>

</body>
</html>
