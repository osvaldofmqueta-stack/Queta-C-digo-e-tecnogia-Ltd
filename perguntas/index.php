<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
$rootPath = '/';
$pageTitle = 'Perguntas & Respostas';
$pageDesc = 'Encontre respostas às perguntas mais frequentes sobre o Super Escola.';

$perguntaEnviada = false;
$perguntaErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_pergunta'])) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pergunta = trim($_POST['pergunta'] ?? '');
    if ($nome && $pergunta) {
        enviarPergunta($nome, $email, $pergunta);
        $perguntaEnviada = true;
    } else {
        $perguntaErro = 'Por favor preencha o nome e a pergunta.';
    }
}

$perguntas = getPerguntasPublicadas(null, 50);
$categorias = getCategoriasManual();
?>
<?php include '../includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= $rootPath ?>">Início</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Perguntas & Respostas</span>
        </div>
        <h1><i class="fas fa-comments"></i> Perguntas & Respostas</h1>
        <p>Encontre respostas para as dúvidas mais comuns sobre o Super Escola</p>
    </div>
</div>

<div class="container" style="padding-top:48px; padding-bottom:64px;">
    <div class="manual-layout">
        <aside class="manual-sidebar">
            <div class="sidebar-title"><i class="fas fa-book"></i> Manual de Apoio</div>
            <ul class="sidebar-menu">
                <li><a href="../manual/index.php"><i class="fas fa-home"></i> Ver Todas as Categorias</a></li>
                <?php foreach ($categorias as $cat): ?>
                <li><a href="../manual/categoria.php?id=<?= $cat['id'] ?>">
                    <i class="fas <?= h($cat['icone']) ?>"></i> <?= h($cat['nome']) ?>
                </a></li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="manual-content-area">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="search-input" placeholder="Pesquisar perguntas e respostas...">
            </div>

            <?php if (!empty($perguntas)): ?>
            <h2 style="font-size:20px; font-weight:700; margin-bottom:20px; color:var(--dark);">
                <i class="fas fa-list" style="color:var(--primary);"></i> <?= count($perguntas) ?> Pergunta(s) Respondida(s)
            </h2>
            <?php foreach ($perguntas as $p): ?>
            <div class="qa-card" data-searchable>
                <div class="qa-question">
                    <p><?= h($p['pergunta']) ?></p>
                    <div class="qa-meta">
                        <i class="fas fa-user"></i> <?= h($p['nome']) ?> · <?= timeAgo($p['criado_em']) ?>
                        <?php if ($p['topico_titulo']): ?>
                        · <a href="../manual/topico.php?id=<?= $p['topico_id'] ?>" style="color:var(--primary);">
                            <i class="fas fa-book"></i> <?= h($p['topico_titulo']) ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($p['resposta']): ?>
                <div class="qa-answer">
                    <div class="qa-answer-label"><i class="fas fa-check-circle"></i> Resposta Oficial</div>
                    <p><?= nl2br(h($p['resposta'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state" style="margin-bottom:32px;">
                <i class="fas fa-comments"></i>
                <p>Ainda não existem perguntas respondidas. Seja o primeiro a perguntar!</p>
            </div>
            <?php endif; ?>

            <!-- FORMULÁRIO -->
            <div class="ask-form" style="margin-top:32px;">
                <h3><i class="fas fa-question-circle" style="color:var(--primary);"></i> Faça a sua Pergunta</h3>
                <p style="color:var(--text-light); font-size:14px; margin-bottom:20px;">Não encontrou resposta? Envie a sua dúvida e responderemos em breve. A sua pergunta e a resposta ficarão disponíveis para ajudar outros utilizadores.</p>

                <?php if ($perguntaEnviada): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> A sua pergunta foi enviada com sucesso! Responderemos brevemente.</div>
                <?php elseif ($perguntaErro): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= h($perguntaErro) ?></div>
                <?php endif; ?>

                <?php if (!$perguntaEnviada): ?>
                <form method="post">
                    <div class="form-row">
                        <div class="form-group">
                            <label>O seu nome *</label>
                            <input type="text" name="nome" required placeholder="Ex: Maria Santos">
                        </div>
                        <div class="form-group">
                            <label>Email (opcional)</label>
                            <input type="email" name="email" placeholder="Para receber a resposta por email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>A sua pergunta *</label>
                        <textarea name="pergunta" required placeholder="Descreva a sua dúvida com o máximo de detalhe possível..."></textarea>
                    </div>
                    <button type="submit" name="enviar_pergunta" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Enviar Pergunta
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
