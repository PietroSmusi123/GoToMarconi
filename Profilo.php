<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connessione al DB
$host = "localhost";
$db = "GoToMarconi";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Verifica login
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$utente_loggato = $_SESSION['user_id'];

// Recupera dati utente
$stmt = $conn->prepare("SELECT Nome, Cognome, Email, Telefono, Ruolo FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
$stmt->close();

// Recupera veicoli dell'utente
$stmt = $conn->prepare("SELECT Targa, Modello, Colore, Posti FROM Veicolo WHERE ID_Proprietario = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$veicoli = [];
while ($row = $result->fetch_assoc()) {
    $veicoli[] = $row;
}
$stmt->close();

// === Sezione: Creazione Viaggio (solo Autisti) ===
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crea_viaggio'])) {
    $data = $_POST['data'];
    $posti = intval($_POST['posti']);
    $note = $_POST['note'] ?? '';
    $partenza = "Casa → Lavoro";

    if (strtotime($data) < strtotime(date("Y-m-d"))) {
        echo "<script>alert('La data non può essere nel passato.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO viaggio (ID_Autista, Partenza, Data, Posti, Note) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $utente_loggato, $partenza, $data, $posti, $note);
        if ($stmt->execute()) {
            echo "<script>alert('Viaggio creato con successo!');</script>";
        } else {
            echo "<script>alert('Errore nella creazione del viaggio');</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo Utente</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .box { background: #f4f4f4; padding: 20px; margin-bottom: 20px; border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        textarea { width: 100%; padding: 8px; border-radius: 5px; }
        input[type="submit"], input[type="number"], input[type="date"] {
            padding: 10px; margin-top: 10px; border-radius: 6px; border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>Profilo Utente</h1>

    <div class="box">
        <h2>Dati Personali</h2>
        <p><strong>Nome:</strong> <?= htmlspecialchars($utente['Nome']) ?></p>
        <p><strong>Cognome:</strong> <?= htmlspecialchars($utente['Cognome']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($utente['Email']) ?></p>
        <p><strong>Telefono:</strong> <?= htmlspecialchars($utente['Telefono']) ?></p>
        <p><strong>Ruolo:</strong> <?= htmlspecialchars($utente['Ruolo']) ?></p>
    </div>

    <div class="box">
        <h2>I miei veicoli</h2>
        <?php if (count($veicoli) > 0): ?>
            <table>
                <tr>
                    <th>Targa</th>
                    <th>Modello</th>
                    <th>Colore</th>
                    <th>Posti</th>
                </tr>
                <?php foreach ($veicoli as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['Targa']) ?></td>
                        <td><?= htmlspecialchars($v['Modello']) ?></td>
                        <td><?= htmlspecialchars($v['Colore']) ?></td>
                        <td><?= htmlspecialchars($v['Posti']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Nessun veicolo associato.</p>
        <?php endif; ?>
    </div>

    <?php if ($utente['Ruolo'] === 'Autista'): ?>
        <div class="box">
            <h2>Organizza un viaggio per il lavoro</h2>
            <form method="POST">
                <label for="data">Data del viaggio:</label><br>
                <input type="date" name="data" required><br><br>

                <label for="posti">Posti disponibili:</label><br>
                <input type="number" name="posti" min="1" max="8" required><br><br>

                <label for="note">Note aggiuntive (opzionale):</label><br>
                <textarea name="note" rows="4" placeholder="Es: Parto alle 7:30 davanti scuola..."></textarea><br><br>

                <input type="submit" name="crea_viaggio" value="Crea viaggio">
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
