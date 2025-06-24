<?php
session_start();

// Controllo accesso solo per admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_utente.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recupero dati dal form
    $indirizzo = $_POST['indirizzo'] ?? '';
    $cap = $_POST['cap'] ?? '';
    $idSegnalazione1 = $_POST['idSegnalazione1'] ?? '';
    $idSegnalazione2 = $_POST['idSegnalazione2'] ?? '';
    $idAmministratore = $_SESSION['admin_id'];

    // Validazione base
    if ($indirizzo && $cap && $idSegnalazione1 && $idSegnalazione2) {
        // Inserimento
        $stmt = $conn->prepare("
            INSERT INTO restituzioni (data, ora, indirizzo, cap, idAmministratore, idSegnalazione1, idSegnalazione2)
            VALUES (CURRENT_DATE(), CURRENT_TIME(), ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssii", $indirizzo, $cap, $idAmministratore, $idSegnalazione1, $idSegnalazione2);

        if ($stmt->execute()) {
            $messaggio = "✅ Restituzione inserita con successo!";
        } else {
            $messaggio = "❌ Errore durante l'inserimento: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $messaggio = "⚠️ Tutti i campi sono obbligatori.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Inserimento Restituzione</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-section">
    <h1>📦 Inserisci una Restituzione</h1>
    <?php if (!empty($messaggio)): ?>
        <p><strong><?= htmlspecialchars($messaggio) ?></strong></p>
    <?php endif; ?>
    <form method="POST" action="genera_restituzione.php">
        <label for="indirizzo">Indirizzo:</label>
        <input type="text" name="indirizzo" id="indirizzo" required>

        <label for="cap">Città:</label>
        <input type="text" name="citta" id="citta" required>

        <label for="cap">CAP:</label>
        <input type="text" name="cap" id="cap" required>

        <label for="idSegnalazione1">ID Segnalazione Smarrimento:</label>
        <input type="number" name="idSegnalazione1" id="idSegnalazione1" required>

        <label for="idSegnalazione2">ID Segnalazione Ritrovamento:</label>
        <input type="number" name="idSegnalazione2" id="idSegnalazione2" required>

        <button class="btn" type="submit">Inserisci Restituzione</button>
    </form>
    <a href="dashboard_admin.php" class="btn">Torna alla Dashboard</a>
</div>
</body>
</html>
