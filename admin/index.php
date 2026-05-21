<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (isAdminLoggedIn()) { header('Location: dashboard.php'); exit; }
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (adminLogin($user, $pass)) { header('Location: dashboard.php'); exit; }
    else $erro = 'Utilizador ou senha incorretos. Verifique os dados e tente novamente.';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — Painel Queta</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="login-page">
    <!-- Brand Side -->
    <div class="login-brand">
        <div class="login-brand-content">
            <div class="login-brand-logo">
                <div class="login-brand-icon">Q</div>
                <div class="login-brand-name">
                    <strong>Queta</strong>
                    <span>Código e Tecnologia, Lta.</span>
                </div>
            </div>
            <h1>Painel de<br>Controlo</h1>
            <p>Gira todo o conteúdo do Super Escola — desde planos e funcionalidades até utilizadores e analytics — num único lugar.</p>
            <div class="login-features">
                <div class="login-feature"><i class="fas fa-check-circle"></i> Gestão completa de conteúdo</div>
                <div class="login-feature"><i class="fas fa-check-circle"></i> Analytics em tempo real</div>
                <div class="login-feature"><i class="fas fa-check-circle"></i> Suporte a clientes integrado</div>
                <div class="login-feature"><i class="fas fa-check-circle"></i> Configurações do website</div>
            </div>
        </div>
    </div>

    <!-- Form Side -->
    <div class="login-form-side">
        <div class="login-form-box">
            <div class="login-form-header">
                <h2>Bem-vindo de volta</h2>
                <p>Entre com as suas credenciais para continuar</p>
            </div>

            <?php if ($erro): ?>
            <div class="alert alert-error" style="margin-bottom:20px;">
                <i class="fas fa-exclamation-circle"></i> <?= h($erro) ?>
            </div>
            <?php endif; ?>

            <form method="post">
                <div class="login-input-group">
                    <label>Utilizador</label>
                    <i class="fas fa-user li-icon"></i>
                    <input type="text" name="username" required placeholder="admin" autofocus>
                </div>
                <div class="login-input-group">
                    <label>Senha</label>
                    <i class="fas fa-lock li-icon"></i>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="login-submit">
                    <i class="fas fa-sign-in-alt"></i> Entrar no Painel
                </button>
            </form>

            <p class="login-hint">
                <i class="fas fa-info-circle"></i>
                Credenciais padrão: <strong>admin</strong> / <strong>admin123</strong>
            </p>
        </div>
    </div>
</div>
</body>
</html>
