<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
$rootPath = '/';

$id = isset($_GET['app']) ? (int)$_GET['app'] : 0;
$app = $id ? getAplicacao($id) : null;

if (!$app) {
    $apps = getAplicacoes();
    if (count($apps) === 1) {
        header('Location: /aceder.php?app=' . $apps[0]['id']);
        exit;
    }
    $pageTitle = 'Aceder às Aplicações';
} else {
    $pageTitle = 'Aceder — ' . $app['nome'];
}

$whatsapp = getWhatsappLink();

$db = getDB();
$stmtPerfis = $db->prepare("SELECT * FROM perfis_acesso WHERE aplicacao_id=? AND ativo=1 ORDER BY ordem, titulo");
$stmtPerfis->execute([$id]);
$perfisDB = $stmtPerfis->fetchAll();

$perfis = !empty($perfisDB) ? array_map(fn($p) => [
    'icone' => $p['icone'], 'titulo' => $p['titulo'], 'desc' => $p['descricao'], 'cor' => $p['cor']
], $perfisDB) : [
    ['icone' => 'fa-crown',            'titulo' => 'Diretor / Admin',  'desc' => 'Acesso total ao sistema, relatórios e configurações gerais.',   'cor' => '#C0392B'],
    ['icone' => 'fa-building-columns', 'titulo' => 'Secretaria',       'desc' => 'Matrículas, documentos, processos administrativos e propinas.', 'cor' => '#0066FF'],
    ['icone' => 'fa-chalkboard-user',  'titulo' => 'Professor',        'desc' => 'Lançamento de notas, presenças, sumários e materiais.',         'cor' => '#00A37A'],
    ['icone' => 'fa-user-graduate',    'titulo' => 'Aluno',            'desc' => 'Consulta de notas, horários, mensagens e materiais de apoio.',  'cor' => '#F5A623'],
    ['icone' => 'fa-people-roof',      'titulo' => 'Encarregado',      'desc' => 'Acompanhamento do educando: notas, presenças e comunicados.',   'cor' => '#8B5CF6'],
];
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero aceder-hero">
    <div class="container">
        <?php if ($app): ?>
        <div class="breadcrumb">
            <a href="<?= $rootPath ?>">Início</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <a href="<?= $rootPath ?>aceder.php">Aplicações</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= h($app['nome']) ?></span>
        </div>
        <h1><i class="fas fa-sign-in-alt"></i> Aceder ao <?= h($app['nome']) ?></h1>
        <p>Selecione o seu perfil de utilizador para continuar para o sistema.</p>
        <?php else: ?>
        <h1><i class="fas fa-th-large"></i> As Nossas Aplicações</h1>
        <p>Escolha a aplicação que deseja aceder.</p>
        <?php endif; ?>
    </div>
</div>

<?php if (!$app): ?>
<section class="aceder-apps-section">
    <div class="container">
        <div class="aceder-apps-grid">
            <?php foreach(getAplicacoes() as $a): ?>
            <a href="<?= $rootPath ?>aceder.php?app=<?= $a['id'] ?>" class="aceder-app-card">
                <div class="aceder-app-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="aceder-app-info">
                    <h3><?= h($a['nome']) ?></h3>
                    <p><?= h($a['descricao']) ?></p>
                </div>
                <i class="fas fa-arrow-right aceder-app-arrow"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php else: ?>
<section class="aceder-perfis-section">
    <div class="container">
        <div class="aceder-intro">
            <div class="aceder-intro-icon"><i class="fas fa-user-circle"></i></div>
            <div>
                <h2>Quem é você?</h2>
                <p>Selecione o seu perfil para ser direcionado ao sistema <strong><?= h($app['nome']) ?></strong>.</p>
            </div>
        </div>

        <div class="aceder-perfis-grid">
            <?php foreach($perfis as $p): ?>
            <a href="<?= h($app['url'] ?: '#') ?>" target="_blank" class="aceder-perfil-card" style="--perfil-cor: <?= $p['cor'] ?>">
                <div class="perfil-icon-wrap">
                    <i class="fas <?= $p['icone'] ?>"></i>
                </div>
                <div class="perfil-body">
                    <h3><?= $p['titulo'] ?></h3>
                    <p><?= $p['desc'] ?></p>
                </div>
                <div class="perfil-enter">
                    <span>Entrar</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="aceder-demo-box">
            <div class="aceder-demo-header">
                <i class="fas fa-calendar-alt"></i>
                <div>
                    <strong>Não tem acesso? Solicite uma Demonstração</strong>
                    <span>Preencha o formulário e entraremos em contacto por WhatsApp.</span>
                </div>
            </div>
            <form class="aceder-demo-form" id="form-demo" onsubmit="submeterDemo(event)">
                <div class="aceder-demo-fields">
                    <input type="text" id="demo-nome" placeholder="O seu nome *" required maxlength="80">
                    <input type="tel" id="demo-tel" placeholder="Telefone / WhatsApp *" required maxlength="30">
                    <input type="text" id="demo-escola" placeholder="Nome da escola (opcional)" maxlength="100">
                </div>
                <button type="submit" class="btn-demo-submit">
                    <i class="fab fa-whatsapp"></i> Solicitar via WhatsApp
                </button>
            </form>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function submeterDemo(e) {
    e.preventDefault();
    const nome   = document.getElementById('demo-nome').value.trim();
    const tel    = document.getElementById('demo-tel').value.trim();
    const escola = document.getElementById('demo-escola').value.trim();
    const texto  = 'Olá! Gostaria de solicitar uma demonstração do Super Escola.' +
                   '\n\nNome: ' + nome +
                   '\nTelefone: ' + tel +
                   (escola ? '\nEscola: ' + escola : '') +
                   '\n\nAguardo o vosso contacto. Obrigado!';
    const url = 'https://api.whatsapp.com/send?phone=244926219731&text=' + encodeURIComponent(texto) + '&type=phone_number&app_absent=0';
    window.open(url, '_blank');
}
</script>
<?php include 'includes/footer.php'; ?>
