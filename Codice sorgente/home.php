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

// Dati utente
$stmt = $conn->prepare("SELECT Nome, Cognome FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
$stmt->close();

// Ruolo utente
$stmt = $conn->prepare("SELECT Ruolo FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$ruolo_corrente = $row['Ruolo'] ?? "Nessuno";
$stmt->close();

// Cambio ruolo
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cambia_ruolo'])) {
    $nuovo_ruolo = ($ruolo_corrente === 'Autista') ? 'Passeggero' : 'Autista';
    $stmt = $conn->prepare("UPDATE Utente SET Ruolo = ? WHERE ID = ?");
    $stmt->bind_param("si", $nuovo_ruolo, $utente_loggato);
    $stmt->execute();
    $stmt->close();
    header("Location: home.php");
    exit();
}

// Altri utenti
$result = $conn->query("SELECT ID, Nome, Cognome FROM Utente WHERE ID != $utente_loggato");
$utenti = [];
while ($row = $result->fetch_assoc()) {
    $utenti[] = $row;
}

// Viaggi effettuati
$stmt = $conn->prepare("SELECT Partenza, Data FROM Viaggio WHERE ID_Autista = ? ORDER BY Data DESC");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$viaggi_effettuati = [];
while ($riga = $result->fetch_assoc()) {
    $viaggi_effettuati[] = $riga;
}
$stmt->close();

// Richieste ricevute
$richieste = [];
$stmt = $conn->prepare("
    SELECT Prenotazione.ID, Utente.Nome, Utente.Cognome, Prenotazione.Stato 
    FROM Prenotazione 
    JOIN Utente ON Utente.ID = Prenotazione.ID_Passeggero 
    WHERE ID_Passeggero = ?
    ORDER BY Prenotazione.ID DESC
");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $richieste[] = $row;
}
$stmt->close();

// Gestione richiesta
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_richiesta'], $_POST['azione'])) {
    $id_richiesta = $_POST['id_richiesta'];
    $azione = $_POST['azione'];
    $stato = ($azione === 'accetta') ? 'Accettata' : 'Rifiutata';

    $stmt = $conn->prepare("UPDATE Prenotazione SET Stato = ? WHERE ID = ? AND ID_Passeggero = ?");
    $stmt->bind_param("sii", $stato, $id_richiesta, $utente_loggato);
    $stmt->execute();
    $stmt->close();

    header("Location: home.php");
    exit();
}

// Invia richiesta
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['utenti_id'])) {
    $utenti_selezionati = $_POST['utenti_id'];
    foreach ($utenti_selezionati as $utente_id) {
        if ($utente_id == $utente_loggato) continue;

        $stato = "In attesa";
        $stmt = $conn->prepare("INSERT INTO Prenotazione (ID_Passeggero, ID_Viaggio, Stato) VALUES (?, NULL, ?)");
        $stmt->bind_param("is", $utente_id, $stato);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: home.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #00aaff;
            color: white;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;
        }

        .persona {
            background: #0077aa;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 10px;
        }

        .main {
            flex-grow: 1;
            padding: 30px;
        }

        .topbar {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-button {
            background: #ff4e50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 35px;
            right: 0;
            background-color: white;
            color: black;
            min-width: 250px;
            border-radius: 8px;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.2);
            z-index: 1;
            padding: 10px;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .richiesta-item {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .richiesta-accettata {
            background-color: #d4edda;
            color: #155724;
        }

        .richiesta-rifiutata {
            background-color: #f8d7da;
            color: #721c24;
        }

        .richiesta-in {
            background-color: #ffffff;
            color: #000000;
        }

        .box {
            background: #f4f4f4;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        select, button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-top: 10px;
        }

        ul {
            padding-left: 20px;
        }

        /* Bottone cambio ruolo */
        .cambia-ruolo-btn {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            color: white;
            transition: background-color 0.4s ease, transform 0.3s ease;
        }

        .cambia-ruolo-btn:hover {
            transform: scale(1.05);
        }

        .cambia-ruolo-btn.autista {
            background-color: #28a745; /* verde */
        }

        .cambia-ruolo-btn.passeggero {
            background-color: #f0ad4e; /* arancio */
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="richieste-box">
        <div class="dropdown">
            <button class="cambia-ruolo-btn passegero">Richieste (<?= count($richieste) ?>)</button>
            <div class="dropdown-content">
                <?php foreach ($richieste as $r):
                    $stato_class = 'richiesta-' . strtolower(str_replace(' ', '', $r['Stato']));
                ?>
                    <div class="richiesta-item <?= $stato_class ?>">
                        <?= htmlspecialchars($r['Nome'] . ' ' . $r['Cognome']) ?>
                        <?php if ($r['Stato'] === "In attesa"): ?>
                            <form action="home.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_richiesta" value="<?= $r['ID'] ?>">
                                <button type="submit" name="azione" value="accetta">✅</button>
                                <button type="submit" name="azione" value="rifiuta">❌</button>
                            </form>
                        <?php else: ?>
                            <span style="margin-left: 10px; font-size: 0.9em;">
                                <?= htmlspecialchars($r['Stato']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Bottone cambio ruolo -->
    <form method="POST" style="display:inline;">
        <button type="submit" name="cambia_ruolo" class="cambia-ruolo-btn <?= strtolower($ruolo_corrente) ?>">
            Cambia ruolo (<?= $ruolo_corrente ?>)
        </button>
    </form>

    <a href="logout.php" class="logout-button">Esci</a>
</div>

<?php if ($ruolo_corrente !== 'Autista'): ?>
    <div class="sidebar">
        <h3>Altri utenti</h3>
        <?php foreach ($utenti as $persona): ?>
            <div class="persona">
                <?= htmlspecialchars($persona['Nome'] . ' ' . $persona['Cognome']) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="main">
    <h1>Benvenuto, <?= htmlspecialchars($utente['Nome'] . ' ' . $utente['Cognome']) ?>!</h1>

    <?php if ($ruolo_corrente !== 'Autista'): ?>
        <div class="box">
            <h3>Richiedi un passaggio</h3>
            <form action="home.php" method="POST">
                <label for="utenti_id[]">Seleziona uno o più utenti:</label><br>
                <select name="utenti_id[]" multiple size="5" required>
                    <?php foreach ($utenti as $persona): ?>
                        <option value="<?= $persona['ID'] ?>">
                            <?= htmlspecialchars($persona['Nome'] . ' ' . $persona['Cognome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <button type="submit">Invia richiesta</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="box">
        <h3>Viaggi effettuati</h3>
        <?php if (count($viaggi_effettuati) > 0): ?>
            <ul>
                <?php foreach ($viaggi_effettuati as $v): ?>
                    <li>
                        <strong><?= htmlspecialchars($v['Partenza']) ?></strong> - <?= htmlspecialchars($v['Data']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nessun viaggio effettuato.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
