<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connessione al DB
$host = "localhost";
$db = "GoToMarconi";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Verifica sessione
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$richiedente_id = $_SESSION['user_id'];
$utenti_selezionati = $_POST['utenti_id'] ?? [];

if (!empty($utenti_selezionati)) {
    foreach ($utenti_selezionati as $utente_id) {
        // Evita l'autoinvio
        if ($utente_id == $richiedente_id) continue;

        $stato = "In attesa";
        $stmt = $conn->prepare("INSERT INTO Prenotazione (ID_Passeggero, ID_Viaggio, Stato) VALUES (?, NULL, ?)");
        $stmt->bind_param("is", $utente_id, $stato);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: home.php");
exit();
?>
