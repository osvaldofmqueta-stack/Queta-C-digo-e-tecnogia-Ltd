<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();

// KPI stats
$stats = [
    'aplicacoes'  => (int)$db->query("SELECT COUNT(*) FROM aplicacoes WHERE ativo=1")->fetchColumn(),
    'planos'      => (int)$db->query("SELECT COUNT(*) FROM planos WHERE ativo=1")->fetchColumn(),
    'utilizadores'=> (int)$db->query("SELECT COUNT(*) FROM clientes")->fetchColumn(),
    'carrinho'    => (int)$db->query("SELECT COUNT(DISTINCT cliente_id) FROM carrinho")->fetchColumn(),
    'topicos'     => (int)$db->query("SELECT COUNT(*) FROM topicos_manual WHERE ativo=1")->fetchColumn(),
    'perguntas'   => (int)$db->query("SELECT COUNT(*) FROM perguntas WHERE respondido=0")->fetchColumn(),
    'visitasHoje' => (int)$db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas WHERE DATE(criado_em)=DATE('now')")->fetchColumn(),
    'visitasMes'  => (int)$db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas WHERE criado_em >= DATE('now','-30 days')")->fetchColumn(),
    'novosHoje'   => (int)$db->query("SELECT COUNT(*) FROM clientes WHERE DATE(criado_em)=DATE('now')")->fetchColumn(),
];

$recentPerguntas = $db->query("SELECT * FROM perguntas WHERE respondido=0 ORDER BY criado_em DESC LIMIT 6")->fetchAll();
$recentUtilizadores = $db->query("SELECT * FROM clientes ORDER BY criado_em DESC LIMIT 5")->fetchAll();
$recentTopicos = getTopicosRecentes(5);

