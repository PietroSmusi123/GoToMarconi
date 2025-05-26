<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) die("Connessione fallita");

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$id_autista = $_SESSION['user_id'];
$id_viaggio = intval($_POST['id_viaggio'] ?? 0);

// Verifica che il viaggio appartenga all'autista loggato
$stmt = $conn->prepare("SELECT ID FROM Viaggio WHERE ID = ? AND ID_Autista = ?");
$stmt->bind_param("ii", $id_viaggio, $id_autista);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo "<h3 style='color:red;'>❌ Non autorizzato.</h3>";
    exit();
}
$stmt->close();

// Aggiorna viaggio a completato
$stmt = $conn->prepare("UPDATE Viaggio SET Completato = 1 WHERE ID = ?");
$stmt->bind_param("i", $id_viaggio);
$stmt->execute();
$stmt->close();

// Reindirizza
header("Location: viaggi_previsti_a.php");
exit();
?>
