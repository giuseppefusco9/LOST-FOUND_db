<?php
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Campi provenienti dal form
    $email    = $_POST['email']    ?? '';
    $nome     = $_POST['nome']     ?? '';
    $cognome  = $_POST['cognome']  ?? '';
    $password = $_POST['password'] ?? ''; 
    $iban     = $_POST['iban']     ?? '';

    // Connessione al DB
    $conn = new mysqli("localhost", "root", "", "lostfound_db");
    if ($conn->connect_error) die("Connessione fallita");

    /* === 1. Controllo email duplicata =================================== */
    $stmt = $conn->prepare("SELECT idUtente FROM UTENTI WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $message = "Email già registrata.";
    } else {
        $stmt->close();

        /* === 2. Controllo IBAN duplicato ================================= */
        $stmt = $conn->prepare("SELECT IBAN FROM PORTAFOGLI WHERE IBAN = ?");
        $stmt->bind_param("s", $iban);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "IBAN già associato a un utente.";
        } else {
            $stmt->close();

            /* === 3. Inizio transazione (facoltativo ma consigliato) ======= */
            $conn->begin_transaction();

            try {
                /* 3a. Inserisco l'utente */
                $stmt = $conn->prepare(
                    "INSERT INTO UTENTI (email, nome, cognome, password)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param("ssss", $email, $nome, $cognome, $password);
                $stmt->execute();
                $idUtente = $stmt->insert_id;
                $stmt->close();

                /* 3b. Inserisco il portafoglio legato all'utente appena creato */
                $stmt = $conn->prepare(
                    "INSERT INTO PORTAFOGLI (IBAN, saldo, IdUtente)
                     VALUES (?, 0, ?)"
                );
                $stmt->bind_param("si", $iban, $idUtente);
                $stmt->execute();
                $stmt->close();

                /* 3c. Commit */
                $conn->commit();
                header("Location: dashboard_utente.php");
                exit;

            } catch (Exception $e) {
                /* Rollback in caso di errore */
                $conn->rollback();
                $message = "Errore nella registrazione: " . $e->getMessage();
            }
        }
    }

    /* Chiudo la connessione */
    if (isset($stmt) && $stmt) $stmt->close();
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
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Email:<br>
            <input type="email" name="email" required />
        </label><br><br>

        <label>Nome:<br>
            <input type="text" name="nome" required />
        </label><br><br>

        <label>Cognome:<br>
            <input type="text" name="cognome" required />
        </label><br><br>

        <label>Password:<br>
            <input type="password" name="password" maxlength="12" required />
        </label><br><br>

        <label>IBAN:<br>
            <input type="text" name="iban" maxlength="34" required />
        </label><br><br>

        <button type="submit" class="btn">Registrati</button>
    </form>

    <p><a href="index.php">Torna alla pagina principale</a></p>
</div>
</body>
</html>
