<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (clienteLogado()) {
    $redirect = $_GET['redirect'] ?? '/';
    header('Location: ' . $redirect);
    exit;
}

$rootPath  = '/';
$pageTitle = 'Criar Conta';
$planoId   = (int)($_GET['plano'] ?? 0);
$redirect  = $_GET['redirect'] ?? '/carrinho.php';
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $tel      = trim($_POST['telefone'] ?? '');
    $escola   = trim($_POST['escola'] ?? '');
    if (!$nome || !$email || !$password) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } elseif (strlen($password) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $res = registarCliente($nome, $email, $password, $tel, $escola);
        if (isset($res['erro'])) {
            $erro = $res['erro'];
        } else {
            loginCliente($email, $password);
            if ($planoId) adicionarCarrinho($_SESSION['cliente_id'], $planoId);
            header('Location: ' . $redirect);
            exit;
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon"><i class="fas fa-user-plus"></i></div>
                <h1>Criar Conta</h1>
                <p>Registe-se para adicionar planos ao carrinho e fazer encomendas.</p>
            </div>

            <?php if ($erro): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= h($erro) ?></div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <?php if ($planoId): ?>
                <input type="hidden" name="plano_id" value="<?= $planoId ?>">
                <?php endif; ?>
                <div class="auth-field">
                    <label>Nome completo *</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nome" placeholder="O seu nome" value="<?= h($_POST['nome'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="auth-field">
                    <label>Email *</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="email@exemplo.com" value="<?= h($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="auth-field">
                    <label>Senha *</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                </div>
                <div class="auth-field-row">
                    <div class="auth-field">
                        <label>Telefone</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="telefone" placeholder="+244 9XX XXX XXX" value="<?= h($_POST['telefone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="auth-field">
                        <label>Escola / Instituição</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-school"></i>
                            <input type="text" name="escola" placeholder="Nome da escola" value="<?= h($_POST['escola'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <button type="submit" class="auth-submit">
                    <i class="fas fa-user-plus"></i> Criar Conta
                </button>
            </form>
            <div class="auth-footer-link">
                Já tem conta? <a href="<?= $rootPath ?>login.php<?= $planoId ? '?plano='.$planoId : '' ?>">Entrar</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
