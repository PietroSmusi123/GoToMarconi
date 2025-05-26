<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");

if (!isset($_SESSION['user_id']) || !isset($_POST['id_viaggio'])) {
    header("Location: Login.php");
    exit();
}

$utente_id = $_SESSION['user_id'];
$id_viaggio = intval($_POST['id_viaggio']);

// Aggiorna lo stato della prenotazione a 'Annullata'
$stmt = $conn->prepare("UPDATE Prenotazione SET Stato = 'Annullata' WHERE ID_Passeggero = ? AND ID_Viaggio = ?");
$stmt->bind_param("ii", $utente_id, $id_viaggio);
$stmt->execute();
$stmt->close();

header("Location: viaggi_previsti_p.php");
exit();

?>
