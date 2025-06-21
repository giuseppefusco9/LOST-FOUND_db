<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) die("Connessione fallita: " . $conn->connect_error);

$sql = "
SELECT 
    D.testo AS domanda,
    O.descrizioneOggetto,
    R.testo AS risposta,
    U.nome AS nomeUtente,
    U.cognome AS cognomeUtente
FROM DOMANDE_VERIFICA D
JOIN SEGNALAZIONI S ON D.idSegnalazione = S.idSegnalazione
JOIN OGGETTI O ON S.idOggetto = O.idOggetto
JOIN UTENTI U ON S.idUtente = U.idUtente
LEFT JOIN RISPOSTE_VERIFICA R ON D.idRisposta = R.idRisposta
WHERE S.tipoSegnalazione = 0
ORDER BY D.idDomanda DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Domande e Risposte</title>
    <link rel="stylesheet" href="segnalazionestyle.css" />
</head>
<body>
    <h1>Domande di Verifica e Risposte</h1>

    <div class="form-container">
        <div class="form">
            <?php if ($result->num_rows === 0): ?>
                <p class="message">Nessuna domanda trovata.</p>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="entry">
                        <div class="oggetto"><strong>🔍 Oggetto:</strong> <?= htmlspecialchars($row['descrizioneOggetto']) ?></div>
                        <div class="domanda"><strong>❓ Domanda:</strong> <?= htmlspecialchars($row['domanda']) ?></div>

                        <?php if ($row['risposta']): ?>
                            <div class="risposta-box">
                                <?= nl2br(htmlspecialchars($row['risposta'])) ?>
                            </div>
                            <div class="utente">Risposta inviata da: <?= htmlspecialchars($row['nomeUtente']) ?> <?= htmlspecialchars($row['cognomeUtente']) ?></div>
                        <?php else: ?>
                            <div class="no-risposta">⚠️ Nessuna risposta ancora ricevuta.</div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
        <button type="button" onclick="window.location.href='dashboard_admin.php'">Torna alla Dashboard</button>
    </div>
</body>
</html>

<?php $conn->close(); ?>
