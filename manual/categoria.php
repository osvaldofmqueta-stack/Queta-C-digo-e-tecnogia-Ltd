<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
$rootPath = '/';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$categoria = getCategoria($id);
if (!$categoria) { header('Location: index.php'); exit; }

$topicos = getTopicosManual($id);
$recentTopicos = getTopicosRecentes(5);
$categorias = getCategoriasManual();

$pageTitle = h($categoria['nome']) . ' — Manual de Apoio';
$pageDesc = $categoria['descricao'] ?? 'Artigos e tutoriais sobre ' . $categoria['nome'];
?>
<?php include '../includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= $rootPath ?>">Início</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <a href="index.php">Manual de Apoio</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= h($categoria['nome']) ?></span>
        </div>
        <h1><i class="fas <?= h($categoria['icone']) ?>"></i> <?= h($categoria['nome']) ?></h1>
        <?php if ($categoria['descricao']): ?><p><?= h($categoria['descricao']) ?></p><?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="manual-layout">
        <aside class="manual-sidebar">
            <div class="sidebar-title"><i class="fas fa-list"></i> Categorias</div>
            <ul class="sidebar-menu">
                <?php foreach ($categorias as $cat): ?>
                <li><a href="categoria.php?id=<?= $cat['id'] ?>" class="<?= $cat['id']==$id ? 'active' : '' ?>">
                    <i class="fas <?= h($cat['icone']) ?>"></i> <?= h($cat['nome']) ?>
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

        <div class="manual-content-area">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <h2 style="font-size:22px; font-weight:800; color:var(--dark);"><?= count($topicos) ?> Tópico(s) em <?= h($categoria['nome']) ?></h2>
                <a href="index.php" class="btn-secondary" style="padding:10px 20px; font-size:14px;"><i class="fas fa-arrow-left"></i> Voltar</a>
            </div>

            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="search-input" placeholder="Pesquisar tópicos...">
            </div>

            <?php if (empty($topicos)): ?>
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <p>Nenhum tópico disponível nesta categoria ainda.</p>
            </div>
            <?php else: ?>
            <ul class="topics-list">
                <?php foreach ($topicos as $t): ?>
                <li class="topic-item" data-searchable>
                    <a href="topico.php?id=<?= $t['id'] ?>">
                        <span><i class="fas fa-file-alt" style="color:var(--primary); margin-right:10px;"></i><?= h($t['titulo']) ?></span>
                        <span class="topic-meta">
                            <span><i class="fas fa-eye"></i> <?= $t['visualizacoes'] ?></span>
                            <span><?= timeAgo($t['criado_em']) ?></span>
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
