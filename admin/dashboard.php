<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$stats = [
    'aplicacoes' => $db->query("SELECT COUNT(*) FROM aplicacoes WHERE ativo=1")->fetchColumn(),
    'categorias' => $db->query("SELECT COUNT(*) FROM categorias_manual WHERE ativo=1")->fetchColumn(),
    'topicos'    => $db->query("SELECT COUNT(*) FROM topicos_manual WHERE ativo=1")->fetchColumn(),
    'perguntas'  => $db->query("SELECT COUNT(*) FROM perguntas WHERE respondido=0")->fetchColumn(),
];
$recentPerguntas = $db->query("SELECT * FROM perguntas WHERE respondido=0 ORDER BY criado_em DESC LIMIT 5")->fetchAll();
$recentTopicos = getTopicosRecentes(5);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Admin Queta Tech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <div class="admin-header-actions">
            <a href="../" target="_blank" class="btn-sm edit"><i class="fas fa-external-link-alt"></i> Ver Site</a>
            <a href="logout.php" class="btn-sm delete"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>
    <div class="admin-content">
        <p style="color:var(--text-light); margin-bottom:24px;">Bem-vindo, <strong><?= h($_SESSION['admin_nome']) ?></strong>! Aqui está um resumo do seu website.</p>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-graduation-cap"></i></div>
                <div class="stat-info"><div class="stat-num"><?= $stats['aplicacoes'] ?></div><div class="stat-label">Aplicações</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-folder"></i></div>
                <div class="stat-info"><div class="stat-num"><?= $stats['categorias'] ?></div><div class="stat-label">Categorias Manual</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-file-alt"></i></div>
                <div class="stat-info"><div class="stat-num"><?= $stats['topicos'] ?></div><div class="stat-label">Tópicos</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-question-circle"></i></div>
                <div class="stat-info"><div class="stat-num"><?= $stats['perguntas'] ?></div><div class="stat-label">Perguntas por Responder</div></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; flex-wrap:wrap;">
            <div class="admin-card">
                <h2><i class="fas fa-question-circle"></i> Perguntas Pendentes</h2>
                <?php if (empty($recentPerguntas)): ?>
                <p style="color:var(--text-light);">Nenhuma pergunta pendente.</p>
                <?php else: ?>
                <?php foreach ($recentPerguntas as $p): ?>
                <div style="padding:12px 0; border-bottom:1px solid var(--border);">
                    <strong style="font-size:14px;"><?= h(mb_substr($p['pergunta'], 0, 60)) ?>...</strong>
                    <p style="font-size:12px; color:var(--gray); margin-top:4px;"><i class="fas fa-user"></i> <?= h($p['nome']) ?> · <?= timeAgo($p['criado_em']) ?></p>
                    <a href="perguntas.php?responder=<?= $p['id'] ?>" class="btn-sm success" style="margin-top:8px;"><i class="fas fa-reply"></i> Responder</a>
                </div>
                <?php endforeach; ?>
                <a href="perguntas.php" style="display:block; margin-top:12px; font-size:13px; color:var(--primary);">Ver todas →</a>
                <?php endif; ?>
            </div>
            <div class="admin-card">
                <h2><i class="fas fa-clock"></i> Últimos Tópicos</h2>
                <?php foreach ($recentTopicos as $t): ?>
                <div style="padding:10px 0; border-bottom:1px solid var(--border);">
                    <a href="manual.php" style="font-size:14px; font-weight:600; color:var(--dark);"><?= h($t['titulo']) ?></a>
                    <p style="font-size:12px; color:var(--gray); margin-top:2px;"><?= h($t['categoria_nome']) ?> · <?= timeAgo($t['criado_em']) ?></p>
                </div>
                <?php endforeach; ?>
                <a href="manual.php" style="display:block; margin-top:12px; font-size:13px; color:var(--primary);">Gerir Manual →</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
