<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Elimina le prenotazioni ricevute per i viaggi creati
$conn->query("DELETE FROM Prenotazione WHERE ID_Viaggio IN (SELECT ID FROM Viaggio WHERE ID_Autista = $user_id)");

// Elimina le destinazioni associate ai viaggi
$conn->query("DELETE FROM Viaggio_Destinazione WHERE ID_Viaggio IN (SELECT ID FROM Viaggio WHERE ID_Autista = $user_id)");

// Elimina i viaggi creati dall'autista
$conn->query("DELETE FROM Viaggio WHERE ID_Autista = $user_id");

// Elimina il veicolo dell'autista
$conn->query("DELETE FROM Veicolo WHERE ID_Proprietario = $user_id");

// Elimina le prenotazioni come passeggero (opzionale, se l'autista è anche passeggero)
$conn->query("DELETE FROM Prenotazione WHERE ID_Passeggero = $user_id");

// Elimina l'utente
$conn->query("DELETE FROM Utente WHERE ID = $user_id");

// Distruggi la sessione
session_destroy();

header("Location: Login.php");
exit();
?>
