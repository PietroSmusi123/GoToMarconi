<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) die("Connessione fallita.");

if (!isset($_SESSION['user_id']) || !isset($_GET['utente_id'])) exit();

$utente_loggato = $_SESSION['user_id'];
$altro_id = intval($_GET['utente_id']);

$stmt = $conn->prepare("SELECT * FROM Chat WHERE 
    (Mittente_ID = ? AND Destinatario_ID = ?) OR 
    (Mittente_ID = ? AND Destinatario_ID = ?)
    ORDER BY Timestamp ASC");
$stmt->bind_param("iiii", $utente_loggato, $altro_id, $altro_id, $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();

?>
