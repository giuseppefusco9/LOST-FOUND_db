<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_utente.php");
    exit;
}

// Recupera informazioni portafoglio
$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) die("Connessione fallita");

$stmt = $conn->prepare("
    SELECT P.IBAN, P.saldo
    FROM UTENTI U
    LEFT JOIN PORTAFOGLI P ON U.IBAN = P.IBAN
    WHERE U.idUtente = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$portafoglio = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Utente</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container login-container">

    <h1>Ciao, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
    <p>Benvenuto nella tua dashboard utente.</p>

    <?php if ($portafoglio): ?>
        <div class="wallet-info">
            <h3>💼 Il tuo Portafoglio</h3>
            <p><strong>IBAN:</strong> <?= htmlspecialchars($portafoglio['IBAN']) ?></p>
            <p><strong>Saldo:</strong> €<?= number_format($portafoglio['saldo'], 2, ',', '.') ?></p>
        </div>
    <?php else: ?>
        <p class="error">Nessun portafoglio associato.</p>
    <?php endif; ?>

    <div class="dashboard-flex">
        <div class="button-group">
            <button class="btn" onclick="location.href='invio_smarrimento.php'">📤 Segnala Oggetto Smarrito</button>
            <button class="btn" onclick="location.href='segnala_ritrovato.php'">📥 Segnala Oggetto Ritrovato</button>
            <button class="btn" onclick="location.href='visualizza_smarriti.php'">🔍 Visualizza Oggetti Smarriti</button>
            <button class="btn" onclick="location.href='visualizza_ritrovati.php'">🔎 Visualizza Oggetti Ritrovati</button>
            <button class="btn" onclick="location.href='rispondi_domanda.php'">📝 Rispondi a Domanda di Verifica</button>
            <button class="btn" onclick="location.href='logout.php'">🔓 Logout</button>
        </div>
    </div>
</div>
<div class="container login-container">
    <div class="dashboard-flex">
        <div class="statistics-wrapper">
            <?php include 'statistiche_categorie.php'; ?>
        </div>
    </div>
</div>
<div class="container login-container">
    <div class="dashboard-flex">
        <div class="statistics-wrapper">
            <?php include 'statistiche_luoghi.php'; ?>
        </div>
    </div>
</div>
</body>
</html>