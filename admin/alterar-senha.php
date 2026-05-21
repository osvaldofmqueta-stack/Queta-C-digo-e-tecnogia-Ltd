<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atual = $_POST['senha_atual'] ?? '';
    $nova = $_POST['senha_nova'] ?? '';
    $conf = $_POST['confirmar'] ?? '';

    $stmt = $db->prepare("SELECT password FROM admin_usuarios WHERE id=?");
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();

    if (!password_verify($atual, $user['password'])) {
        $erro = 'A senha atual está incorreta.';
    } elseif (strlen($nova) < 6) {
        $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
    } elseif ($nova !== $conf) {
        $erro = 'A confirmação da senha não corresponde.';
    } else {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $db->prepare("UPDATE admin_usuarios SET password=? WHERE id=?")->execute([$hash, $_SESSION['admin_id']]);
        $msg = 'Senha alterada com sucesso!';
    }
}
?>
<?php $pageTitle = 'Alterar Senha'; include 'partials/head.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header"><h1><i class="fas fa-key"></i> Alterar Senha</h1></div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <?php if ($erro): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= h($erro) ?></div><?php endif; ?>
        <div class="admin-card" style="max-width:480px;">
            <h2><i class="fas fa-lock"></i> Alterar Senha de Acesso</h2>
            <form method="post">
                <div class="form-group">
                    <label>Senha Atual</label>
                    <input type="password" name="senha_atual" required>
                </div>
                <div class="form-group">
                    <label>Nova Senha</label>
                    <input type="password" name="senha_nova" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirmar Nova Senha</label>
                    <input type="password" name="confirmar" required minlength="6">
                </div>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Nova Senha</button>
            </form>
        </div>
    </div>
</div>
<?php include 'partials/foot.php'; ?>