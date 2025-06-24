<?php
$message = '';

// Connessione
$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) die("Connessione fallita");

// Gestione POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idUtente'])) {
    $idUtente = $_POST['idUtente'];

    $stmt = $conn->prepare("UPDATE UTENTI SET ban = TRUE WHERE idUtente = ?");
    $stmt->bind_param("i", $idUtente);

    if ($stmt->execute()) {
        $message = "Utente ID $idUtente bloccato con successo.";
    } else {
        $message = "Errore nel blocco utente.";
    }

    $stmt->close();
}

// Recupero utenti
$utenti = [];
$result = $conn->query("SELECT idUtente, nome, cognome, email, ban FROM UTENTI");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $utenti[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <title>Blocca Utente</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        tr.selected {
            background-color: #d1ecf1;
        }
        tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }
    </style>
    <script>
        function selectRow(row, idUtente) {
            document.querySelectorAll("tbody tr").forEach(tr => tr.classList.remove("selected"));
            row.classList.add("selected");
            document.getElementById("idUtente").value = idUtente;
        }
    </script>
</head>
<body>
<div class="container login-container">
    <h2>Gestione Utenti</h2>

    <?php if ($message): ?>
        <p class="<?= str_contains($message, 'Errore') ? 'error' : 'success' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="idUtente" id="idUtente" />
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Email</th>
                    <th>ban</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utenti as $utente): ?>
                    <tr onclick="selectRow(this, <?= $utente['idUtente'] ?>)">
                        <td><?= $utente['idUtente'] ?></td>
                        <td><?= htmlspecialchars($utente['nome']) ?></td>
                        <td><?= htmlspecialchars($utente['cognome']) ?></td>
                        <td><?= htmlspecialchars($utente['email']) ?></td>
                        <td><?= $utente['ban'] ? 'Sì' : 'No' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="button-group">
            <button type="submit" class="btn">Blocca Utente</button>
        </div>
    </form>

    <button class="btn" onclick="location.href='dashboard_admin.php'">Torna alla Dashboard</button>
</div>
</body>
</html>
