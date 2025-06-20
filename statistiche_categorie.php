<?php
// Connessione e query statistiche
$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) die("Connessione fallita");

$sql = "
    SELECT tipoCategoria, COUNT(*) as numSegnalazioni,
    ROUND(COUNT(*) * 100.0 / (
        SELECT COUNT(*) 
        FROM SEGNALAZIONI 
        WHERE idOggetto IS NOT NULL
        AND tipoSegnalazione = 0
    ), 2) AS percentuale
    FROM SEGNALAZIONI S 
    JOIN OGGETTI O ON S.idOggetto = O.idOggetto
    JOIN CATEGORIE C ON O.idCategoria = C.idCategoria
    WHERE S.tipoSegnalazione = 0
    GROUP BY C.tipoCategoria
    ORDER BY numSegnalazioni DESC;
";

$result = $conn->query($sql);
$dati = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dati[] = $row;
    }
}
$conn->close();
?>

<section class="statistics-section">
    <h2>📊 Statistiche per Categorie</h2>
    
    <?php if (count($dati) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Numero Segnalazioni</th>
                    <th>Percentuale (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dati as $riga): ?>
                    <tr>
                        <td><?= htmlspecialchars($riga['tipoCategoria']) ?></td>
                        <td><?= $riga['numSegnalazioni'] ?></td>
                        <td><?= $riga['percentuale'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <canvas id="graficoCategorie" width="400" height="400" style="margin-top: 40px;"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('graficoCategorie').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_column($dati, 'tipoCategoria')) ?>,
                    datasets: [{
                        label: 'Percentuale segnalazioni',
                        data: <?= json_encode(array_column($dati, 'percentuale')) ?>,
                        backgroundColor: [
                            '#007bff', '#28a745', '#ffc107', '#dc3545',
                            '#17a2b8', '#6610f2', '#e83e8c', '#20c997'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.label}: ${ctx.parsed}%`
                            }
                        }
                    }
                }
            });
        </script>
    <?php else: ?>
        <p class="error">Nessuna statistica disponibile.</p>
    <?php endif; ?>
</section>
