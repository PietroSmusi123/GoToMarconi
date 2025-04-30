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
      background: linear-gradient(to right, #FFD93D, #FF6F3C);
      padding: 15px 30px;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .navbar a {
      text-decoration: none;
      background-color: #5B8FB9;
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .navbar a:hover {
      background-color: #3c6e91;
    }

    .main-container {
      display: flex;
      justify-content: space-between;
      padding: 60px 80px;
      gap: 40px;
    }

    .left-section {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    .logo-box {
      background: linear-gradient(to right, #A084E8, #8BE8E5);
      color: white;
      font-size: 32px;
      font-weight: bold;
      padding: 20px 30px;
      border-radius: 20px;
      width: fit-content;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .about-box {
      background-color: #fefefe;
      border-left: 6px solid #FF6F3C;
      padding: 20px 25px;
      border-radius: 15px;
      line-height: 1.6;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .right-section {
      flex: 2;
      background-color: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }

    @media screen and (max-width: 768px) {
      .main-container {
        flex-direction: column;
        padding: 30px 20px;
      }
      .right-section, .left-section {
        width: 100%;
        text-align: center;
        padding: 20px 0;
      }
      .logo-box {
        margin: 0 auto;
      }
    }
  </style>
</head>
<body>

  <div class="navbar">
    <a href="Registrati.php">Registrati</a>
    <a href="Login.php">Accedi</a>
  </div>

  <div class="main-container">
    <div class="left-section">
      <div class="logo-box">GoToMarconi</div>
      <div class="about-box">
        <strong>Chi siamo:</strong><br>
        Arcenni Alessandro<br>
        Cipolli Lorenzo<br>
        Ciuca Matteo Ștefan<br>
        Vratsov Romanno Nikolov
      </div>
    </div>

    <div class="right-section">
      <p><strong>Benvenuto su GoToMarconi!</strong><br>La piattaforma dedicata agli studenti, docenti e genitori dell'ITIS Marconi.</p>
    </div>
  </div>

</body>
</html>
