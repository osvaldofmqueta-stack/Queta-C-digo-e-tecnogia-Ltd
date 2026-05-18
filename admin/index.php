<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (adminLogin($user, $pass)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $erro = 'Utilizador ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin — Queta Tech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #0D1117 0%, #161B22 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; border-radius: 20px; padding: 48px; width: 100%; max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .login-logo { text-align: center; margin-bottom: 32px; }
        .login-logo .logo-text { font-size: 28px; }
        .login-logo p { color: var(--text-light); font-size: 14px; margin-top: 8px; }
        .login-card h2 { font-size: 22px; font-weight: 700; margin-bottom: 24px; color: var(--dark); text-align: center; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <div class="logo-text">
            <span class="logo-q">Q</span><span class="logo-rest">ueta</span>
            <span class="logo-dot">·</span>
            <span class="logo-tech">Tech</span>
        </div>
        <p>Painel de Controlo</p>
    </div>
    <h2>Entrar no Sistema</h2>
    <?php if ($erro): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= h($erro) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Utilizador</label>
            <input type="text" name="username" required placeholder="admin" autofocus>
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">
            <i class="fas fa-sign-in-alt"></i> Entrar
        </button>
    </form>
    <p style="text-align:center; margin-top:20px; font-size:13px; color:var(--gray);">Credenciais padrão: admin / admin123</p>
</div>
</body>
</html>
