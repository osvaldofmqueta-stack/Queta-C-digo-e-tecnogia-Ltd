<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';
$icones = ['fa-school','fa-graduation-cap','fa-university','fa-chalkboard-teacher','fa-user-graduate','fa-users','fa-child','fa-building','fa-landmark','fa-book'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar' || $acao === 'editar') {
        $appId = (int)$_POST['aplicacao_id'];
        $titulo = trim($_POST['titulo'] ?? '');
        $desc = trim($_POST['descricao'] ?? '');
        $icone = $_POST['icone'] ?? 'fa-school';
        $ordem = (int)($_POST['ordem'] ?? 0);
        if ($acao === 'criar') {
            $stmt = $db->prepare("INSERT INTO target_audience (aplicacao_id, titulo, descricao, icone, ordem) VALUES (?,?,?,?,?)");
            $stmt->execute([$appId ?: null, $titulo, $desc, $icone, $ordem]);
            $msg = 'Audiência criada!';
        } else {
            $id = (int)$_POST['id'];
            $stmt = $db->prepare("UPDATE target_audience SET aplicacao_id=?,titulo=?,descricao=?,icone=?,ordem=? WHERE id=?");
            $stmt->execute([$appId ?: null, $titulo, $desc, $icone, $ordem, $id]);
            $msg = 'Atualizado!';
        }
    } elseif ($acao === 'eliminar') {
        $db->exec("DELETE FROM target_audience WHERE id=".(int)$_POST['id']);
        $msg = 'Eliminado.';
    }
}

$audiencias = $db->query("SELECT ta.*, a.nome as app_nome FROM target_audience ta LEFT JOIN aplicacoes a ON ta.aplicacao_id=a.id ORDER BY ta.ordem")->fetchAll();
$apps = getAplicacoes(false);
$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM target_audience WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audiência — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-users"></i> Para Quem É</h1>
        <button class="btn-primary" onclick="openModal('modal-aud')" style="font-size:14px; padding:10px 20px;">
            <i class="fas fa-plus"></i> Novo Item
        </button>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Audiências (<?= count($audiencias) ?>)</h2>
            <table class="admin-table">
                <thead><tr><th>#</th><th>Título</th><th>Aplicação</th><th>Ícone</th><th>Ordem</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($audiencias as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><i class="fas <?= h($a['icone']) ?>" style="color:var(--primary);margin-right:8px;"></i><strong><?= h($a['titulo']) ?></strong></td>
                    <td><?= h($a['app_nome'] ?? '—') ?></td>
                    <td><?= h($a['icone']) ?></td>
                    <td><?= $a['ordem'] ?></td>
                    <td class="actions">
                        <a href="audience.php?editar=<?= $a['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i></a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
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
<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-aud">
    <div class="modal">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar' : 'Novo Item' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-aud')">×</button>
        </div>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
            <div class="form-group">
                <label>Aplicação</label>
                <select name="aplicacao_id">
                    <option value="">— Geral —</option>
                    <?php foreach ($apps as $a): ?><option value="<?= $a['id'] ?>" <?= ($editando['aplicacao_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= h($a['nome']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Título *</label>
                <input type="text" name="titulo" required value="<?= h($editando['titulo'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3"><?= h($editando['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ícone</label>
                    <select name="icone">
                        <?php foreach ($icones as $ic): ?><option value="<?= $ic ?>" <?= ($editando['icone'] ?? '') == $ic ? 'selected' : '' ?>><?= $ic ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>">
                </div>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
        </form>
    </div>
</div>
<script src="../assets/js/main.js"></script>
<?php if ($editando): ?><script>openModal('modal-aud');</script><?php endif; ?>
</body>
</html>
