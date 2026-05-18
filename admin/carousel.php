<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar' || $acao === 'editar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $ordem = (int)($_POST['ordem'] ?? 0);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $imagem = trim($_POST['imagem_atual'] ?? '');

        if (!empty($_FILES['imagem']['name'])) {
            $uploaded = uploadImagem($_FILES['imagem'], 'uploads/carousel/');
            if ($uploaded) $imagem = $uploaded;
        }

        if ($acao === 'criar') {
            $stmt = $db->prepare("INSERT INTO carousel (titulo, descricao, imagem, link, ativo, ordem) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$titulo, $descricao, $imagem, $link, $ativo, $ordem]);
            $msg = 'Slide criado com sucesso!';
        } else {
            $id = (int)$_POST['id'];
            $stmt = $db->prepare("UPDATE carousel SET titulo=?, descricao=?, imagem=?, link=?, ativo=?, ordem=? WHERE id=?");
            $stmt->execute([$titulo, $descricao, $imagem, $link, $ativo, $ordem, $id]);
            $msg = 'Slide atualizado com sucesso!';
        }
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM carousel WHERE id=$id");
        $msg = 'Slide eliminado.';
    }
}

$slides = getCarousel(false);
$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM carousel WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carousel — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-images"></i> Carousel / Banners</h1>
        <button class="btn-primary" onclick="openModal('modal-slide')" style="font-size:14px; padding:10px 20px;">
            <i class="fas fa-plus"></i> Novo Slide
        </button>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>

        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Slides (<?= count($slides) ?>)</h2>
            <table class="admin-table">
                <thead><tr><th>#</th><th>Título</th><th>Link</th><th>Ordem</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($slides as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><strong><?= h($s['titulo']) ?></strong><?php if($s['descricao']): ?><br><small style="color:var(--gray);"><?= h(mb_substr($s['descricao'],0,60)) ?></small><?php endif; ?></td>
                    <td><?= h($s['link'] ?: '—') ?></td>
                    <td><?= $s['ordem'] ?></td>
                    <td><span class="badge <?= $s['ativo'] ? 'badge-success' : 'badge-danger' ?>"><?= $s['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="actions">
                        <a href="carousel.php?editar=<?= $s['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i></a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar este slide?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button class="btn-sm delete" type="submit"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CRIAR/EDITAR -->
<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-slide">
    <div class="modal">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar Slide' : 'Novo Slide' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-slide')">×</button>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><input type="hidden" name="imagem_atual" value="<?= h($editando['imagem']) ?>"><?php endif; ?>
            <div class="form-group">
                <label>Título *</label>
                <input type="text" name="titulo" required value="<?= h($editando['titulo'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3"><?= h($editando['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Imagem (JPG/PNG, máx 5MB)</label>
                <?php if ($editando && $editando['imagem'] && file_exists($editando['imagem'])): ?>
                <img src="../<?= h($editando['imagem']) ?>" style="max-height:80px; display:block; margin-bottom:8px; border-radius:8px;">
                <?php endif; ?>
                <input type="file" name="imagem" accept="image/*" onchange="previewImage(this,'img-preview')">
                <img id="img-preview" style="max-height:80px; margin-top:8px; border-radius:8px; display:none;">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Link (URL)</label>
                    <input type="text" name="link" value="<?= h($editando['link'] ?? '') ?>" placeholder="#demo">
                </div>
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>">
                </div>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="ativo" <?= (!$editando || $editando['ativo']) ? 'checked' : '' ?>> Ativo</label>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
        </form>
    </div>
</div>
<script src="../assets/js/main.js"></script>
<?php if ($editando): ?><script>openModal('modal-slide');</script><?php endif; ?>
</body>
</html>
