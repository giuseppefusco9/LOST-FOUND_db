<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_utente.php");
    exit;
}
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

    <div class="button-group">
        <button class="btn" onclick="location.href='invio_smarrimento.php'">📤 Segnala Oggetto Smarrito</button>
        <button class="btn" onclick="location.href='segnala_ritrovato.php'">📥 Segnala Oggetto Ritrovato</button>
        <button class="btn" onclick="location.href='visualizza_smarriti.php'">🔍 Visualizza Oggetti Smarriti</button>
        <button class="btn" onclick="location.href='visualizza_ritrovati.php'">🔎 Visualizza Oggetti Ritrovati</button>
        <button class="btn" onclick="location.href='portafoglio.php'">👛 Il Mio Portafoglio</button>
        <button class="btn" onclick="location.href='rispondi_domanda.php'">📝 Rispondi a Domanda di Verifica</button>
        <button class="btn" onclick="location.href='statistiche_categorie.php'">📊 Statistiche per Categorie</button>
        <button class="btn" onclick="location.href='logout.php'">🔓 Logout</button>
    </div>
</div>
</body>
</html>