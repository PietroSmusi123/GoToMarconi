<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) die("Connessione fallita");

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id_viaggio'])) {
    header("Location: viaggi_previsti_a.php");
    exit();
}

$id_viaggio = intval($_POST['id_viaggio']);
$id_autista = $_SESSION['user_id'];

// Controlla che il viaggio appartenga all'autista loggato
$stmt = $conn->prepare("SELECT ID FROM Viaggio WHERE ID = ? AND ID_Autista = ?");
$stmt->bind_param("ii", $id_viaggio, $id_autista);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h3 style='color:red;'>❌ Errore: viaggio non valido o non autorizzato.</h3>";
    exit();
}
$stmt->close();

// Notifica i passeggeri accettati
$stmt = $conn->prepare("
    SELECT ID_Passeggero FROM Prenotazione
    WHERE ID_Viaggio = ? AND Stato = 'Accettata'
");
$stmt->bind_param("i", $id_viaggio);
$stmt->execute();
$result = $stmt->get_result();
$passeggeri = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($passeggeri as $p) {
    $id_passeggero = $p['ID_Passeggero'];
    $msg = "⚠️ Il viaggio a cui eri stato accettato è stato annullato dall'autista. Cerca un'altra soluzione.";
    $stmt = $conn->prepare("INSERT INTO Notifica (ID_Utente, Messaggio) VALUES (?, ?)");
    $stmt->bind_param("is", $id_passeggero, $msg);
    $stmt->execute();
    $stmt->close();
}

// Elimina prenotazioni associate
$conn->query("DELETE FROM Prenotazione WHERE ID_Viaggio = $id_viaggio");

// Elimina città associate
$conn->query("DELETE FROM Viaggio_Destinazione WHERE ID_Viaggio = $id_viaggio");

// Elimina il viaggio
$conn->query("DELETE FROM Viaggio WHERE ID = $id_viaggio");

header("Location: viaggi_previsti_a.php");
exit();
?>
