<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar' || $acao === 'editar') {
        $appId = (int)$_POST['aplicacao_id'];
        $titulo = trim($_POST['titulo'] ?? '');
        $desc = trim($_POST['descricao'] ?? '');
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = (int)($_POST['ordem'] ?? 0);
        $imagem = trim($_POST['imagem_atual'] ?? '');
        if (!empty($_FILES['imagem']['name'])) {
            $uploaded = uploadImagem($_FILES['imagem'], 'uploads/');
            if ($uploaded) $imagem = $uploaded;
        }
        if ($acao === 'criar') {
            $stmt = $db->prepare("INSERT INTO funcionalidades (aplicacao_id, titulo, descricao, imagem, destaque, ativo, ordem) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$appId ?: null, $titulo, $desc, $imagem, $destaque, $ativo, $ordem]);
            $msg = 'Funcionalidade criada!';
        } else {
            $id = (int)$_POST['id'];
            $stmt = $db->prepare("UPDATE funcionalidades SET aplicacao_id=?,titulo=?,descricao=?,imagem=?,destaque=?,ativo=?,ordem=? WHERE id=?");
            $stmt->execute([$appId ?: null, $titulo, $desc, $imagem, $destaque, $ativo, $ordem, $id]);
            $msg = 'Funcionalidade atualizada!';
        }
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM funcionalidades WHERE id=$id");
        $msg = 'Eliminado.';
    }
}

$funcs = $db->query("SELECT f.*, a.nome as app_nome FROM funcionalidades f LEFT JOIN aplicacoes a ON f.aplicacao_id=a.id ORDER BY f.ordem")->fetchAll();
$apps = getAplicacoes(false);
$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM funcionalidades WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}
?>
<?php $pageTitle = 'Funcionalidades'; include 'partials/head.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-star"></i> Funcionalidades em Destaque</h1>
        <button class="btn-primary" onclick="openModal('modal-func')" style="font-size:14px; padding:10px 20px;">
            <i class="fas fa-plus"></i> Nova Funcionalidade
        </button>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Funcionalidades (<?= count($funcs) ?>)</h2>
            <table class="admin-table">
                <thead><tr><th>#</th><th>Título</th><th>Aplicação</th><th>Destaque</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($funcs as $f): ?>
                <tr>
                    <td><?= $f['id'] ?></td>
                    <td><strong><?= h($f['titulo']) ?></strong><br><small style="color:var(--gray);"><?= h(mb_substr($f['descricao'],0,60)) ?></small></td>
                    <td><?= h($f['app_nome'] ?? '—') ?></td>
                    <td><?= $f['destaque'] ? '<span class="badge badge-info">Destaque</span>' : '—' ?></td>
                    <td><span class="badge <?= $f['ativo'] ? 'badge-success' : 'badge-danger' ?>"><?= $f['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="actions">
                        <a href="funcionalidades.php?editar=<?= $f['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i></a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
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
<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-func">
    <div class="modal">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar' : 'Nova Funcionalidade' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-func')">×</button>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><input type="hidden" name="imagem_atual" value="<?= h($editando['imagem']) ?>"><?php endif; ?>
            <div class="form-row">
                <div class="form-group">
                    <label>Aplicação</label>
                    <select name="aplicacao_id">
                        <option value="">— Geral —</option>
                        <?php foreach ($apps as $a): ?><option value="<?= $a['id'] ?>" <?= ($editando['aplicacao_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= h($a['nome']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Título *</label>
                <input type="text" name="titulo" required value="<?= h($editando['titulo'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3"><?= h($editando['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Imagem</label>
                <?php if ($editando && $editando['imagem'] && file_exists($editando['imagem'])): ?>
                <img src="../<?= h($editando['imagem']) ?>" style="max-height:60px; display:block; margin-bottom:8px; border-radius:8px;">
                <?php endif; ?>
                <input type="file" name="imagem" accept="image/*" onchange="previewImage(this,'img-f')">
                <img id="img-f" style="max-height:60px; margin-top:8px; border-radius:8px; display:none;">
            </div>
            <div class="form-row">
                <div class="form-group" style="padding-top:8px;">
                    <label><input type="checkbox" name="destaque" <?= ($editando['destaque'] ?? 0) ? 'checked' : '' ?>> Mostrar em Destaque</label>
                </div>
                <div class="form-group" style="padding-top:8px;">
                    <label><input type="checkbox" name="ativo" <?= (!$editando || $editando['ativo']) ? 'checked' : '' ?>> Ativo</label>
                </div>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
        </form>
    </div>
</div>
<?php if ($editando): ?><script>openModal('modal-func');</script><?php endif; ?>
<?php include 'partials/foot.php'; ?>