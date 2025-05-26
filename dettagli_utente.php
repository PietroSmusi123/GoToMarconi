<?php
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['errore' => 'ID mancante']);
    exit;
}

$host = "localhost";
$db = "GoToMarconi";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['errore' => 'Connessione al database fallita']);
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT Nome, Cognome, Numero, ModelloAuto, Targa FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($dati = $result->fetch_assoc()) {
    echo json_encode($dati);
} else {
    echo json_encode(['errore' => 'Utente non trovato']);
}

$conn->close();
?>