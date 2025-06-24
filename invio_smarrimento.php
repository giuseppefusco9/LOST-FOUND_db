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
    $indirizzo   = $conn->real_escape_string($_POST['indirizzo'] ?? '');
    $cap         = $conn->real_escape_string($_POST['cap'] ?? '');
    $descrizione = $conn->real_escape_string($_POST['descrizione'] ?? '');
    $categoria   = (int)($_POST['categoria'] ?? 0);

    if ($indirizzo === '' || $cap === '' || $descrizione === '' || $categoria <= 0) {
        $message = "Tutti i campi sono obbligatori.";
    } elseif (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $message = "È obbligatorio caricare una foto.";
    }

    if (empty($message)) {
        $data = date('Y-m-d');
        $ora  = date('H:i:s');
        $stato = 'In attesa';
        $tipo  = 'Smarrimento';

        $uploadDir = 'foto/foto_smarrimenti/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $originalName = basename($_FILES['foto']['name']);
        $fotoName = uniqid('img_', true) . '_' . $originalName;
        $fotoTmp = $_FILES['foto']['tmp_name'];
        $fotoDest = $uploadDir . $fotoName;

        if (move_uploaded_file($fotoTmp, $fotoDest)) {
            try {
                $stmtFoto = $conn->prepare("INSERT INTO FOTO (nomeFoto) VALUES (?)");
                $stmtFoto->bind_param("s", $fotoName);
                if (!$stmtFoto->execute()) {
                    throw new Exception("Errore inserimento foto: " . $stmtFoto->error);
                }
                $idFoto = $stmtFoto->insert_id;
                $stmtFoto->close();

                $stmtSegn = $conn->prepare("
                    INSERT INTO SEGNALAZIONI
                      (idUtente, indirizzo, cap, data, ora, descrizioneSegnalazione, stato, tipoSegnalazione, idFoto, idRicompensa, idOggetto)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)
                ");
                $stmtSegn->bind_param(
                    "isssssssi",
                    $idUtente, $indirizzo, $cap, $data, $ora,
                    $descrizione, $stato, $tipo, $idFoto
                );
                if (!$stmtSegn->execute()) {
                    throw new Exception("Errore inserimento segnalazione: " . $stmtSegn->error);
                }
                $stmtSegn->close();

                $conn->commit();
                $success = "Segnalazione inviata con successo!";
            } catch (Exception $e) {
                $conn->rollback();
                $message = $e->getMessage();
                // opzionale: elimina il file salvato in caso di errore
                if (file_exists($fotoDest)) {
                    unlink($fotoDest);
                }
            }
        } else {
            $message = "Errore nel salvataggio della foto.";
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

        <label>CAP:<br>
            <input type="text" name="cap" required value="<?= htmlspecialchars($_POST['cap'] ?? '') ?>" />
        </label><br><br>

        <label>Descrizione:<br>
            <textarea name="descrizione" rows="4" required><?= htmlspecialchars($_POST['descrizione'] ?? '') ?></textarea>
        </label><br><br>

        <label>Categoria:<br>
            <select name="categoria" required>
                <option value="">-- Seleziona --</option>
                <option value="1" <?= (($_POST['categoria'] ?? '') == 1 ? 'selected' : '') ?>>Elettronica</option>
                <option value="2" <?= (($_POST['categoria'] ?? '') == 2 ? 'selected' : '') ?>>Abbigliamento</option>
                <!-- Aggiungi altre categorie se serve -->
            </select>
        </label><br><br>

        <label>Foto (obbligatoria):<br>
            <input type="file" name="foto" accept="image/*" required />
        </label><br><br>

        <button type="submit" class="btn">Invia Segnalazione</button>
    </form>

    <p><a href="dashboard.php">Torna alla Dashboard</a></p>
</div>
</body>
</html>
