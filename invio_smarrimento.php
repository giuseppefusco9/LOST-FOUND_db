<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_utente.php");
    exit;
}

$message = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "lostfound_db");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $conn->autocommit(false);

    $idUtente    = $_SESSION['user_id'];
    $indirizzo   = trim($_POST['indirizzo'] ?? '');
    $cap         = trim($_POST['cap'] ?? '');
    $citta       = trim($_POST['citta'] ?? '');
    $descrizione = trim($_POST['descrizione'] ?? '');
    $categoria   = (int)($_POST['categoria'] ?? 0);
    $ricompensa  = isset($_POST['ricompensa']) && $_POST['ricompensa'] !== '' ? (float)$_POST['ricompensa'] : null;

    // Validazione
    if ($indirizzo === '' || $cap === '' || $citta === '' || $descrizione === '' || $categoria <= 0) {
        $message = "Tutti i campi obbligatori devono essere compilati.";
    } elseif (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $message = "È obbligatorio caricare una foto valida.";
    }

    if (empty($message)) {
        $indirizzo = $conn->real_escape_string($indirizzo);
        $cap = $conn->real_escape_string($cap);
        $citta = $conn->real_escape_string($citta);
        $descrizione = $conn->real_escape_string($descrizione);

        $data = date('Y-m-d');
        $ora = date('H:i:s');
        $stato = 'in attesa';
        $tipoSegnalazione = 0; // smarrimento

        $uploadDir = 'foto/foto_smarrimenti/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $originalName = basename($_FILES['foto']['name']);
        $fotoName = uniqid('img_', true) . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $originalName);
        $fotoTmp = $_FILES['foto']['tmp_name'];
        $fotoDest = $uploadDir . $fotoName;

        if (move_uploaded_file($fotoTmp, $fotoDest)) {
            try {
                // Inserimento LUOGO se non esiste
                $stmtLuogo = $conn->prepare("SELECT indirizzo,cap FROM LUOGHI WHERE indirizzo = ? AND cap = ?");
                $stmtLuogo->bind_param("ss", $indirizzo, $cap);
                $stmtLuogo->execute();
                $resLuogo = $stmtLuogo->get_result();

                if ($resLuogo->num_rows === 0) {
                    $stmtInsLuogo = $conn->prepare("INSERT INTO LUOGHI (indirizzo, cap, citta) VALUES (?, ?, ?)");
                    $stmtInsLuogo->bind_param("sss", $indirizzo, $cap, $citta);
                    if (!$stmtInsLuogo->execute()) {
                        throw new Exception("Errore inserimento luogo: " . $stmtInsLuogo->error);
                    }
                    $stmtInsLuogo->close();
                }
                $stmtLuogo->close();

                // Inserimento RICOMPENSA
                $idRicompensa = null;
                if ($ricompensa !== null && $ricompensa > 0) {
                    $stmtRic = $conn->prepare("INSERT INTO RICOMPENSE (importo) VALUES (?)");
                    $stmtRic->bind_param("d", $ricompensa);
                    if (!$stmtRic->execute()) {
                        throw new Exception("Errore inserimento ricompensa: " . $stmtRic->error);
                    }
                    $idRicompensa = $stmtRic->insert_id;
                    $stmtRic->close();
                }

                $stmtOggetto = $conn->prepare("INSERT INTO OGGETTI (descrizioneOggetto, idCategoria) VALUES (?, ?)");
                $stmtOggetto->bind_param("si", $descrizione, $categoria);
                if (!$stmtOggetto->execute()) {
                    throw new Exception("Errore inserimento oggetto: " . $stmtOggetto->error);
                }
                $idOggetto = $stmtOggetto->insert_id;
                $stmtOggetto->close();

                // INSERIMENTO SEGNALAZIONE
                $stmtSegn = $conn->prepare("
                    INSERT INTO SEGNALAZIONI (
                        idUtente, indirizzo, cap, data, ora,
                        descrizioneSegnalazione, stato, tipoSegnalazione, idRicompensa, idOggetto
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtSegn->bind_param(
                    "issssssssi",
                    $idUtente, $indirizzo, $cap, $data, $ora,
                    $descrizione, $stato, $tipoSegnalazione, $idRicompensa, $idOggetto
                );

                if (!$stmtSegn->execute()) {
                    throw new Exception("Errore inserimento segnalazione: " . $stmtSegn->error);
                }
                $idSegnalazione = $stmtSegn->insert_id;
                $stmtSegn->close();

                // Inserimento FOTO
                $stmtFoto = $conn->prepare("INSERT INTO FOTO (idSegnalazione, nomeFoto) VALUES (?, ?)");
                $stmtFoto->bind_param("is", $idSegnalazione, $fotoName);
                if (!$stmtFoto->execute()) {
                    throw new Exception("Errore inserimento foto: " . $stmtFoto->error);
                }
                $stmtFoto->close();

                $conn->commit();
                $success = "Segnalazione inviata con successo!";
                $_POST = []; // pulizia del form
            } catch (Exception $e) {
                $conn->rollback();
                $message = $e->getMessage();
                if (file_exists($fotoDest)) {
                    unlink($fotoDest);
                }
            }
        } else {
            $message = "Errore nel salvataggio della foto sul server.";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Invia Segnalazione di Smarrimento</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container login-container">
    <h2>Invia Segnalazione di Smarrimento</h2>

    <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="">
        <label>Indirizzo:<br>
            <input type="text" name="indirizzo" required value="<?= htmlspecialchars($_POST['indirizzo'] ?? '') ?>" />
        </label><br><br>

        <label>Città:<br>
            <input type="text" name="citta" required value="<?= htmlspecialchars($_POST['citta'] ?? '') ?>" />
        </label><br><br>

        <label>CAP:<br>
            <input type="text" name="cap" required value="<?= htmlspecialchars($_POST['cap'] ?? '') ?>" />
        </label><br><br>

        <label>Descrizione:<br>
            <textarea name="descrizione" rows="4" required><?= htmlspecialchars($_POST['descrizione'] ?? '') ?></textarea>
        </label><br><br>

        <label>Categoria:<br>
            <select name="categoria" required>
                <option value="">-- Seleziona categoria --</option>
                <option value="1" <?= ($success ? '' : (($_POST['categoria']??'')==1 ? 'selected' : '')) ?>>Abbigliamento</option>
                <option value="2" <?= ($success ? '' : (($_POST['categoria']??'')==2 ? 'selected' : '')) ?>>Elettronica</option>
                <option value="3" <?= ($success ? '' : (($_POST['categoria']??'')==3 ? 'selected' : '')) ?>>Accessori</option>
                <option value="4" <?= ($success ? '' : (($_POST['categoria']??'')==4 ? 'selected' : '')) ?>>Altro</option>
            </select>
        </label><br><br>

        <label>Foto (obbligatoria):<br>
            <input type="file" name="foto" accept="image/*" required />
        </label><br><br>

        <button type="submit" class="btn">Invia Segnalazione</button>
        <button type="button" class="btn" onclick="window.location.href='dashboard_utente.php'">Torna alla Dashboard</button>
    </form>
</div>
</body>
</html>
