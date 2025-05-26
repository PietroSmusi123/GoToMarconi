<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$utente_loggato = $_SESSION['user_id'];
$altro_id = isset($_GET['utente_id']) ? intval($_GET['utente_id']) : null;

$ruolo = $_GET['from'] ?? $_POST['from'] ?? 'home';


switch ($ruolo) {
    case 'autista':
        $torna_home = 'autista.php';
        break;
    case 'passeggero':
        $torna_home = 'passeggero.php';
        break;
    default:
        $torna_home = 'seleziona_ruolo.php';
}




if (!$altro_id || $altro_id == $utente_loggato) {
    die("ID utente non valido.");
}

// Recupera dati dell'altro utente
$stmt = $conn->prepare("SELECT Nome, Cognome FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $altro_id);
$stmt->execute();
$result = $stmt->get_result();
$altro_utente = $result->fetch_assoc();
$stmt->close();

if (!$altro_utente) {
    die("Utente non trovato.");
}

// Invia nuovo messaggio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['messaggio'])) {
    $testo = trim($_POST['messaggio']);
    if (!empty($testo)) {
        $stmt = $conn->prepare("INSERT INTO Chat (Mittente_ID, Destinatario_ID, Messaggio) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $utente_loggato, $altro_id, $testo);
        $stmt->execute();
        $stmt->close();

        // 🔁 Redireziona per evitare il reinvio del messaggio su refresh
        header("Location: Chat.php?utente_id=$altro_id&from=$ruolo");
        exit();

    }
}


// Recupera tutti i messaggi tra i due utenti
$stmt = $conn->prepare("
    SELECT Mittente_ID, Messaggio, Timestamp 
    FROM Chat 
    WHERE (Mittente_ID = ? AND Destinatario_ID = ?) 
       OR (Mittente_ID = ? AND Destinatario_ID = ?)
    ORDER BY Timestamp ASC
");
$stmt->bind_param("iiii", $utente_loggato, $altro_id, $altro_id, $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$messaggi = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Chat con <?= htmlspecialchars($altro_utente['Nome'] . ' ' . $altro_utente['Cognome']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        .chat-container {
            max-width: 600px;
            margin: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .chat-header {
            background: #0077aa;
            color: white;
            padding: 15px;
            font-size: 18px;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 15px;
            background: #f9f9f9;
        }
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            max-width: 70%;
            clear: both;
        }
        .tu {
            background-color: #dcf8c6;
            float: right;
            text-align: right;
        }
        .altro {
            background-color: #e0e0e0;
            float: left;
            text-align: left;
        }
        .chat-form {
            display: flex;
            padding: 15px;
            border-top: 1px solid #ccc;
            gap: 10px;
        }
        .chat-form textarea {
            flex: 1;
            padding: 10px;
            resize: none;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .chat-form button {
            padding: 10px 20px;
            border: none;
            background: #0077aa;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }
        .chat-form button:hover {
            background: #005f88;
        }
        .back-link {
            display: block;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        Chat con <?= htmlspecialchars($altro_utente['Nome'] . ' ' . $altro_utente['Cognome']) ?>
    </div>
    <div class="chat-messages">
        <?php foreach ($messaggi as $msg): 
            $classe = ($msg['Mittente_ID'] == $utente_loggato) ? 'tu' : 'altro';
        ?>
            <div class="message <?= $classe ?>">
                <?= nl2br(htmlspecialchars($msg['Messaggio'])) ?>
                <br><small><em><?= $msg['Timestamp'] ?></em></small>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="POST" class="chat-form">
    <textarea name="messaggio" rows="2" placeholder="Scrivi un messaggio..." required></textarea>
    <input type="hidden" name="from" value="<?= htmlspecialchars($_GET['from'] ?? 'home') ?>">
    <button type="submit">Invia</button>
</form>


</form>

</div>

<a href="<?= htmlspecialchars($torna_home) ?>" class="back-link">🏠 Torna alla Home</a>


</body>
</html>