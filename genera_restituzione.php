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
    $citta = $_POST['citta'] ?? '';
    $cap = $_POST['cap'] ?? '';
    $idSegnalazione1 = $_POST['idSegnalazione1'] ?? '';
    $idSegnalazione2 = $_POST['idSegnalazione2'] ?? '';
    $idAmministratore = $_SESSION['admin_id'];

    // Validazione base
    if ($indirizzo && $citta && $cap && $idSegnalazione1 && $idSegnalazione2) {

        // **Controllo se il luogo esiste già**
        $checkLuogo = $conn->prepare("SELECT indirizzo FROM LUOGHI WHERE indirizzo = ? AND cap = ?");
        $checkLuogo->bind_param("ss", $indirizzo, $cap);
        $checkLuogo->execute();
        $resultLuogo = $checkLuogo->get_result();

        if ($resultLuogo->num_rows === 0) {
            // Luogo non esiste, lo inserisco
            $insertLuogo = $conn->prepare("INSERT INTO LUOGHI (indirizzo, cap, citta) VALUES (?, ?, ?)");
            $insertLuogo->bind_param("sss", $indirizzo, $cap, $citta);
            $insertLuogo->execute();
            $insertLuogo->close();
        }
        $checkLuogo->close();

        // Inserimento restituzione
        $stmt = $conn->prepare("
            INSERT INTO restituzioni (data, ora, indirizzo, cap, idAmministratore, idSegnalazione1, idSegnalazione2)
            VALUES (CURRENT_DATE(), CURRENT_TIME(), ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssiii", $indirizzo, $cap, $idAmministratore, $idSegnalazione1, $idSegnalazione2);

        if ($stmt->execute()) {
        // Aggiorna lo stato delle due segnalazioni
        $update = $conn->prepare("UPDATE segnalazioni SET stato = 'restituito' WHERE idSegnalazione = ? OR idSegnalazione = ?");
        $update->bind_param("ii", $idSegnalazione1, $idSegnalazione2);
        $update->execute();
        $update->close();

        // Recupera l'importo della ricompensa associata alla segnalazione di smarrimento (idSegnalazione1)
        $ricompensaQuery = $conn->prepare("
            SELECT r.importo 
            FROM segnalazioni s
            JOIN ricompense r ON s.idRicompensa = r.idRicompensa
            WHERE s.idSegnalazione = ?
        ");
        $ricompensaQuery->bind_param("i", $idSegnalazione1);
        $ricompensaQuery->execute();
        $ricompensaResult = $ricompensaQuery->get_result();

        if ($ricompensaResult->num_rows > 0) {
            $ricompensa = $ricompensaResult->fetch_assoc()['importo'];
            $ricompensaQuery->close();

            // Trova l'utente che ha fatto la segnalazione di ritrovamento (idSegnalazione2)
            $utenteQuery = $conn->prepare("SELECT idUtente FROM segnalazioni WHERE idSegnalazione = ?");
            $utenteQuery->bind_param("i", $idSegnalazione2);
            $utenteQuery->execute();
            $utenteResult = $utenteQuery->get_result();

            if ($utenteResult->num_rows > 0) {
                $idUtenteRitrovatore = $utenteResult->fetch_assoc()['idUtente'];
                $utenteQuery->close();

                // Accredita la ricompensa al portafoglio dell'utente ritrovatore
                $updateSaldo = $conn->prepare("UPDATE portafogli SET saldo = saldo + ? WHERE idUtente = ?");
                $updateSaldo->bind_param("di", $ricompensa, $idUtenteRitrovatore);
                $updateSaldo->execute();
                $updateSaldo->close();
            } else {
                $messaggio = "⚠️ Utente ritrovatore non trovato.";
            }
        } else {
            $messaggio = "⚠️ Nessuna ricompensa associata alla segnalazione di smarrimento.";
        }

        if (empty($messaggio)) {
            $messaggio = "Restituzione inserita con successo e ricompensa accreditata!";
        }
        } else {
            $messaggio = "Errore durante l'inserimento: " . $stmt->error;
        }


        $stmt->close();
    } else {
        $messaggio = "Tutti i campi sono obbligatori.";
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

        <label for="citta">Città:</label>
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
