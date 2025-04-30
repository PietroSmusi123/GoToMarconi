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
            $_SESSION['user_id'] = $id;  // Salva la sessione
            header("Location: home.php"); // Vai direttamente a home.php
            exit();
        } else {
            $errore = "Password errata.";
        }
    } else {
        $errore = "Utente non trovato.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Accedi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: linear-gradient(to right, #f9d423, #ff4e50);
            padding: 30px;
            border-radius: 10px;
            width: 300px;
            color: black;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        input[type="email"], input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
        }
        input[type="submit"] {
            background-color: #ffe066;
            font-weight: bold;
            cursor: pointer;
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

<form action="Login.php" method="POST" class="form-container">
    <h2>Accedi</h2>
    <input type="email" name="email" placeholder="E-mail" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="submit" value="Accedi">
    <a href="Registrati.php">Non hai un account? Registrati</a>
    <?php if (!empty($errore)) echo "<p style='color:red;'>$errore</p>"; ?>
</form>

</body>
</html>
