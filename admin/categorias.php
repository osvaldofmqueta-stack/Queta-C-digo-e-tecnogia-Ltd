<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';
$icones = ['fa-rocket','fa-book','fa-user-graduate','fa-money-bill-wave','fa-chart-bar','fa-cog','fa-school','fa-graduation-cap','fa-users','fa-file-alt','fa-question-circle','fa-bell','fa-calendar','fa-envelope','fa-shield-alt'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar' || $acao === 'editar') {
        $nome = trim($_POST['nome'] ?? '');
        $desc = trim($_POST['descricao'] ?? '');
        $icone = $_POST['icone'] ?? 'fa-book';
        $appId = (int)($_POST['aplicacao_id'] ?? 0);
        $ordem = (int)($_POST['ordem'] ?? 0);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        if ($acao === 'criar') {
            $stmt = $db->prepare("INSERT INTO categorias_manual (nome, descricao, icone, aplicacao_id, ativo, ordem) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nome, $desc, $icone, $appId ?: null, $ativo, $ordem]);
            $msg = 'Categoria criada!';
        } else {
            $id = (int)$_POST['id'];
            $stmt = $db->prepare("UPDATE categorias_manual SET nome=?,descricao=?,icone=?,aplicacao_id=?,ativo=?,ordem=? WHERE id=?");
            $stmt->execute([$nome, $desc, $icone, $appId ?: null, $ativo, $ordem, $id]);
            $msg = 'Categoria atualizada!';
        }
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM categorias_manual WHERE id=$id");
        $msg = 'Categoria eliminada.';
    }
}

$categorias = getCategoriasManual(null, false);
$aplicacoes = getAplicacoes(false);
$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM categorias_manual WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}
?>
<?php $pageTitle = 'Categorias'; include 'partials/head.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-folder"></i> Categorias do Manual</h1>
        <button class="btn-primary" onclick="openModal('modal-cat')" style="font-size:14px; padding:10px 20px;">
            <i class="fas fa-plus"></i> Nova Categoria
        </button>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Categorias (<?= count($categorias) ?>)</h2>
            <table class="admin-table">
                <thead><tr><th>#</th><th>Nome</th><th>Aplicação</th><th>Ordem</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><i class="fas <?= h($c['icone']) ?>" style="color:var(--primary);margin-right:8px;"></i><strong><?= h($c['nome']) ?></strong></td>
                    <td><?= h($c['aplicacao_nome'] ?? '—') ?></td>
                    <td><?= $c['ordem'] ?></td>
                    <td><span class="badge <?= $c['ativo'] ? 'badge-success' : 'badge-danger' ?>"><?= $c['ativo'] ? 'Ativa' : 'Inativa' ?></span></td>
                    <td class="actions">
                        <a href="categorias.php?editar=<?= $c['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i></a>
                        <a href="manual.php?cat=<?= $c['id'] ?>" class="btn-sm success"><i class="fas fa-file-alt"></i> Tópicos</a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-cat">
    <div class="modal">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar Categoria' : 'Nova Categoria' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-cat')">×</button>
        </div>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
            <div class="form-group">
                <label>Nome *</label>
                <input type="text" name="nome" required value="<?= h($editando['nome'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3"><?= h($editando['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ícone (Font Awesome)</label>
                    <select name="icone">
                        <?php foreach ($icones as $ic): ?>
                        <option value="<?= $ic ?>" <?= ($editando['icone'] ?? '') == $ic ? 'selected' : '' ?>><?= $ic ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Aplicação</label>
                    <select name="aplicacao_id">
                        <option value="">— Geral —</option>
                        <?php foreach ($aplicacoes as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($editando['aplicacao_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= h($a['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>">
                </div>
                <div class="form-group" style="padding-top:32px;">
                    <label><input type="checkbox" name="ativo" <?= (!$editando || $editando['ativo']) ? 'checked' : '' ?>> Ativa</label>
                </div>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
        </form>
    </div>
</div>
<?php if ($editando): ?><script>openModal('modal-cat');</script><?php endif; ?>
<?php include 'partials/foot.php'; ?>