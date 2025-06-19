<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Amministratore</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container">
    <h1>Ciao, <?=htmlspecialchars($_SESSION['admin_name'])?>!</h1>
    <p>Benvenuto nella dashboard amministratore.</p>

    <ul class="dashboard-menu">
        <li><a href="ban_utente.php">🚫 Banna Utente (OP2)</a></li>
        <li><a href="visualizza_smarriti.php">🔍 Visualizza Oggetti Smarriti (OP5)</a></li>
        <li><a href="visualizza_ritrovati.php">🔎 Visualizza Oggetti Ritrovati (OP6)</a></li>
        <li><a href="invia_domanda_verifica.php">❓ Invia Domanda di Verifica (OP8)</a></li>
        <li><a href="visualizza_domande.php">📂 Visualizza Domande con Risposte (OP11)</a></li>
        <li><a href="genera_restituzione.php">✅ Genera Restituzione Oggetto (OP10)</a></li>
        <li><a href="statistiche_categorie.php">📊 Statistiche per Categorie (OP12)</a></li>
        <li><a href="statistiche_luoghi.php">📍 Statistiche per Luoghi (OP13)</a></li>
        <li><a href="logout.php" class="btn">🔓 Logout</a></li>
    </ul>
</div>
</body>
</html>
