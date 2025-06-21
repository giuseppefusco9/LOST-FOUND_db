<?php
session_start();

// Verifica login utente
if (!isset($_SESSION['user_id'])) {
    header("Location: login_utente.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

$messages = []; // Array per messaggi per ciascun form, indicizzati per idDomanda

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idDomanda = $_POST['idDomanda'] ?? '';
    $testo = trim($_POST['testoRisposta'] ?? '');

    if ($idDomanda && $testo) {
        // Inserimento risposta
        $stmt = $conn->prepare("INSERT INTO RISPOSTE_VERIFICA (testo, idUtente) VALUES (?, ?)");
        $stmt->bind_param("si", $testo, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $idRisposta = $conn->insert_id;
            $stmt->close();

            // Aggiorna domanda con idRisposta, solo se non è già stata risposta
            $upd = $conn->prepare("UPDATE DOMANDE_VERIFICA SET idRisposta = ? WHERE idDomanda = ? AND idRisposta IS NULL");
            $upd->bind_param("ii", $idRisposta, $idDomanda);
            $upd->execute();
            $upd->close();

            $messages[$idDomanda] = ['success' => "Risposta inviata correttamente."];
        } else {
            $messages[$idDomanda] = ['error' => "Errore nell'invio della risposta."];
        }
    } else {
        $messages[$idDomanda] = ['error' => "Compila il campo della risposta."];
    }
}

// Recupero domande aperte dell’utente, con descrizione oggetto
$sql = "
SELECT D.idDomanda, D.testo AS testoDomanda, O.descrizioneOggetto
FROM DOMANDE_VERIFICA D
JOIN SEGNALAZIONI S ON D.idSegnalazione = S.idSegnalazione
JOIN OGGETTI O ON S.idOggetto = O.idOggetto
WHERE D.idRisposta IS NULL
  AND S.idUtente = ?
ORDER BY D.idDomanda DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Rispondi alle Domande di Verifica</title>
    <link rel="stylesheet" href="segnalazionestyle.css" />
</head>
<body>
    <h1>Domande di Verifica Aperte</h1>

    <div class="form-container">
        <?php if ($result->num_rows === 0): ?>
            <p>Non hai domande di verifica da completare.</p>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): 
                $idDomanda = $row['idDomanda'];
            ?>
                <form method="POST" class="entry">
                    <p class="oggetto"><strong>Oggetto:</strong> <?= htmlspecialchars($row['descrizioneOggetto']) ?></p>
                    <p class="domanda"><strong>Domanda:</strong> <?= htmlspecialchars($row['testoDomanda']) ?></p>

                    <?php if (isset($messages[$idDomanda])): ?>
                        <?php if (isset($messages[$idDomanda]['success'])): ?>
                            <div class="message success"><?= htmlspecialchars($messages[$idDomanda]['success']) ?></div>
                        <?php elseif (isset($messages[$idDomanda]['error'])): ?>
                            <div class="message error"><?= htmlspecialchars($messages[$idDomanda]['error']) ?></div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <input type="hidden" name="idDomanda" value="<?= $idDomanda ?>" />
                    <textarea name="testoRisposta" required placeholder="Scrivi la tua risposta..."></textarea>

                    <input type="submit" value="Invia risposta" />
                </form>
            <?php endwhile; ?>
        <?php endif; ?>
        <button type="button" onclick="window.location.href='dashboard_utente.php'">Torna alla dashboard</button>
    </div>
</body>
</html>

<?php $conn->close(); ?>

