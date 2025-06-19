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
<div class="container login-container">
    <h1>Ciao, <?=htmlspecialchars($_SESSION['admin_name'])?>!</h1>
    <p>Benvenuto nella dashboard amministratore.</p>

    <div class="button-group">
        <button class="btn" onclick="location.href='ban_utente.php'">🚫 Banna Utente</button>
        <button class="btn" onclick="location.href='visualizza_smarriti.php'">🔍 Visualizza Oggetti Smarriti</button>
        <button class="btn" onclick="location.href='visualizza_ritrovati.php'">🔎 Visualizza Oggetti Ritrovati</button>
        <button class="btn" onclick="location.href='invia_domanda_verifica.php'">❓ Invia Domanda di Verifica</button>
        <button class="btn" onclick="location.href='visualizza_domande.php'">📂 Visualizza Domande con Risposte</button>
        <button class="btn" onclick="location.href='genera_restituzione.php'">✅ Genera Restituzione Oggetto</button>
        <button class="btn" onclick="location.href='statistiche_categorie.php'">📊 Statistiche per Categorie</button>
        <button class="btn" onclick="location.href='statistiche_luoghi.php'">📍 Statistiche per Luoghi</button>
        <button class="btn" onclick="location.href='logout.php'">🔓 Logout</button>
    </div>
</div>
</body>
</html>
