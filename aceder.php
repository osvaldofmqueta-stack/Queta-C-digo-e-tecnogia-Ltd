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

$perfis = [
    ['icone' => 'fa-crown',            'titulo' => 'Diretor / Admin',       'desc' => 'Acesso total ao sistema, relatórios e configurações gerais.',        'cor' => '#C0392B'],
    ['icone' => 'fa-building-columns', 'titulo' => 'Secretaria',            'desc' => 'Matrículas, documentos, processos administrativos e propinas.',       'cor' => '#0066FF'],
    ['icone' => 'fa-chalkboard-user',  'titulo' => 'Professor',             'desc' => 'Lançamento de notas, presenças, sumários e materiais didáticos.',     'cor' => '#00A37A'],
    ['icone' => 'fa-user-graduate',    'titulo' => 'Aluno',                 'desc' => 'Consulta de notas, horários, mensagens e materiais de apoio.',        'cor' => '#F5A623'],
    ['icone' => 'fa-people-roof',      'titulo' => 'Encarregado',           'desc' => 'Acompanhamento do educando: notas, presenças e comunicados.',         'cor' => '#8B5CF6'],
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

        <div class="aceder-nota">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Não tem as suas credenciais?</strong>
                As credenciais de acesso são fornecidas pela sua instituição de ensino ou pela nossa equipa de suporte.
                <a href="<?= $whatsapp ?>" target="_blank">Contactar suporte <i class="fas fa-external-link-alt"></i></a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
