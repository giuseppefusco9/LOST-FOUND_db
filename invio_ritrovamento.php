<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_utente.php");
    exit;
}

$message = '';
$success = '';

if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "lostfound_db");
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }
    $conn->autocommit(false);

    $idUtente           = $_SESSION['user_id'];
    $indirizzo          = trim($_POST['indirizzo'] ?? '');
    $cap                = trim($_POST['cap'] ?? '');
    $citta              = trim($_POST['citta'] ?? '');
    $descrizione        = trim($_POST['descrizione'] ?? '');
    $categoria          = (int)($_POST['categoria'] ?? 0);
    $descrizioneOggetto = trim($_POST['descrizioneOggetto'] ?? '');

    if ($indirizzo === '' || $cap === '' || $citta === '' 
        || $descrizione === '' || $categoria <= 0 || $descrizioneOggetto === '') {
        $message = "Tutti i campi obbligatori devono essere compilati.";
    } elseif (!preg_match('/^[0-9]{5}$/', $cap)) {
        $message = "Il CAP deve essere di 5 cifre.";
    } elseif (!isset($_FILES['foto']) || count($_FILES['foto']['name']) === 0) {
        $message = "È obbligatorio caricare almeno una foto valida.";
    }

    $uploadDir = __DIR__ . '/foto/foto_ritrovamenti/';
    if (empty($message)) {
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            $message = "Impossibile creare la cartella per l'upload.";
        } elseif (!is_writable($uploadDir)) {
            $message = "La cartella di destinazione non è scrivibile.";
        }
    }

    $uploadedFotoNames = [];
    if (empty($message)) {
        $fotoFiles = $_FILES['foto'];
        $numFiles = count($fotoFiles['name']);

        for ($i = 0; $i < $numFiles; $i++) {
            if ($fotoFiles['error'][$i] !== UPLOAD_ERR_OK) {
                $message = "Errore nel caricamento di una delle foto.";
                break;
            }

            $originalName = basename($fotoFiles['name'][$i]);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowedExt = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowedExt, true)) {
                $message = "Estensione non consentita per una delle foto.";
                break;
            }

            $fotoTmp = $fotoFiles['tmp_name'][$i];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($fotoTmp);
            $allowedMime = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array($mime, $allowedMime, true)) {
                $message = "Formato immagine non valido per una delle foto.";
                break;
            }

            $fotoName = uniqid('img_', true) . '.' . $ext;
            $fotoDest = $uploadDir . $fotoName;

            if (!move_uploaded_file($fotoTmp, $fotoDest)) {
                $message = "Errore nel salvataggio di una delle foto.";
                break;
            }

            $uploadedFotoNames[] = $fotoName;
        }
    }

    if (empty($message)) {
        try {
            $stmtLuogo = $conn->prepare("SELECT COUNT(*) FROM LUOGHI WHERE indirizzo = ? AND cap = ?");
            $stmtLuogo->bind_param("ss", $indirizzo, $cap);
            $stmtLuogo->execute();
            $stmtLuogo->bind_result($countLuogo);
            $stmtLuogo->fetch();
            $stmtLuogo->close();

            if ($countLuogo == 0) {
                $stmtInsertLuogo = $conn->prepare("INSERT INTO LUOGHI (indirizzo, cap, citta) VALUES (?, ?, ?)");
                $stmtInsertLuogo->bind_param("sss", $indirizzo, $cap, $citta);
                if (!$stmtInsertLuogo->execute()) throw new Exception($stmtInsertLuogo->error);
                $stmtInsertLuogo->close();
            }

            $stmtObj = $conn->prepare("INSERT INTO OGGETTI (descrizioneOggetto, idCategoria) VALUES (?, ?)");
            $stmtObj->bind_param("si", $descrizioneOggetto, $categoria);
            if (!$stmtObj->execute()) throw new Exception($stmtObj->error);
            $idOggetto = $stmtObj->insert_id;
            $stmtObj->close();

            $stato = 'In attesa';
            $tipoSegnalazione = 1; // Ritrovamento

            $stmtSegn = $conn->prepare("
                INSERT INTO SEGNALAZIONI
                (idUtente, indirizzo, cap, data, ora,
                 descrizioneSegnalazione, stato, tipoSegnalazione,
                 idRicompensa, idOggetto)
                VALUES (?, ?, ?, CURDATE(), CURTIME(),
                        ?, ?, ?, NULL, ?)
            ");
            $stmtSegn->bind_param(
                "isssssi",
                $idUtente,
                $indirizzo,
                $cap,
                $descrizione,
                $stato,
                $tipoSegnalazione,
                $idOggetto
            );
            if (!$stmtSegn->execute()) throw new Exception($stmtSegn->error);
            $idSegnalazione = $stmtSegn->insert_id;
            $stmtSegn->close();

            $stmtFoto = $conn->prepare("INSERT INTO FOTO (idSegnalazione, nomeFoto) VALUES (?, ?)");
            foreach ($uploadedFotoNames as $nomeFoto) {
                $stmtFoto->bind_param("is", $idSegnalazione, $nomeFoto);
                if (!$stmtFoto->execute()) throw new Exception($stmtFoto->error);
            }
            $stmtFoto->close();

            $conn->commit();
            $_SESSION['success_message'] = "Segnalazione di ritrovamento inviata con successo!";
            header("Location: invio_ritrovamento.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            foreach ($uploadedFotoNames as $fn) {
                $path = $uploadDir . $fn;
                if (file_exists($path)) unlink($path);
            }
            $message = "Errore durante l'inserimento: " . htmlspecialchars($e->getMessage());
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Invia Segnalazione di Ritrovamento</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="container login-container">
    <h2>Invia Segnalazione di Ritrovamento</h2>

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

        <label>CAP:<br>
            <input type="text" name="cap" required value="<?= htmlspecialchars($_POST['cap'] ?? '') ?>" />
        </label><br><br>

        <label>Descrizione:<br>
            <textarea name="descrizione" rows="4" required><?= htmlspecialchars($_POST['descrizione'] ?? '') ?></textarea>
        </label><br><br>

        <label>Categoria:<br>
            <select name="categoria" required>
                <option value="">-- Seleziona categoria --</option>
                <option value="1" <?= ($success ? '' : (($_POST['categoria']??'')==1 ? 'selected' : '')) ?>>Elettronica</option>
                <option value="2" <?= ($success ? '' : (($_POST['categoria']??'')==2 ? 'selected' : '')) ?>>Abbigliamento</option>
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