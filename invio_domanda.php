<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testo = $_POST['testo'] ?? '';
    $idSegnalazione = $_POST['idSegnalazione'] ?? '';

    if ($testo && $idSegnalazione) {
        $stmt = $conn->prepare("INSERT INTO DOMANDE_VERIFICA (testo, idSegnalazione, idAmministratore) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $testo, $idSegnalazione, $_SESSION['admin_id']);

        if ($stmt->execute()) {
            $message = "Domanda inviata con successo!";
        } else {
            $message = "Errore nell'invio: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Compila tutti i campi.";
    }
}

$segnalazioni = $conn->query("
    SELECT S.idSegnalazione, U.nome, U.cognome, P.descrizioneOggetto 
    FROM SEGNALAZIONI S 
    JOIN UTENTI U ON S.idUtente = U.idUtente
    JOIN OGGETTI P ON S.idOggetto = P.idOggetto
    WHERE S.tipoSegnalazione = 0
    ORDER BY S.idSegnalazione DESC
");
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Invia Domanda di Verifica</title>
    <link rel="stylesheet" href="segnalazionestyle.css" />
</head>
<body>
    <h1>Invia Domanda di Verifica</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'Errore') === false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <label for="idSegnalazione">Seleziona Segnalazione:</label>
            <select name="idSegnalazione" id="idSegnalazione" required>
                <option value="">-- Seleziona --</option>
                <?php while ($row = $segnalazioni->fetch_assoc()): ?>
                    <option value="<?= $row['idSegnalazione'] ?>">
                    <?= htmlspecialchars($row['nome'] . ' ' . $row['cognome'] . ' - ' . $row['descrizioneOggetto']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="testo">Testo Domanda:</label>
            <textarea name="testo" id="testo" rows="4" required></textarea>

            <input type="submit" value="Invia Domanda" />
            <button type="button" onclick="window.location.href='dashboard_admin.php'">Torna alla Dashboard</button>
        </form>
    </div>
</body>
</html>
