<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connessione DB
$host = "localhost";
$db = "GoToMarconi";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$id_utente = $_SESSION['user_id'];
$id_richiesta = $_POST['id_richiesta'] ?? null;
$azione = $_POST['azione'] ?? null;

if ($id_richiesta && in_array($azione, ['accetta', 'rifiuta'])) {
    $stato = $azione === 'accetta' ? 'Accettata' : 'Rifiutata';

    // Aggiorna solo se la richiesta è destinata all'utente loggato
    $stmt = $conn->prepare("UPDATE Prenotazione SET Stato = ? WHERE ID = ? AND ID_Passeggero = ?");
    $stmt->bind_param("sii", $stato, $id_richiesta, $id_utente);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: home.php");
exit();
?>
