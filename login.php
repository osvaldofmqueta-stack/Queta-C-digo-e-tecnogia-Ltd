<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (clienteLogado()) {
    header('Location: ' . ($_GET['redirect'] ?? '/'));
    exit;
}

$rootPath  = '/';
$pageTitle = 'Entrar na Conta';
$planoId   = (int)($_GET['plano'] ?? 0);
$redirect  = $_GET['redirect'] ?? '/carrinho.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $erro = 'Preencha o email e a senha.';
    } else {
        $c = loginCliente($email, $password);
        if (!$c) {
            $erro = 'Email ou senha incorrectos. Verifique os seus dados.';
        } else {
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
                <div class="auth-icon"><i class="fas fa-sign-in-alt"></i></div>
                <h1>Entrar na Conta</h1>
                <p>Aceda à sua conta para gerir encomendas e o carrinho de compras.</p>
            </div>

            <?php if ($erro): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= h($erro) ?></div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="auth-field">
                    <label>Email</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="email@exemplo.com" value="<?= h($_POST['email'] ?? '') ?>" required autofocus>
                    </div>
                </div>
                <div class="auth-field">
                    <label>Senha</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="A sua senha" required>
                    </div>
                </div>
                <button type="submit" class="auth-submit">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
            <div class="auth-footer-link">
                Ainda não tem conta? <a href="<?= $rootPath ?>registro.php<?= $planoId ? '?plano='.$planoId : '' ?>">Criar conta grátis</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
