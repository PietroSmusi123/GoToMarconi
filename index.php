<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>GoToMarconi - Benvenuto</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #fdfcfb, #e2d1c3);
      color: #333;
    }

    .navbar {
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

    .navbar .nav-links a {
      text-decoration: none;
      background-color: #5B8FB9;
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: bold;
      margin-left: 10px;
      transition: background 0.3s ease;
    }

    .navbar .nav-links a:hover {
      background-color: #3c6e91;
    }

    .hero {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: calc(100vh - 100px);
      text-align: center;
      padding: 20px;
    }

    .hero h1 {
      font-size: 3em;
      color: #2f6f75;
      margin-bottom: 10px;
    }

    .hero p {
      font-size: 1.2em;
      color: #444;
      max-width: 600px;
    }

    .hero a {
      margin-top: 30px;
      display: inline-block;
      padding: 12px 25px;
      background-color: #2f6f75;
      color: white;
      border-radius: 30px;
      text-decoration: none;
      font-weight: bold;
    }

    .hero a:hover {
      background-color: #224c50;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <img src="GoToMarconi_logo.jpg" alt="Logo GoToMarconi" class="logo">
    <div class="nav-links">
      <a href="Login.php">Accedi</a>
      <a href="Registrati.php">Registrati</a>
    </div>
  </div>

  <div class="hero">
    <h1>Benvenuto su GoToMarconi</h1>
    <p>Unisciti alla nostra piattaforma di carpooling intelligente per viaggiare in modo più sostenibile, economico e sociale. Siamo qui per connettere chi viaggia verso la stessa direzione.</p>
    <a href="Registrati.php">Inizia ora</a>
  </div>
</body>
</html>
