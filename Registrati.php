<?php
session_start();
$conn = new mysqli("localhost", "root", "", "GoToMarconi");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$errore = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $citta = trim($_POST['citta']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

   // Verifica email o telefono già esistenti
$check = $conn->prepare("SELECT ID FROM Utente WHERE Email = ? OR Telefono = ?");
$check->bind_param("ss", $email, $telefono);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $errore = "Email o numero di telefono già registrati.";
} else {

        $stmt = $conn->prepare("INSERT INTO Utente (Nome, Cognome, Email, Telefono, Password, Citta) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nome, $cognome, $email, $telefono, $password, $citta);
        if ($stmt->execute()) {
            header("Location: Login.php");
            exit();
        } else {
            $errore = "Errore durante la registrazione.";
        }
        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Registrati - GoToMarconi</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #fdfcfb, #e2d1c3);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .input-field {
  width: 105%;
  padding: 10px;
  margin: 10px 0;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-family: 'Segoe UI', sans-serif;
}

    .navbar {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      background-color: #2f6f75;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .navbar img.logo {
      height: 50px;
    }

    .form-container {
      margin-top: 80px;
      background-color: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
      width: 400px;
    }

    h2 {
      text-align: center;
      color: #2f6f75;
    }

    input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #2f6f75;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      margin-top: 10px;
    }

    .error {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }

    .link {
      margin-top: 15px;
      text-align: center;
      font-size: 0.95em;
    }

    .link a {
      color: #007bff;
      text-decoration: none;
    }

    .link a:hover {
      text-decoration: underline;
    }

    #popup {
      display: none;
      position: fixed;
      top: 20%;
      left: 50%;
      transform: translateX(-50%);
      background: white;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      z-index: 10;
      border-radius: 10px;
      width: 300px;
    }

    #popup button {
      margin-top: 15px;
      background-color: #28a745;
    }

    #overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 5;
    }
  </style>
  <script>
    function apriPopup() {
      document.getElementById('popup').style.display = 'block';
      document.getElementById('overlay').style.display = 'block';
    }

    function accettaTermini() {
      document.getElementById('checkbox').disabled = false;
      document.getElementById('popup').style.display = 'none';
      document.getElementById('overlay').style.display = 'none';
    }
  </script>
</head>
<body>

  <div class="navbar">
    <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
  </div>

  <div id="overlay"></div>

	<div id="popup">
	  <h4>Termini e condizioni</h4>
	  <p>
		Utilizzando GoToMarconi accetti la gestione dei tuoi dati personali secondo le normative vigenti.
		I tuoi dati saranno usati solo ai fini del carpooling. <br><br>
		Puoi consultare il <a href="https://gdpr-info.eu" target="_blank" style="color:#007bff; text-decoration: underline;">Regolamento Generale sulla Protezione dei Dati (GDPR)</a> per maggiori informazioni.
	  </p>
	  <button onclick="accettaTermini()">Accetto e chiudi</button>
	</div>

  <div class="form-container">
    <h2>Crea un nuovo account</h2>
    <?php if (!empty($errore)): ?>
      <div class="error"><?= $errore ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="text" name="nome" placeholder="Nome" required>
      <input type="text" name="cognome" placeholder="Cognome" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="telefono" placeholder="Telefono" required>
   <select name="citta" class="input-field" required>
  <option value="" disabled selected>Seleziona la tua città</option>
  <option value="Lari">Lari</option>
  <option value="Pontedera">Pontedera</option>
  <option value="Calcinaia">Calcinaia</option>
  <option value="Ponsacco">Ponsacco</option>
  <option value="Santa Maria a Monte">Santa Maria a Monte</option>
  <option value="Capannoli">Capannoli</option>
  <option value="Montopoli in Val d'Arno">Montopoli in Val d'Arno</option>
  <option value="Cascina">Cascina</option>
  <option value="Palaia">Palaia</option>
  <option value="Casciana Terme Lari">Casciana Terme Lari</option>
  <option value="Vicopisano">Vicopisano</option>
  <option value="Bientina">Bientina</option>
  <option value="Castelfranco di Sotto">Castelfranco di Sotto</option>
  <option value="Crespina Lorenzana">Crespina Lorenzana</option>
  <option value="Peccioli">Peccioli</option>
  <option value="San Miniato">San Miniato</option>
  <option value="Terricciola">Terricciola</option>
  <option value="Pisa">Pisa</option>
  <option value="San Giuliano Terme">San Giuliano Terme</option>
  <option value="Chianni">Chianni</option>
  <option value="Santa Luce">Santa Luce</option>
  <option value="Montaione">Montaione</option>
</select>



      <input type="password" name="password" placeholder="Password" required>
	<p id="errorePassword" style="color:red; font-size: 0.85em; display: none;"></p>


	<div style="font-size: 0.9em; margin: 10px 0;">
	  <p>Leggi i <a href="javascript:void(0);" onclick="apriPopup()">termini e condizioni</a></p>
		<label style="display: inline-flex; align-items: center; white-space: nowrap;">
		<input type="checkbox" id="checkbox" required style="margin-right: 6px;">
		Accetto i termini
	  </label>
	</div>


      <button type="submit">Registrati</button>
    </form>

    <div class="link">
      Hai già un account? <a href="Login.php">Accedi qui</a>
    </div>
  </div>
  
  <script>
  const telefonoInput = document.querySelector('input[name="telefono"]');
  const passwordInput = document.querySelector('input[name="password"]');

  telefonoInput.addEventListener('input', () => {
    telefonoInput.setCustomValidity("");
    const val = telefonoInput.value.trim();
    if (!/^\d{10}$/.test(val)) {
      telefonoInput.setCustomValidity("Il numero di telefono deve contenere esattamente 10 cifre.");
    }
  });

  passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    const errore = document.getElementById('errorePassword');

    const almeno8 = val.length >= 8;
    const maiuscola = /[A-Z]/.test(val);
    const numero = /[0-9]/.test(val);
    const speciale = /[\W_]/.test(val);

    const errori = [];
    if (!almeno8) errori.push("• almeno 8 caratteri");
    if (!maiuscola) errori.push("• una lettera maiuscola");
    if (!numero) errori.push("• un numero");
    if (!speciale) errori.push("• un carattere speciale");

    if (errori.length > 0) {
      errore.innerHTML = "La password deve contenere:<br>" + errori.join("<br>");
      errore.style.display = "block";
      passwordInput.setCustomValidity("Password non conforme.");
    } else {
      errore.style.display = "none";
      passwordInput.setCustomValidity("");
    }
  });
</script>


</body>
</html>
