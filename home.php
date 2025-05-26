<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$utente_loggato = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT Nome, Cognome, Email, Telefono, Ruolo, Citta FROM Utente WHERE ID = ?");
$stmt->bind_param("i", $utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$utente = $result->fetch_assoc();
$stmt->close();

$ruolo_corrente = $utente['Ruolo'] ?? "Nessuno";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_richiesta'], $_POST['azione'])) {
    $id_richiesta = intval($_POST['id_richiesta']);
    $azione = $_POST['azione'];
    $stato = ($azione === 'accetta') ? 'Accettata' : 'Rifiutata';

    // Aggiorna lo stato della richiesta
    $stmt = $conn->prepare("UPDATE Prenotazione SET Stato = ? WHERE ID = ?");
    $stmt->bind_param("si", $stato, $id_richiesta);
    $stmt->execute();
    $stmt->close();

    // Se accettata, decrementa posti
    if ($stato === 'Accettata') {
        $stmt = $conn->prepare("
            UPDATE Viaggio
            SET Posti = Posti - 1
            WHERE ID = (SELECT ID_Viaggio FROM Prenotazione WHERE ID = ?) AND Posti > 0
        ");
        $stmt->bind_param("i", $id_richiesta);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: home.php");
    exit();
}


$autisti_disponibili = [];
if ($ruolo_corrente === 'Passeggero') {
    $citta_utente = $utente['Citta'];

    $stmt = $conn->prepare("
        SELECT u.ID, u.Nome, u.Cognome, u.FotoProfilo,
               ROUND(AVG(r.Voto), 1) as MediaVoto
        FROM Utente u
        JOIN Viaggio v ON v.ID_Autista = u.ID
        JOIN Viaggio_Destinazione vd ON vd.ID_Viaggio = v.ID
        LEFT JOIN Recensione r ON r.ID_Utente = u.ID
        WHERE u.Ruolo = 'Autista' AND vd.Citta = ?
        GROUP BY u.ID
    ");
    $stmt->bind_param("s", $citta_utente);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $autisti_disponibili[] = $row;
    }
    $stmt->close();
}

$viaggi_disponibili = [];
if ($ruolo_corrente === 'Passeggero') {
    $citta_utente = $utente['Citta'];
    $stmt = $conn->prepare("
        SELECT v.ID, v.Partenza, v.Data, v.Posti, u.Nome AS AutistaNome, u.Cognome AS AutistaCognome
        FROM Viaggio v
        JOIN Viaggio_Destinazione vd ON vd.ID_Viaggio = v.ID
        JOIN Utente u ON u.ID = v.ID_Autista
        WHERE vd.Citta = ?
        ORDER BY v.Data ASC
    ");
    $stmt->bind_param("s", $citta_utente);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($riga = $result->fetch_assoc()) {
        $viaggi_disponibili[] = $riga;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['crea_viaggio'])) {
    $data_viaggio = $_POST['data_viaggio'];
    $posti = intval($_POST['posti_disponibili']);
    $note = $_POST['note'] ?? '';
    $partenza = 'Casa → Lavoro';

    if (strtotime($data_viaggio) < strtotime(date("Y-m-d"))) {
        echo "<script>alert('La data non può essere nel passato.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO Viaggio (ID_Autista, Partenza, Data, Posti, Note) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $utente_loggato, $partenza, $data_viaggio, $posti, $note);
        if ($stmt->execute()) {
            echo "<script>alert('Viaggio creato con successo!');</script>";
            $id_viaggio = $stmt->insert_id;

            if (!empty($_POST['citta_disponibili'])) {
                $stmt_dest = $conn->prepare("INSERT INTO Viaggio_Destinazione (ID_Viaggio, Citta) VALUES (?, ?)");
                foreach ($_POST['citta_disponibili'] as $citta) {
                    $stmt_dest->bind_param("is", $id_viaggio, $citta);
                    $stmt_dest->execute();
                }
                $stmt_dest->close();
            }
        } else {
            echo "<script>alert('Errore nella creazione del viaggio');</script>";
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cambia_ruolo'])) {
    $nuovo_ruolo = ($ruolo_corrente === 'Autista') ? 'Passeggero' : 'Autista';
    $stmt = $conn->prepare("UPDATE Utente SET Ruolo = ? WHERE ID = ?");
    $stmt->bind_param("si", $nuovo_ruolo, $utente_loggato);
    $stmt->execute();
    $stmt->close();
    header("Location: home.php");
    exit();
}

$citta_utente = $utente['Citta'];
$result = $conn->prepare("
    SELECT ID, Nome, Cognome 
    FROM Utente 
    WHERE ID != ? AND Ruolo = 'Autista' AND Citta = ?
");
$result->bind_param("is", $utente_loggato, $citta_utente);
$result->execute();
$res = $result->get_result();
$utenti = [];
while ($row = $res->fetch_assoc()) {
    $utenti[] = $row;
}
$result->close();

$viaggi_effettuati = [];
if ($ruolo_corrente === 'Autista') {
    $stmt = $conn->prepare("SELECT Partenza, Data FROM Viaggio WHERE ID_Autista = ? ORDER BY Data DESC");
    $stmt->bind_param("i", $utente_loggato);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($riga = $result->fetch_assoc()) {
        $viaggi_effettuati[] = $riga;
    }
    $stmt->close();
}

$richieste = [];

if ($ruolo_corrente === 'Passeggero') {
    $stmt = $conn->prepare("
        SELECT p.ID, u.ID as UtenteID, u.Nome, u.Cognome, p.Stato,
               veic.Modello, veic.Colore, veic.Targa
        FROM Prenotazione p
        JOIN Viaggio v ON v.ID = p.ID_Viaggio
        JOIN Utente u ON u.ID = v.ID_Autista
        LEFT JOIN Veicolo veic ON veic.ID_Proprietario = u.ID
        WHERE p.ID_Passeggero = ?
        ORDER BY p.ID DESC
    ");
    $stmt->bind_param("i", $utente_loggato);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $richieste[] = $row;
    }
    $stmt->close();
}


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
            min-width: 280px;
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
            gap: 10px;
        }

        .richiesta-accettata {
            background-color: #d4edda;
            color: #155724;
        }

        .richiesta-rifiutata {
            background-color: #f8d7da;
            color: #721c24;
        }

        .richiesta-inattesa {
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
            background-color: #28a745;
        }

        .cambia-ruolo-btn.passeggero {
            background-color: #f0ad4e;
        }

        .chat-button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 0.9em;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="topbar">
    <?php if ($ruolo_corrente === 'Autista'): ?>
        <div class="richieste-box">
            <div class="dropdown">
                <button class="cambia-ruolo-btn passeggero">Richieste (<?= count($richieste) ?>)</button>
                <div class="dropdown-content">
                    <?php foreach ($richieste as $r): 
                        $stato_class = 'richiesta-' . strtolower(str_replace(' ', '', $r['Stato']));
                    ?>
                        <div class="richiesta-item <?= $stato_class ?>">
                            <?= htmlspecialchars($r['Nome'] . ' ' . $r['Cognome']) ?>
                            <div>
                            <?php if ($r['Stato'] === "In attesa"): ?>
                                <form action="gestisci_richiesta.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="id_richiesta" value="<?= $r['ID'] ?>">
                                    <button type="submit" name="azione" value="accetta">✅</button>
                                    <button type="submit" name="azione" value="rifiuta">❌</button>
                                </form>
                            <?php else: ?>
                                <span><?= htmlspecialchars($r['Stato']) ?></span>
                                <?php if (strtolower($r['Stato']) === 'accettata'): ?>
                                    <a class="chat-button" href="chat.php?utente_id=<?= urlencode($r['UtenteID']) ?>">💬 Chat</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Pulsanti sempre visibili -->
    <form method="POST" style="display:inline;">
        <?php $ruolo_opposto = ($ruolo_corrente === 'Autista') ? 'Passeggero' : 'Autista'; ?>
        <button type="submit" name="cambia_ruolo" class="cambia-ruolo-btn <?= strtolower($ruolo_opposto) ?>">
            Cambia ruolo in <?= $ruolo_opposto ?>
        </button>
    </form>

    <a href="logout.php" class="logout-button">Esci</a>
</div>


<?php if ($ruolo_corrente === 'Passeggero'): ?>
    <div class="sidebar">
        <h3>Utenti che passano per la tua città</h3>

        <?php if (count($autisti_disponibili) > 0): ?>
            <?php foreach ($autisti_disponibili as $a): ?>
                <div class="persona" style="display: flex; align-items: center; gap: 10px;">
                    <?php if (!empty($a['FotoProfilo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($a['FotoProfilo']) ?>" width="40" height="40" style="border-radius: 50%;">
                    <?php else: ?>
                        <div style="width: 40px; height: 40px; background: #ccc; border-radius: 50%; display:flex; align-items:center; justify-content:center;">👤</div>
                    <?php endif; ?>

                    <div>
                        <a href="profilo_autista.php?id=<?= $a['ID'] ?>" style="text-decoration: none; color: inherit;">
						<strong><?= htmlspecialchars($a['Nome'] . ' ' . $a['Cognome']) ?></strong><br>
						<small>⭐ <?= $a['MediaVoto'] ?? 'N.D.' ?> / 5</small>
						</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="margin-top:10px;">Nessun autista trovato.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>


<div class="main">
    <h1>Benvenuto, <?= htmlspecialchars($utente['Nome'] . ' ' . $utente['Cognome']) ?>!</h1>

	<?php if ($ruolo_corrente === 'Passeggero'): ?>
	<div class="box">
		<h3>Viaggi disponibili nella tua città (<?= htmlspecialchars($citta_utente) ?>)</h3>
		<?php if (count($viaggi_disponibili) > 0): ?>
			<ul>
				<?php foreach ($viaggi_disponibili as $v): ?>
					<li>
						<strong><?= htmlspecialchars($v['Partenza']) ?></strong> – 
						<?= htmlspecialchars(date("d/m/Y", strtotime($v['Data']))) ?> – 
						<?= htmlspecialchars($v['AutistaNome'] . ' ' . $v['AutistaCognome']) ?> – 
						Posti: <?= htmlspecialchars($v['Posti']) ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else: ?>
			<p>Nessun viaggio disponibile per la tua città al momento.</p>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	
	<?php if ($ruolo_corrente === 'Autista'): ?>
    <div class="box">
        <h3>Organizza un nuovo viaggio</h3>
        <form action="home.php" method="POST">
            <label for="data_viaggio">Data del viaggio:</label><br>
            <input type="date" name="data_viaggio" required><br><br>

            <label for="posti_disponibili">Posti disponibili:</label><br>
            <input type="number" name="posti_disponibili" min="1" max="8" required><br><br>

            <label for="citta_disponibili[]">Città disponibili (tieni Ctrl per selezioni multiple):</label><br>
			<select name="citta_disponibili[]" multiple size="5" required>
				<option value="Roma">Roma</option>
				<option value="Firenze">Firenze</option>
				<option value="Bologna">Bologna</option>
				<option value="Milano">Milano</option>
				<option value="Napoli">Napoli</option>
			</select><br><br>

			<label for="note">Note aggiuntive:</label><br>
			<textarea name="note" rows="3" placeholder="..."></textarea>


            <button type="submit" name="crea_viaggio">Crea viaggio</button>
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