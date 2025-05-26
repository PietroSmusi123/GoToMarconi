<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Conferma Eliminazione</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f9f9f9;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .card {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 500px;
    }
    h2 {
      color: #dc3545;
    }
    .button {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      margin: 10px;
      cursor: pointer;
    }
    .yes {
      background-color: #dc3545;
      color: white;
    }
    .no {
      background-color: #2f6f75;
      color: white;
    }
    a.button {
      text-decoration: none;
      display: inline-block;
    }
  </style>
</head>
<body>

<div class="card">
  <h2>Sei sicuro di voler eliminare il tuo account?</h2>
  <p>Questa azione è <strong>irreversibile</strong> e comporterà la perdita di tutti i tuoi dati.</p>
  <form method="post" action="elimina_account.php">
    <button class="button yes" type="submit">✅ Conferma eliminazione</button>
    <a href="profilo_autista.php" class="button no">↩ Annulla</a>
  </form>
</div>

</body>
</html>
