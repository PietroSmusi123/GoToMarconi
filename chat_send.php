<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) die("Connessione fallita.");

if (!isset($_SESSION['user_id']) || !isset($_POST['destinatario_id']) || !isset($_POST['messaggio'])) exit();

$mittente = $_SESSION['user_id'];
$destinatario = intval($_POST['destinatario_id']);
$messaggio = trim($_POST['messaggio']);

if ($messaggio !== '') {
    $stmt = $conn->prepare("INSERT INTO Chat (Mittente_ID, Destinatario_ID, Messaggio) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $mittente, $destinatario, $messaggio);
    $stmt->execute();
    $stmt->close();
}
?>