$pageTitle = 'Dashboard';
?>
<?php include 'partials/head.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">

    <!-- Header -->
    <div class="admin-header">
        <div class="admin-header-left">
            <button onclick="toggleSidebar()" style="display:none;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;" id="sidebarToggle">
                <i class="fas fa-bars" style="font-size:18px;color:#6B7280;"></i>
            </button>
            <div>
                <div class="admin-header-title"><i class="fas fa-tachometer-alt"></i> Dashboard</div>
                <div class="admin-breadcrumb"><span>Painel</span></div>
            </div>
        </div>
        <div class="admin-header-right">
            <div class="admin-header-date"><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y') ?> · <?= date('H:i') ?></div>
            <a href="../" target="_blank" class="btn-secondary" style="font-size:12.5px;padding:7px 14px;"><i class="fas fa-external-link-alt"></i> Ver Site</a>
            <a href="logout.php" class="btn-sm delete"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>

    <div class="admin-content">

        <!-- Welcome Banner -->
        <div class="dash-welcome">
            <div class="dash-welcome-text">
                <h2>Olá, <?= h($_SESSION['admin_nome'] ?? 'Admin') ?>! 👋</h2>
                <?php
                $dias = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
                $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
                $dataFormatada = $dias[date('w')] . ', ' . date('j') . ' de ' . $meses[(int)date('n')-1] . ' de ' . date('Y');
                ?>
                <p>Hoje é <strong style="color:rgba(255,255,255,0.85);"><?= $dataFormatada ?></strong>.
                <?php if ($stats['visitasHoje'] > 0): ?>
                    <strong style="color:#79C0FF;"><?= $stats['visitasHoje'] ?> visita<?= $stats['visitasHoje']!=1?'s':'' ?></strong> no site hoje.
                <?php else: ?>
                    Bem-vindo ao seu painel de controlo.
                <?php endif; ?>
                </p>
            </div>
            <div class="dash-welcome-actions">
                <?php if ($stats['perguntas'] > 0): ?>
                <a href="perguntas.php?filtro=pendentes" class="btn-sm delete" style="font-size:13px;padding:8px 16px;">
                    <i class="fas fa-comment-dots"></i> <?= $stats['perguntas'] ?> pergunta<?= $stats['perguntas']!=1?'s':'' ?> pendente<?= $stats['perguntas']!=1?'s':'' ?>
                </a>
                <?php endif; ?>
                <a href="configuracoes.php" class="btn-sm secondary" style="font-size:13px;padding:8px 14px;"><i class="fas fa-cog"></i> Configurações</a>
            </div>
        </div>

        <!-- KPI Row 1 -->
        <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-eye"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= number_format($stats['visitasMes']) ?></div>
                    <div class="stat-label">Visitas este mês</div>
                    <?php if ($stats['visitasHoje']>0): ?>
                    <div class="stat-trend up"><i class="fas fa-arrow-up"></i> <?= $stats['visitasHoje'] ?> hoje</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-user-plus"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $stats['utilizadores'] ?></div>
                    <div class="stat-label">Utilizadores registados</div>
                    <?php if ($stats['novosHoje']>0): ?>
                    <div class="stat-trend up"><i class="fas fa-arrow-up"></i> +<?= $stats['novosHoje'] ?> hoje</div>
                    <?php else: ?>
                    <div class="stat-trend same"><i class="fas fa-shopping-cart"></i> <?= $stats['carrinho'] ?> com carrinho</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-tags"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $stats['planos'] ?></div>
                    <div class="stat-label">Planos activos</div>
                    <div class="stat-trend same"><i class="fas fa-graduation-cap"></i> <?= $stats['aplicacoes'] ?> aplicações</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon <?= $stats['perguntas']>0?'red':'purple' ?>">
                    <i class="fas fa-<?= $stats['perguntas']>0?'exclamation-circle':'comments' ?>"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-num"><?= $stats['perguntas'] ?></div>
                    <div class="stat-label">Perguntas por responder</div>
                    <div class="stat-trend same"><i class="fas fa-file-alt"></i> <?= $stats['topicos'] ?> tópicos no manual</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-card" style="margin-bottom:24px;">
            <div class="admin-card-header">
                <h2 class="admin-card-title"><i class="fas fa-bolt"></i> Acesso Rápido</h2>
            </div>
            <div class="dash-quick-grid">
                <?php
                $quickLinks = [
                    ['carousel.php',      'fa-images',        'blue',   'Carousel',       'Gerir banners do hero'],
                    ['planos.php',        'fa-tags',          'green',  'Planos',          'Preços e funcionalidades'],
                    ['funcionalidades.php','fa-star',         'orange', 'Funcionalidades', 'Destaques do produto'],
                    ['utilizadores.php',  'fa-users',         'purple', 'Utilizadores',    'Contas registadas'],
                    ['perguntas.php',     'fa-comments',      'red',    'Perguntas',       'FAQ e suporte'],
                    ['manual.php',        'fa-book',          'cyan',   'Manual',          'Artigos e guias'],
                    ['analytics.php',     'fa-chart-line',    'blue',   'Analytics',       'Estatísticas do site'],
                    ['configuracoes.php', 'fa-cog',           'orange', 'Configurações',   'Definições gerais'],
                ];
                $iconColors = ['blue'=>'#0066FF','green'=>'#10B981','orange'=>'#F59E0B','purple'=>'#8B5CF6','red'=>'#EF4444','cyan'=>'#06B6D4'];
                $bgColors   = ['blue'=>'#EFF6FF','green'=>'#ECFDF5','orange'=>'#FFFBEB','purple'=>'#F5F3FF','red'=>'#FEF2F2','cyan'=>'#ECFEFF'];
                foreach ($quickLinks as [$href,$icon,$color,$label,$sub]):
                ?>
                <a href="<?= $href ?>" class="dash-quick-card">
                    <div class="dqc-icon" style="background:<?= $bgColors[$color] ?>;color:<?= $iconColors[$color] ?>;">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="dqc-label"><?= $label ?></div>
                    <div class="text-muted text-small" style="margin-top:3px;"><?= $sub ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Two columns: Pending questions + Recent users -->
        <div class="dash-two-col">
            <!-- Pending Q&A -->
            <div class="admin-card" style="margin-bottom:0;">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <i class="fas fa-comment-dots"></i> Perguntas Pendentes
                        <?php if ($stats['perguntas']>0): ?>
                        <span class="badge badge-danger" style="margin-left:4px;"><?= $stats['perguntas'] ?></span>
                        <?php endif; ?>
                    </h2>
                    <a href="perguntas.php" class="btn-sm secondary">Ver todas</a>
                </div>
                <?php if (empty($recentPerguntas)): ?>
                <div class="empty-state" style="padding:28px 0;">
                    <i class="fas fa-check-circle" style="color:#10B981;"></i>
                    <p>Sem perguntas pendentes. Excelente!</p>
                </div>
                <?php else: ?>
                <?php foreach ($recentPerguntas as $p): ?>
                <div class="activity-item">
                    <div class="activity-dot orange"></div>
                    <div class="activity-body">
                        <strong><?= h(mb_substr($p['pergunta'], 0, 65)) ?><?= mb_strlen($p['pergunta'])>65?'…':'' ?></strong>
                        <span><i class="fas fa-user"></i> <?= h($p['nome']) ?></span>
                    </div>
                    <a href="perguntas.php?responder=<?= $p['id'] ?>" class="btn-sm success">
                        <i class="fas fa-reply"></i>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Recent users -->
            <div class="admin-card" style="margin-bottom:0;">
                <div class="admin-card-header">
                    <h2 class="admin-card-title"><i class="fas fa-user-plus"></i> Últimos Registos</h2>
                    <a href="utilizadores.php" class="btn-sm secondary">Ver todos</a>
                </div>
                <?php if (empty($recentUtilizadores)): ?>
                <div class="empty-state" style="padding:28px 0;">
                    <i class="fas fa-users"></i>
                    <p>Ainda não há utilizadores registados.</p>
                </div>
                <?php else: ?>
                <?php foreach ($recentUtilizadores as $u): ?>
                <div class="activity-item">
                    <div class="avatar-sm" style="width:30px;height:30px;font-size:12px;flex-shrink:0;">
                        <?= mb_strtoupper(mb_substr($u['nome'],0,1)) ?>
                    </div>
                    <div class="activity-body">
                        <strong><?= h($u['nome']) ?></strong>
                        <span><?= h($u['email']) ?><?= $u['escola'] ? ' · ' . h($u['escola']) : '' ?></span>
                    </div>
                    <div class="activity-time"><?= timeAgo($u['criado_em']) ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent manual topics -->
        <?php if (!empty($recentTopicos)): ?>
        <div class="admin-card" style="margin-top:24px;margin-bottom:0;">
            <div class="admin-card-header">
                <h2 class="admin-card-title"><i class="fas fa-clock"></i> Tópicos Recentes do Manual</h2>
                <a href="manual.php" class="btn-sm secondary">Gerir Manual</a>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Título</th><th>Categoria</th><th>Adicionado</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recentTopicos as $t): ?>
                    <tr>
                        <td><strong><?= h($t['titulo']) ?></strong></td>
                        <td><span class="badge badge-info"><?= h($t['categoria_nome']) ?></span></td>
                        <td class="text-muted text-small"><?= timeAgo($t['criado_em']) ?></td>
                        <td><a href="manual.php?editar=<?= $t['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /admin-content -->
</div><!-- /admin-main -->

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    const ov = document.getElementById('sidebarOverlay');
    ov.style.display = ov.style.display === 'block' ? 'none' : 'block';
}
function closeSidebar() {
    document.getElementById('adminSidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').style.display = 'none';
}
// Show mobile toggle
if (window.innerWidth < 900) document.getElementById('sidebarToggle').style.display = 'inline-flex';
</script>
<?php include 'partials/foot.php'; ?>
