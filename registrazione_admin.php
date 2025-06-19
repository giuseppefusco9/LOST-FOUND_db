<?php
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $cognome = $_POST['cognome'] ?? '';
    $indirizzo = $_POST['indirizzo'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = new mysqli("localhost", "root", "", "lostfound_db");
    if ($conn->connect_error) die("Connessione fallita");

    $stmt = $conn->prepare("SELECT idAmministratore FROM AMMINISTRATORI WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $message = "Email già registrata.";
    } else {
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO AMMINISTRATORI (email, nome, cognome, indirizzo, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $email, $nome, $cognome, $indirizzo, $password);

        if ($stmt->execute()) {
            header("Location: login_admin.php");
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
    <title>Registrazione Amministratore</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container login-container">
    <h2>Registrazione Amministratore</h2>
    <?php if ($message): ?>
        <p class="error"><?=htmlspecialchars($message)?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Email:<br><input type="email" name="email" required /></label><br><br>
        <label>Nome:<br><input type="text" name="nome" required /></label><br><br>
        <label>Cognome:<br><input type="text" name="cognome" required /></label><br><br>
        <label>Indirizzo:<br><input type="text" name="indirizzo" required /></label><br><br>
        <label>Password:<br><input type="password" name="password" required /></label><br><br>
        <button type="submit" class="btn">Registrati</button>
    </form>
    <p><a href="index.php">Torna alla pagina principale</a></p>
</div>
</body>
</html>
