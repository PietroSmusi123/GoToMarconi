<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) die("Errore connessione");

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $autore = $_SESSION['user_id'];
    $id_utente = $_POST['id_utente'];
    $voto = $_POST['voto'];
    $commento = $_POST['commento'];
    $data = date("Y-m-d");

    $stmt = $conn->prepare("INSERT INTO recensione (ID_Autore, ID_Utente, Voto, Commento, Data) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $autore, $id_utente, $voto, $commento, $data);
    $stmt->execute();
    $stmt->close();

    header("Location: Viaggi_effettuatiP.php?msg=recensione_ok");
    exit();
}
?>
