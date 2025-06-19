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
<div class="container">
    <h1>Ciao, <?=htmlspecialchars($_SESSION['user_name'])?>!</h1>
    <p>Benvenuto nella tua dashboard utente.</p>

    <ul class="dashboard-menu">
        <li><a href="segnala_smarrito.php">📤 Segnala Oggetto Smarrito (OP3)</a></li>
        <li><a href="segnala_ritrovato.php">📥 Segnala Oggetto Ritrovato (OP4)</a></li>
        <li><a href="visualizza_smarriti.php">🔍 Visualizza Oggetti Smarriti (OP5)</a></li>
        <li><a href="visualizza_ritrovati.php">🔎 Visualizza Oggetti Ritrovati (OP6)</a></li>
        <li><a href="portafoglio.php">👛 Il Mio Portafoglio (OP7)</a></li>
        <li><a href="rispondi_domanda.php">📝 Rispondi a Domanda di Verifica (OP9)</a></li>
        <li><a href="statistiche_categorie.php">📊 Statistiche per Categorie (OP12)</a></li>
        <li><a href="statistiche_luoghi.php">📍 Statistiche per Luoghi (OP13)</a></li>
        <li><a href="logout.php" class="btn">🔓 Logout</a></li>
    </ul>
</div>
</body>
</html>
