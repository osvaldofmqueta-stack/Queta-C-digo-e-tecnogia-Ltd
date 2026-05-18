<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar' || $acao === 'editar') {
        $nome = trim($_POST['nome'] ?? '');
        $desc = trim($_POST['descricao'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $ordem = (int)($_POST['ordem'] ?? 0);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $imagem = trim($_POST['imagem_atual'] ?? '');
        if (!empty($_FILES['imagem']['name'])) {
            $uploaded = uploadImagem($_FILES['imagem'], 'uploads/');
            if ($uploaded) $imagem = $uploaded;
        }
        if ($acao === 'criar') {
            $stmt = $db->prepare("INSERT INTO aplicacoes (nome, descricao, imagem, url, ativo, ordem) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nome, $desc, $imagem, $url, $ativo, $ordem]);
            $msg = 'Aplicação criada!';
        } else {
            $id = (int)$_POST['id'];
            $stmt = $db->prepare("UPDATE aplicacoes SET nome=?,descricao=?,imagem=?,url=?,ativo=?,ordem=? WHERE id=?");
            $stmt->execute([$nome, $desc, $imagem, $url, $ativo, $ordem, $id]);
            $msg = 'Aplicação atualizada!';
        }
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $db->exec("UPDATE aplicacoes SET ativo=0 WHERE id=$id");
        $msg = 'Aplicação desativada.';
    }
}

$apps = getAplicacoes(false);
$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM aplicacoes WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicações — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-graduation-cap"></i> Aplicações</h1>
        <button class="btn-primary" onclick="openModal('modal-app')" style="font-size:14px; padding:10px 20px;">
            <i class="fas fa-plus"></i> Nova Aplicação
        </button>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Aplicações (<?= count($apps) ?>)</h2>
            <table class="admin-table">
                <thead><tr><th>#</th><th>Nome</th><th>Descrição</th><th>Ordem</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($apps as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><strong><?= h($a['nome']) ?></strong></td>
                    <td style="max-width:300px;"><?= h(mb_substr($a['descricao'],0,80)) ?></td>
                    <td><?= $a['ordem'] ?></td>
                    <td><span class="badge <?= $a['ativo'] ? 'badge-success' : 'badge-danger' ?>"><?= $a['ativo'] ? 'Ativa' : 'Inativa' ?></span></td>
                    <td class="actions">
                        <a href="aplicacoes.php?editar=<?= $a['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i></a>
                        <a href="../aplicacao.php?id=<?= $a['id'] ?>" target="_blank" class="btn-sm edit"><i class="fas fa-eye"></i></a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Desativar?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button class="btn-sm delete" type="submit"><i class="fas fa-ban"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-app">
    <div class="modal">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar Aplicação' : 'Nova Aplicação' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-app')">×</button>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= $editando['id'] ?>">
            <input type="hidden" name="imagem_atual" value="<?= h($editando['imagem']) ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Nome *</label>
                <input type="text" name="nome" required value="<?= h($editando['nome'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="4"><?= h($editando['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Logo / Imagem</label>
                <?php if ($editando && $editando['imagem'] && file_exists($editando['imagem'])): ?>
                <img src="../<?= h($editando['imagem']) ?>" style="max-height:60px; display:block; margin-bottom:8px; border-radius:8px;">
                <?php endif; ?>
                <input type="file" name="imagem" accept="image/*" onchange="previewImage(this,'img-prev-app')">
                <img id="img-prev-app" style="max-height:60px; margin-top:8px; border-radius:8px; display:none;">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>URL / Link</label>
                    <input type="text" name="url" value="<?= h($editando['url'] ?? '') ?>" placeholder="#demo">
                </div>
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>">
                </div>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="ativo" <?= (!$editando || $editando['ativo']) ? 'checked' : '' ?>> Ativa (visível no site)</label>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
        </form>
    </div>
</div>
<script src="../assets/js/main.js"></script>
<?php if ($editando): ?><script>openModal('modal-app');</script><?php endif; ?>
</body>
</html>
