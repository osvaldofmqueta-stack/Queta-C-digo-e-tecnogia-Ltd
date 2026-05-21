<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();

// ---- Stats ----
$totalVisitas       = (int)$db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas")->fetchColumn();
$visitasHoje        = (int)$db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas WHERE DATE(criado_em)=DATE('now')")->fetchColumn();
$visitasOntem       = (int)$db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas WHERE DATE(criado_em)=DATE('now','-1 day')")->fetchColumn();
$visitasSemana      = (int)$db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas WHERE criado_em >= DATE('now','-7 days')")->fetchColumn();
$visitasMes         = (int)$db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas WHERE criado_em >= DATE('now','-30 days')")->fetchColumn();
$totalClientes      = (int)$db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$clientesHoje       = (int)$db->query("SELECT COUNT(*) FROM clientes WHERE DATE(criado_em)=DATE('now')")->fetchColumn();
$totalCarrinho      = (int)$db->query("SELECT COUNT(DISTINCT cliente_id) FROM carrinho")->fetchColumn();

// ---- Visits per day (last 30 days) ----
$visitasDia = $db->query("
    SELECT DATE(criado_em) as dia, COUNT(DISTINCT sessao_hash) as total
    FROM visitas
    WHERE criado_em >= DATE('now','-30 days')
    GROUP BY DATE(criado_em)
    ORDER BY dia ASC
")->fetchAll();

// ---- Top pages ----
$topPaginas = $db->query("
    SELECT pagina, COUNT(DISTINCT sessao_hash) as visitas
    FROM visitas
    WHERE pagina IS NOT NULL AND pagina != ''
    GROUP BY pagina
    ORDER BY visitas DESC
    LIMIT 10
")->fetchAll();

// ---- Visits by hour (today) ----
$visitasPorHora = $db->query("
    SELECT strftime('%H', criado_em) as hora, COUNT(DISTINCT sessao_hash) as total
    FROM visitas
    WHERE DATE(criado_em)=DATE('now')
    GROUP BY hora
    ORDER BY hora ASC
")->fetchAll();

// ---- Recent registrations (last 14 days) ----
$registosDia = $db->query("
    SELECT DATE(criado_em) as dia, COUNT(*) as total
    FROM clientes
    WHERE criado_em >= DATE('now','-14 days')
    GROUP BY DATE(criado_em)
    ORDER BY dia ASC
")->fetchAll();

// ---- Chart data ----
// Fill missing days for visits chart
$diasMap = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $diasMap[$d] = 0;
}
foreach ($visitasDia as $v) $diasMap[$v['dia']] = (int)$v['total'];

$chartLabels = array_map(fn($d) => date('d/m', strtotime($d)), array_keys($diasMap));
$chartData   = array_values($diasMap);

// Hours chart
$horasMap = [];
for ($h = 0; $h < 24; $h++) $horasMap[sprintf('%02d', $h)] = 0;
foreach ($visitasPorHora as $v) $horasMap[$v['hora']] = (int)$v['total'];

// Trend
$tendencia = $visitasHoje > $visitasOntem ? 'up' : ($visitasHoje < $visitasOntem ? 'down' : 'same');
?>
<?php $pageTitle = 'Analytics'; $extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>'; include 'partials/head.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <div>
            <h1><i class="fas fa-chart-line"></i> Analytics do Site
                <span class="live-badge" style="font-size:12px;margin-left:10px;vertical-align:middle;">
                    <span class="live-dot-g"></span> Em tempo real
                </span>
            </h1>
            <p style="color:var(--text-light);font-size:14px;margin-top:4px;">Estatísticas de visitas e registos — últimos 30 dias</p>
        </div>
        <div style="font-size:13px;color:var(--text-light);">
            Actualizado: <?= date('d/m/Y H:i') ?>
            <button onclick="location.reload()" style="border:1px solid var(--border);background:white;border-radius:6px;padding:5px 12px;cursor:pointer;margin-left:8px;font-size:12px;">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="admin-content">

        <!-- KPI CARDS -->
        <div class="analytics-grid">
            <div class="analytics-card">
                <div class="ac-label"><i class="fas fa-eye" style="color:var(--primary);"></i> Total de Visitas</div>
                <div class="ac-value" style="color:var(--primary);"><?= number_format($totalVisitas) ?></div>
                <div class="ac-sub"><i class="fas fa-users"></i> sessões únicas registadas</div>
            </div>
            <div class="analytics-card">
                <div class="ac-label"><i class="fas fa-calendar-day" style="color:var(--secondary);"></i> Visitas Hoje</div>
                <div class="ac-value" style="color:var(--secondary);"><?= $visitasHoje ?></div>
                <div class="ac-sub <?= $tendencia==='up'?'trend-up':($tendencia==='down'?'trend-down':'trend-same') ?>">
                    <i class="fas fa-arrow-<?= $tendencia==='up'?'up':($tendencia==='down'?'down':'right') ?>"></i>
                    <?php if ($tendencia === 'up'): ?>
                        +<?= $visitasHoje - $visitasOntem ?> vs ontem (<?= $visitasOntem ?>)
                    <?php elseif ($tendencia === 'down'): ?>
                        <?= $visitasHoje - $visitasOntem ?> vs ontem (<?= $visitasOntem ?>)
                    <?php else: ?>
                        Igual a ontem (<?= $visitasOntem ?>)
                    <?php endif; ?>
                </div>
            </div>
            <div class="analytics-card">
                <div class="ac-label"><i class="fas fa-calendar-week" style="color:#f59e0b;"></i> Esta Semana</div>
                <div class="ac-value" style="color:#f59e0b;"><?= $visitasSemana ?></div>
                <div class="ac-sub"><i class="fas fa-calendar"></i> últimos 7 dias</div>
            </div>
            <div class="analytics-card">
                <div class="ac-label"><i class="fas fa-user-plus" style="color:#8b5cf6;"></i> Registos</div>
                <div class="ac-value" style="color:#8b5cf6;"><?= $totalClientes ?></div>
                <div class="ac-sub">
                    <i class="fas fa-star" style="color:#8b5cf6;"></i>
                    <?= $clientesHoje ?> novo<?= $clientesHoje!=1?'s':'' ?> hoje · <?= $totalCarrinho ?> com itens no carrinho
                </div>
            </div>
        </div>

        <!-- MAIN CHART + PAGES -->
        <div class="charts-row">
            <div class="chart-card">
                <h3><i class="fas fa-chart-area" style="color:var(--primary);"></i> Visitas Únicas — Últimos 30 Dias</h3>
                <canvas id="chartVisitas" height="90"></canvas>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-file-alt" style="color:var(--secondary);"></i> Páginas Mais Visitadas</h3>
                <?php if (empty($topPaginas)): ?>
                <p style="color:var(--text-light);font-size:13px;text-align:center;padding:20px;">Ainda sem dados de páginas.</p>
                <?php else: ?>
                <?php $maxV = max(array_column($topPaginas, 'visitas')); ?>
                <table class="paginas-table">
                    <?php foreach($topPaginas as $pg): ?>
                    <tr>
                        <td style="color:var(--text);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= h($pg['pagina']) ?>">
                            <?= h(strlen($pg['pagina']) > 22 ? substr($pg['pagina'], 0, 22) . '…' : $pg['pagina']) ?>
                        </td>
                        <td>
                            <div class="pagina-bar-wrap"><div class="pagina-bar" style="width:<?= round($pg['visitas']/$maxV*100) ?>%"></div></div>
                        </td>
                        <td style="text-align:right;font-weight:700;font-size:13px;color:var(--primary);min-width:28px;"><?= $pg['visitas'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- HOUR CHART + REGISTRATIONS -->
        <div class="charts-row2">
            <div class="chart-card">
                <h3><i class="fas fa-clock" style="color:#f59e0b;"></i> Visitas por Hora — Hoje</h3>
                <?php if ($visitasHoje === 0): ?>
                <p style="color:var(--text-light);font-size:13px;text-align:center;padding:20px;">Ainda sem visitas hoje.</p>
                <?php else: ?>
                <canvas id="chartHoras" height="100"></canvas>
                <?php endif; ?>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-user-plus" style="color:#8b5cf6;"></i> Novos Registos — Últimos 14 Dias</h3>
                <?php if (empty($registosDia)): ?>
                <p style="color:var(--text-light);font-size:13px;text-align:center;padding:20px;">Ainda não há registos.</p>
                <?php else: ?>
                <canvas id="chartRegistos" height="100"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- SUMMARY ROW -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:4px;">
            <div class="analytics-card" style="text-align:center;">
                <div class="ac-label" style="justify-content:center;"><i class="fas fa-calendar" style="color:var(--primary);"></i> Este Mês</div>
                <div class="ac-value" style="color:var(--primary);"><?= $visitasMes ?></div>
                <div class="ac-sub" style="justify-content:center;">visitas únicas (30 dias)</div>
            </div>
            <div class="analytics-card" style="text-align:center;">
                <div class="ac-label" style="justify-content:center;"><i class="fas fa-shopping-cart" style="color:var(--accent);"></i> Carrinhos Activos</div>
                <div class="ac-value" style="color:var(--accent);"><?= $totalCarrinho ?></div>
                <div class="ac-sub" style="justify-content:center;">utilizadores com planos no carrinho</div>
            </div>
            <div class="analytics-card" style="text-align:center;">
                <div class="ac-label" style="justify-content:center;"><i class="fas fa-percentage" style="color:var(--secondary);"></i> Taxa de Registo</div>
                <?php $taxa = $totalVisitas > 0 ? round($totalClientes / $totalVisitas * 100, 1) : 0; ?>
                <div class="ac-value" style="color:var(--secondary);"><?= $taxa ?>%</div>
                <div class="ac-sub" style="justify-content:center;">visitantes que criaram conta</div>
            </div>
        </div>

    </div>
</div>

<script>
// Chart defaults
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#57606A';

// Visitas chart
new Chart(document.getElementById('chartVisitas'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Visitas únicas',
            data: <?= json_encode($chartData) ?>,
            borderColor: '#0066FF',
            backgroundColor: 'rgba(0,102,255,0.08)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: '#0066FF'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#F0F2F5' } },
            x: { grid: { display: false }, ticks: { maxRotation: 45, font: { size: 11 } } }
        }
    }
});

<?php if ($visitasHoje > 0): ?>
// Horas chart
new Chart(document.getElementById('chartHoras'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($horasMap)) ?>,
        datasets: [{
            label: 'Visitas',
            data: <?= json_encode(array_values($horasMap)) ?>,
            backgroundColor: 'rgba(245,158,11,0.7)',
            borderColor: '#f59e0b',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#F0F2F5' } },
            x: { grid: { display: false } }
        }
    }
});
<?php endif; ?>

<?php if (!empty($registosDia)): ?>
// Registos chart
<?php
$regMap = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $regMap[$d] = 0;
}
foreach ($registosDia as $r) $regMap[$r['dia']] = (int)$r['total'];
?>
new Chart(document.getElementById('chartRegistos'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($d) => date('d/m', strtotime($d)), array_keys($regMap))) ?>,
        datasets: [{
            label: 'Novos utilizadores',
            data: <?= json_encode(array_values($regMap)) ?>,
            backgroundColor: 'rgba(139,92,246,0.7)',
            borderColor: '#8b5cf6',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#F0F2F5' } },
            x: { grid: { display: false } }
        }
    }
});
<?php endif; ?>

// Auto-refresh every 60 seconds
setTimeout(() => location.reload(), 60000);
</script>
<?php include 'partials/foot.php'; ?>