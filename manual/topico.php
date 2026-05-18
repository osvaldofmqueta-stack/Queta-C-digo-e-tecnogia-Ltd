<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
$rootPath = '/';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$topico = getTopico($id);
if (!$topico) { header('Location: index.php'); exit; }

incrementarVisualizacoes($id);

$passos = getPassosTopico($id);
$perguntas = getPerguntasPublicadas($id);
$categorias = getCategoriasManual();
$topicosRelacionados = getTopicosManual($topico['categoria_id']);
$recentTopicos = getTopicosRecentes(5);

$pageTitle = h($topico['titulo']) . ' — Manual de Apoio';
$pageDesc = mb_substr($topico['conteudo'] ?? '', 0, 150);

$perguntaEnviada = false;
$perguntaErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_pergunta'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pergunta = trim($_POST['pergunta'] ?? '');
    if ($nome && $pergunta) {
        enviarPergunta($nome, $email, $pergunta, $id);
        $perguntaEnviada = true;
    } else {
        $perguntaErro = 'Por favor preencha o nome e a pergunta.';
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= $rootPath ?>">Início</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <a href="index.php">Manual</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <a href="categoria.php?id=<?= $topico['categoria_id'] ?>"><?= h($topico['categoria_nome']) ?></a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= h($topico['titulo']) ?></span>
        </div>
        <h1><?= h($topico['titulo']) ?></h1>
        <div class="topic-meta-bar">
            <span><i class="fas fa-folder"></i> <?= h($topico['categoria_nome']) ?></span>
            <span><i class="fas fa-eye"></i> <?= $topico['visualizacoes'] ?> visualizações</span>
            <span><i class="fas fa-clock"></i> <?= timeAgo($topico['criado_em']) ?></span>
        </div>
    </div>
</div>

<div class="container">
    <div class="manual-layout">
        <aside class="manual-sidebar">
            <div class="sidebar-title"><i class="fas fa-list"></i> Nesta Categoria</div>
            <ul class="sidebar-menu">
                <?php foreach ($topicosRelacionados as $t): ?>
                <li><a href="topico.php?id=<?= $t['id'] ?>" class="<?= $t['id']==$id ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i> <?= h($t['titulo']) ?>
                </a></li>
                <?php endforeach; ?>
            </ul>
            <div class="sidebar-title" style="margin-top:16px;"><i class="fas fa-th-large"></i> Outras Categorias</div>
            <ul class="sidebar-menu">
                <?php foreach ($categorias as $cat): ?>
                <li><a href="categoria.php?id=<?= $cat['id'] ?>" class="<?= $cat['id']==$topico['categoria_id'] ? 'active' : '' ?>">
                    <i class="fas <?= h($cat['icone']) ?>"></i> <?= h($cat['nome']) ?>
                </a></li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="manual-content-area">
            <!-- INTRO -->
            <?php if ($topico['conteudo']): ?>
            <div class="topic-header">
                <p style="font-size:16px; color:var(--text-light); line-height:1.7;"><?= nl2br(h($topico['conteudo'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- VIDEO -->
            <?php if ($topico['video_url']): ?>
            <div class="video-section-manual">
                <h3><i class="fas fa-play-circle" style="color:var(--primary);"></i> Vídeo Tutorial</h3>
                <div class="video-wrapper" style="max-width:640px; aspect-ratio:16/9;">
                    <iframe src="<?= h($topico['video_url']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            <?php endif; ?>

            <!-- PASSOS -->
            <?php if (!empty($passos)): ?>
            <div class="steps-container">
                <h3 style="font-size:20px; font-weight:700; margin-bottom:20px; color:var(--dark);">
                    <i class="fas fa-list-ol" style="color:var(--primary);"></i> Passo a Passo
                </h3>
                <?php foreach ($passos as $i => $passo): ?>
                <div class="step-item">
                    <div class="step-header">
                        <div class="step-number"><?= $i + 1 ?></div>
                        <div class="step-title"><?= h($passo['titulo']) ?></div>
                    </div>
                    <div class="step-body">
                        <?php if ($passo['descricao']): ?><p><?= nl2br(h($passo['descricao'])) ?></p><?php endif; ?>
                        <?php if ($passo['imagem'] && file_exists($passo['imagem'])): ?>
                        <img src="<?= h($passo['imagem']) ?>" alt="Passo <?= $i+1 ?>" style="max-width:600px;">
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- PERGUNTAS PUBLICADAS -->
            <?php if (!empty($perguntas)): ?>
            <div style="margin-bottom:32px;">
                <h3 style="font-size:20px; font-weight:700; margin-bottom:20px; color:var(--dark);">
                    <i class="fas fa-comments" style="color:var(--primary);"></i> Perguntas & Respostas
                </h3>
                <?php foreach ($perguntas as $p): ?>
                <div class="qa-card">
                    <div class="qa-question">
                        <p><?= h($p['pergunta']) ?></p>
                        <div class="qa-meta"><i class="fas fa-user"></i> <?= h($p['nome']) ?> · <?= timeAgo($p['criado_em']) ?></div>
                    </div>
                    <?php if ($p['resposta']): ?>
                    <div class="qa-answer">
                        <div class="qa-answer-label"><i class="fas fa-check-circle"></i> Resposta Oficial</div>
                        <p><?= nl2br(h($p['resposta'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- FORMULÁRIO DE PERGUNTA -->
            <div class="ask-form">
                <h3><i class="fas fa-question-circle" style="color:var(--primary);"></i> Enviar uma Pergunta</h3>
                <?php if ($perguntaEnviada): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> A sua pergunta foi enviada! Responderemos em breve.</div>
                <?php elseif ($perguntaErro): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= h($perguntaErro) ?></div>
                <?php endif; ?>
                <?php if (!$perguntaEnviada): ?>
                <form method="post" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>O seu nome *</label>
                            <input type="text" name="nome" required placeholder="Ex: João Silva">
                        </div>
                        <div class="form-group">
                            <label>Email (opcional)</label>
                            <input type="email" name="email" placeholder="para receber a resposta por email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>A sua pergunta *</label>
                        <textarea name="pergunta" required placeholder="Descreva a sua dúvida com o máximo de detalhe..."></textarea>
                    </div>
                    <button type="submit" name="enviar_pergunta" class="btn-primary"><i class="fas fa-paper-plane"></i> Enviar Pergunta</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
