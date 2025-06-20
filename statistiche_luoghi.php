<?php
// Connessione e query statistiche per città
$conn = new mysqli("localhost", "root", "", "lostfound_db");
if ($conn->connect_error) die("Connessione fallita");

$sql = "
    SELECT L.citta, COUNT(*) as numSegnalazioni
    FROM SEGNALAZIONI S 
    JOIN LUOGHI L ON S.cap = L.cap AND S.indirizzo = L.indirizzo
    WHERE S.tipoSegnalazione = 0
    GROUP BY L.citta
    ORDER BY numSegnalazioni DESC;
";

$result = $conn->query($sql);
$luoghi = [];
$total = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $luoghi[] = $row;
        $total += $row['numSegnalazioni'];
    }
}
$conn->close();
?>

<section class="statistics-section">
    <h2>📍 Statistiche per Luoghi</h2>

    <?php if (count($luoghi) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Città</th>
                    <th>Numero Segnalazioni</th>
                    <th>Percentuale (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($luoghi as $riga): ?>
                    <tr>
                        <td><?= htmlspecialchars($riga['citta']) ?></td>
                        <td><?= $riga['numSegnalazioni'] ?></td>
                        <td><?= round(($riga['numSegnalazioni'] / $total) * 100, 2) ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <canvas id="graficoLuoghi" width="400" height="400" style="margin-top: 40px;"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctxLuoghi = document.getElementById('graficoLuoghi').getContext('2d');
            new Chart(ctxLuoghi, {
                type: 'pie',
                data: {
                    labels: <?= json_encode(array_column($luoghi, 'citta')) ?>,
                    datasets: [{
                        label: 'Segnalazioni per città (%)',
                        data: <?= json_encode(array_map(function($r) use ($total) {
                            return round(($r['numSegnalazioni'] / $total) * 100, 2);
                        }, $luoghi)) ?>,
                        backgroundColor: [
                            '#ff6384', '#36a2eb', '#ffce56', '#4bc0c0',
                            '#9966ff', '#ff9f40', '#6f42c1', '#20c997'
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
