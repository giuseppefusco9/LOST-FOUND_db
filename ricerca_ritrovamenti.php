<?php
session_start();

$isUser = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['admin_id']);

if (!$isUser && !$isAdmin) {
    header("Location: login_utente.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) die("Connessione fallita");

$tipo_luogo = "%";
$tipo_categoria = "%";
$soloMieSegnalazioni = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['luogo'])) {
        $tipo_luogo = "%" . $_POST['luogo'] . "%";
    }
    if (isset($_POST['categoria']) && $_POST['categoria'] !== "") {
        $tipo_categoria = $_POST['categoria'];
    }
    if (isset($_POST['mieSegnalazioni']) && $_POST['mieSegnalazioni'] == "1" && $isUser && !$isAdmin) {
        $soloMieSegnalazioni = true;
    }
}

$query = "
    SELECT s.*, l.citta AS tipoLuogo, l.indirizzo, c.tipoCategoria, f.nomeFoto, r.importo
    FROM segnalazioni s
    JOIN luoghi l ON s.cap = l.cap AND s.indirizzo = l.indirizzo
    JOIN oggetti o ON s.idOggetto = o.idOggetto
    JOIN categorie c ON o.idCategoria = c.idCategoria
    JOIN foto f ON s.idSegnalazione = f.idSegnalazione
    LEFT JOIN ricompense r ON s.idRicompensa = r.idRicompensa
    WHERE s.tipoSegnalazione = 1
    AND l.citta LIKE ?
    AND c.tipoCategoria LIKE ?
";

if ($soloMieSegnalazioni) {
    $query .= " AND s.idUtente = ? ";
}

$stmt = $conn->prepare($query);

if ($soloMieSegnalazioni) {
    $stmt->bind_param("ssi", $tipo_luogo, $tipo_categoria, $_SESSION['user_id']);
} else {
    $stmt->bind_param("ss", $tipo_luogo, $tipo_categoria);
}

$stmt->execute();
$result = $stmt->get_result();
$userType = $isAdmin ? 'admin' : 'utente';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Ricerca Oggetti Ritrovati</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .segnalazione-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .segnalazione-foto {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 1px solid #ccc;
        }
        .segnalazioni-list {
            margin-top: 20px;
        }
        .btn-mie-segnalazioni {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="container login-container">
    <h1>🔎 Visualizza Oggetti Ritrovati</h1>

    <?php if ($isUser && !$isAdmin): ?>
        <form method="post" style="margin-bottom:10px;">
            <input type="hidden" name="mieSegnalazioni" value="<?= $soloMieSegnalazioni ? "0" : "1" ?>" />
            <button type="submit" class="btn btn-mie-segnalazioni">
                <?= $soloMieSegnalazioni ? "Tutte le segnalazioni" : "Le mie segnalazioni" ?>
            </button>
        </form>
    <?php endif; ?>

    <form method="post">
        <label>Luogo:</label>
        <input type="text" name="luogo" placeholder="Es. Milano, Napoli">
        <label>Categoria:</label>
        <select name="categoria">
            <option value="">-- Qualsiasi --</option>
            <option value="abbigliamento">Abbigliamento</option>
            <option value="elettronica">Elettronica</option>
            <option value="accessori">Accessori</option>
            <option value="altro">Altro</option>
        </select>
        <button class="btn" type="submit">Cerca</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <h3>Risultati trovati:</h3>
        <div class="segnalazioni-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="segnalazione-item">
                    <?php if ($isAdmin): ?>
                        <strong>ID Segnalazione:</strong> <?= htmlspecialchars($row['idSegnalazione']) ?> |
                    <?php endif; ?>
                    <strong>Luogo:</strong> <?= htmlspecialchars($row['tipoLuogo']) ?> |
                    <strong>Indirizzo:</strong> <?= htmlspecialchars($row['indirizzo']) ?> |
                    <strong>Categoria:</strong> <?= htmlspecialchars($row['tipoCategoria']) ?> |
                    <strong>Data:</strong> <?= htmlspecialchars($row['data']) ?> |
                    <strong>Descrizione:</strong> <?= htmlspecialchars($row['descrizioneSegnalazione']) ?> |
                    <strong>Stato:</strong> <?= htmlspecialchars($row['stato']) ?>
                    <?php if (!empty($row['nomeFoto'])): ?>
                        <div>
                            <img src="foto/foto_ritrovamenti/<?= htmlspecialchars($row['nomeFoto']) ?>" alt=" " class="segnalazione-foto">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Nessuna segnalazione trovata.</p>
    <?php endif; ?>

    <a href="dashboard_<?= $userType ?>.php" class="btn" type="submit">Torna alla Dashboard</a>
<?php
$stmt->close();
$conn->close();
?>
</div>
</body>
</html>
