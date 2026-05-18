<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
$rootPath = '/';
$pageTitle = 'Manual de Apoio';
$pageDesc = 'Consulte o manual completo do Super Escola. Guias passo a passo organizados por categorias.';

$categorias = getCategoriasManual();
$recentTopicos = getTopicosRecentes(5);
$aplicacoes = getAplicacoes();

$filtroApp = isset($_GET['app']) ? (int)$_GET['app'] : null;
if ($filtroApp) {
    $categorias = getCategoriasManual($filtroApp);
}
?>
<?php include '../includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= $rootPath ?>">Início</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Manual de Apoio</span>
        </div>
        <h1><i class="fas fa-book-open"></i> Manual de Apoio</h1>
        <p>Encontre guias detalhados e tutoriais passo a passo para usar o Super Escola</p>
    </div>
</div>

<div class="container">
    <div class="manual-layout">
        <!-- SIDEBAR -->
        <aside class="manual-sidebar">
            <div class="sidebar-title"><i class="fas fa-filter"></i> Filtrar por Aplicação</div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="<?= !$filtroApp ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Todas as Categorias</a></li>
                <?php foreach ($aplicacoes as $app): ?>
                <li><a href="index.php?app=<?= $app['id'] ?>" class="<?= $filtroApp == $app['id'] ? 'active' : '' ?>">
                    <i class="fas fa-graduation-cap"></i> <?= h($app['nome']) ?>
                </a></li>
                <?php endforeach; ?>
            </ul>
            <div class="sidebar-title" style="margin-top:16px;"><i class="fas fa-clock"></i> Artigos Recentes</div>
            <div style="padding:12px 16px;">
                <?php foreach ($recentTopicos as $t): ?>
                <div class="recent-post-item">
                    <div>
                        <a href="topico.php?id=<?= $t['id'] ?>"><?= h($t['titulo']) ?></a>
                        <span class="date"><?= timeAgo($t['criado_em']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- CONTENT -->
        <div class="manual-content-area">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="search-input" placeholder="Pesquisar categorias e tópicos...">
            </div>

            <?php if (empty($categorias)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>Nenhuma categoria disponível.</p>
            </div>
            <?php else: foreach ($categorias as $cat):
                $topicos = getTopicosManual($cat['id']);
            ?>
            <div class="category-card" data-searchable onclick="window.location='categoria.php?id=<?= $cat['id'] ?>'">
                <div class="category-icon"><i class="fas <?= h($cat['icone']) ?>"></i></div>
                <div class="category-info">
                    <h3><?= h($cat['nome']) ?></h3>
                    <?php if ($cat['descricao']): ?><p><?= h($cat['descricao']) ?></p><?php endif; ?>
                    <div class="topic-count"><i class="fas fa-file-alt"></i> <?= count($topicos) ?> tópico(s)</div>
                </div>
                <div style="margin-left:auto; color:var(--primary);"><i class="fas fa-chevron-right"></i></div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
