<?php
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $cognome = $_POST['cognome'] ?? '';
    $password = $_POST['password'] ?? '';
    $iban = $_POST['iban'] ?? '';

    $conn = new mysqli("localhost", "root", "", "lostfound_db");
    if ($conn->connect_error) die("Connessione fallita");

    // Verifica email unica
    $stmt = $conn->prepare("SELECT idUtente FROM UTENTI WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $message = "Email già registrata.";
    } else {
        // Inserimento portafoglio se non esiste
        $stmt->close();

        $stmt = $conn->prepare("SELECT IBAN FROM PORTAFOGLI WHERE IBAN = ?");
        $stmt->bind_param("s", $iban);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 0) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO PORTAFOGLI (IBAN, saldo) VALUES (?, 0)");
            $stmt->bind_param("s", $iban);
            $stmt->execute();
        }

        $stmt->close();

        // Inserisco utente
        $stmt = $conn->prepare("INSERT INTO UTENTI (email, nome, cognome, password, IBAN) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $email, $nome, $cognome, $password, $iban);

        if ($stmt->execute()) {
            header("Location: login_utente.php");
            exit;
        } else {
            $message = "Errore nella registrazione.";
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Registrazione Utente</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container login-container">
    <h2>Registrazione Utente</h2>
    <?php if ($message): ?>
        <p class="error"><?=htmlspecialchars($message)?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Email:<br><input type="email" name="email" required /></label><br><br>
        <label>Nome:<br><input type="text" name="nome" required /></label><br><br>
        <label>Cognome:<br><input type="text" name="cognome" required /></label><br><br>
        <label>Password:<br><input type="password" name="password" required /></label><br><br>
        <label>IBAN:<br><input type="text" name="iban" required /></label><br><br>
        <button type="submit" class="btn">Registrati</button>
    </form>
    <p><a href="index.php">Torna alla pagina principale</a></p>
</div>
</body>
</html>
