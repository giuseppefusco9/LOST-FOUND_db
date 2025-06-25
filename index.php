<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login</title>
<style>
  /* Body e centro verticale+orizzontale */
  body, html {
    height: 100%;
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* Box rettangolare */
  .login-box {
    background: white;
    width: 350px;
    padding: 40px 30px;
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    text-align: center;
  }

  /* Titolo */
  .login-box h1 {
    margin: 0 0 30px 0;
    font-weight: 700;
    font-size: 28px;
    color: #333;
  }

  /* Bottone full-width e verticali */
  .btn {
    display: block;
    width: 100%;
    padding: 14px 0;
    margin-bottom: 20px;
    font-size: 16px;
    font-weight: 600;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }
  .btn:hover {
    background-color: #0056b3;
  }

  /* Link amministratore */
  .admin-link {
    font-size: 14px;
    color: #555;
    text-decoration: underline;
    cursor: pointer;
    display: inline-block;
    margin-top: 10px;
    transition: color 0.3s ease;
  }
  .admin-link:hover {
    color: #007bff;
  }
</style>
</head>
<body>

  <div class="login-box">
    <h1>Benvenuto</h1>
    <a href="registrazione_utente.php" class="btn">Registrati</a>
    <a href="login_utente.php" class="btn">Accedi</a>
    <a href="login_admin.php" class="admin-link">Sei un amministratore?</a>
  </div>

</body>
</html>
