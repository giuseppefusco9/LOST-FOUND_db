<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_utente.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Connessione DB
    $conn = new mysqli("localhost", "root", "", "lostfound_db");
    if ($conn->connect_error) die("Connessione fallita");

    $stmt = $conn->prepare("SELECT idUtente, password, nome, ban FROM UTENTI WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['ban']) {
            $message = "Il tuo account è stato bannato. Ciao ciao.";
        } else if ($row['password'] === $password) {
            $_SESSION['user_id'] = $row['idUtente'];
            $_SESSION['user_name'] = $row['nome'];
            header("Location: dashboard_utente.php");
            exit;
        } else {
            $message = "Password errata.";
        }
    } else {
        $message = "Email non trovata.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Login Utente</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container login-container">
    <h2>Login Utente</h2>
    <?php if ($message): ?>
        <p class="error"><?=htmlspecialchars($message)?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Email:<br><input type="email" name="email" required /></label><br><br>
        <label>Password:<br><input type="password" name="password" required /></label><br><br>
        <button type="submit" class="btn">Accedi</button>
    </form>
    <p><a href="index.php">Torna alla pagina principale</a></p>
</div>
</body>
</html>
