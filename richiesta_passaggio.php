<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Utente non autenticato"]);
    exit;
}

$utente_loggato = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_viaggio = $_POST['id_viaggio'] ?? null;

    if (!$id_viaggio) {
        echo json_encode(["success" => false, "message" => "ID viaggio mancante"]);
        exit;
    }

    $id_viaggio = intval($id_viaggio);

    $conn = new mysqli("localhost", "root", "", "GoToMarconi");
    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "Connessione fallita"]);
        exit;
    }

    // Controllo posti
    $stmt = $conn->prepare("SELECT Posti FROM Viaggio WHERE ID = ?");
    $stmt->bind_param("i", $id_viaggio);
    $stmt->execute();
    $result = $stmt->get_result();
    $viaggio = $result->fetch_assoc();
    $stmt->close();

    if (!$viaggio || $viaggio['Posti'] <= 0) {
        echo json_encode(["success" => false, "message" => "Viaggio non disponibile"]);
        exit;
    }

    // Controlla se esiste già la richiesta
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM Prenotazione WHERE ID_Passeggero = ? AND ID_Viaggio = ?");
    $stmt->bind_param("ii", $utente_loggato, $id_viaggio);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row['count'] > 0) {
        echo json_encode(["success" => false, "message" => "Hai già richiesto questo viaggio"]);
        exit;
    }

    // Inserimento richiesta
    $stmt = $conn->prepare("INSERT INTO Prenotazione (ID_Passeggero, ID_Viaggio, Stato) VALUES (?, ?, 'In attesa')");
    $stmt->bind_param("ii", $utente_loggato, $id_viaggio);
    if ($stmt->execute()) {
        echo "<script>alert('✅ Richiesta inviata con successo!'); window.location.href = 'home.php';</script>";
    } else {
        echo json_encode(["success" => false, "message" => "Errore durante l'inserimento"]);
    }

    $stmt->close();
    $conn->close();
}

header("Location: home.php");
exit();
?>
