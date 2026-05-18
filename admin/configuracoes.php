<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campos = ['site_nome','site_slogan','site_email','whatsapp_numero','whatsapp_mensagem','youtube_video','demo_link'];
    foreach ($campos as $campo) {
        setConfig($campo, trim($_POST[$campo] ?? ''));
    }
    if (!empty($_FILES['logo']['name'])) {
        $uploaded = uploadImagem($_FILES['logo'], 'uploads/');
        if ($uploaded) setConfig('logo', $uploaded);
    }
    $msg = 'Configurações guardadas com sucesso!';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-cog"></i> Configurações do Website</h1>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="admin-card">
                <h2><i class="fas fa-info-circle"></i> Informações do Site</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome da Empresa *</label>
                        <input type="text" name="site_nome" value="<?= h(getConfig('site_nome')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Slogan</label>
                        <input type="text" name="site_slogan" value="<?= h(getConfig('site_slogan')) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email de Contacto</label>
                    <input type="email" name="site_email" value="<?= h(getConfig('site_email')) ?>">
                </div>
                <div class="form-group">
                    <label>Logo da Empresa</label>
                    <?php $logo = getConfig('logo'); if ($logo && file_exists($logo)): ?>
                    <img src="../<?= h($logo) ?>" style="max-height:60px; display:block; margin-bottom:8px; border-radius:8px; border:1px solid var(--border);">
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/*" onchange="previewImage(this,'logo-prev')">
                    <img id="logo-prev" style="max-height:60px; margin-top:8px; border-radius:8px; display:none;">
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fab fa-whatsapp"></i> WhatsApp & Contacto</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Número WhatsApp (com código do país)</label>
                        <input type="text" name="whatsapp_numero" value="<?= h(getConfig('whatsapp_numero')) ?>" placeholder="244923000000">
                        <small style="color:var(--gray);">Ex: 244923000000 (Angola)</small>
                    </div>
                    <div class="form-group">
                        <label>Mensagem Pré-definida WhatsApp</label>
                        <input type="text" name="whatsapp_mensagem" value="<?= h(getConfig('whatsapp_mensagem')) ?>">
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fas fa-play-circle"></i> Vídeo de Demonstração</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>URL do Vídeo (YouTube Embed)</label>
                        <input type="text" name="youtube_video" value="<?= h(getConfig('youtube_video')) ?>" placeholder="https://www.youtube.com/embed/VIDEO_ID">
                        <small style="color:var(--gray);">Use o link embed do YouTube: youtube.com/embed/ID_DO_VIDEO</small>
                    </div>
                    <div class="form-group">
                        <label>Link do Botão "Demonstração"</label>
                        <input type="text" name="demo_link" value="<?= h(getConfig('demo_link')) ?>" placeholder="#contacto">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Configurações</button>
        </form>
    </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
