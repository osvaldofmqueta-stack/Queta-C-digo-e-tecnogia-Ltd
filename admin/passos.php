<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

$topicoId = isset($_GET['topico']) ? (int)$_GET['topico'] : 0;
$topico = $topicoId ? getTopico($topicoId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar' || $acao === 'editar') {
        $tid = (int)$_POST['topico_id'];
        $titulo = trim($_POST['titulo'] ?? '');
        $desc = trim($_POST['descricao'] ?? '');
        $ordem = (int)($_POST['ordem'] ?? 0);
        $imagem = trim($_POST['imagem_atual'] ?? '');
        if (!empty($_FILES['imagem']['name'])) {
            $uploaded = uploadImagem($_FILES['imagem'], 'uploads/manual/');
            if ($uploaded) $imagem = $uploaded;
        }
        if ($acao === 'criar') {
            $stmt = $db->prepare("INSERT INTO topico_passos (topico_id, titulo, descricao, imagem, ordem) VALUES (?,?,?,?,?)");
            $stmt->execute([$tid, $titulo, $desc, $imagem, $ordem]);
            $msg = 'Passo criado!';
        } else {
            $id = (int)$_POST['id'];
            $stmt = $db->prepare("UPDATE topico_passos SET topico_id=?,titulo=?,descricao=?,imagem=?,ordem=? WHERE id=?");
            $stmt->execute([$tid, $titulo, $desc, $imagem, $ordem, $id]);
            $msg = 'Passo atualizado!';
        }
        $topicoId = $tid;
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $topicoId = (int)$_POST['topico_id'];
        $db->exec("DELETE FROM topico_passos WHERE id=$id");
        $msg = 'Passo eliminado.';
    }
    $topico = $topicoId ? getTopico($topicoId) : null;
}

$passos = $topicoId ? getPassosTopico($topicoId) : [];
$todosTopicos = $db->query("SELECT t.*, c.nome as cat FROM topicos_manual t JOIN categorias_manual c ON t.categoria_id=c.id ORDER BY c.nome,t.titulo")->fetchAll();

$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM topico_passos WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
    if ($editando) $topicoId = $editando['topico_id'];
}
?>
<?php $pageTitle = 'Passos do Tópico'; include 'partials/head.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-list-ol"></i> Passos <?= $topico ? '— ' . h($topico['titulo']) : '' ?></h1>
        <div class="admin-header-actions">
            <select onchange="window.location='passos.php'+(this.value?'?topico='+this.value:'')" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px; max-width:240px;">
                <option value="">— Selecionar Tópico —</option>
                <?php foreach ($todosTopicos as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $t['id']==$topicoId?'selected':'' ?>><?= h($t['cat']) ?> › <?= h($t['titulo']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($topicoId): ?>
            <button class="btn-primary" onclick="openModal('modal-passo')" style="font-size:14px; padding:10px 20px;">
                <i class="fas fa-plus"></i> Novo Passo
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <?php if (!$topicoId): ?>
        <div class="empty-state">
            <i class="fas fa-list-ol"></i>
            <p>Selecione um tópico acima para gerir os seus passos.</p>
        </div>
        <?php else: ?>
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> <?= count($passos) ?> Passo(s)</h2>
            <?php if (empty($passos)): ?>
            <div class="empty-state" style="padding:32px;">
                <i class="fas fa-plus-circle" style="font-size:32px;"></i>
                <p>Nenhum passo ainda. Clique em "Novo Passo" para começar.</p>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Ordem</th><th>Título</th><th>Descrição</th><th>Imagem</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($passos as $p): ?>
                <tr>
                    <td><?= $p['ordem'] ?></td>
                    <td><strong><?= h($p['titulo']) ?></strong></td>
                    <td style="max-width:300px;"><?= h(mb_substr($p['descricao'] ?? '', 0, 80)) ?></td>
                    <td><?= ($p['imagem'] && file_exists($p['imagem'])) ? '<img src="../'.h($p['imagem']).'" style="max-height:40px; border-radius:4px;">' : '—' ?></td>
                    <td class="actions">
                        <a href="passos.php?topico=<?= $topicoId ?>&editar=<?= $p['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i></a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="topico_id" value="<?= $topicoId ?>">
                            <button class="btn-sm delete" type="submit"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-passo">
    <div class="modal">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar Passo' : 'Novo Passo' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-passo')">×</button>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <input type="hidden" name="topico_id" value="<?= $topicoId ?>">
            <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?= $editando['id'] ?>">
            <input type="hidden" name="imagem_atual" value="<?= h($editando['imagem']) ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Título do Passo *</label>
                <input type="text" name="titulo" required value="<?= h($editando['titulo'] ?? '') ?>" placeholder="Ex: Aceder ao sistema">
            </div>
            <div class="form-group">
                <label>Descrição / Instrução</label>
                <textarea name="descricao" rows="4" placeholder="Descreva o que o utilizador deve fazer neste passo..."><?= h($editando['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Imagem (captura de ecrã, JPG/PNG)</label>
                <?php if ($editando && $editando['imagem'] && file_exists($editando['imagem'])): ?>
                <img src="../<?= h($editando['imagem']) ?>" style="max-height:100px; display:block; margin-bottom:8px; border-radius:8px; border:1px solid var(--border);">
                <?php endif; ?>
                <input type="file" name="imagem" accept="image/*" onchange="previewImage(this,'img-prev')">
                <img id="img-prev" style="max-height:100px; margin-top:8px; border-radius:8px; display:none;">
            </div>
            <div class="form-group">
                <label>Ordem</label>
                <input type="number" name="ordem" value="<?= $editando['ordem'] ?? (count($passos)+1) ?>">
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Passo</button>
        </form>
    </div>
</div>
<?php if ($editando): ?><script>openModal('modal-passo');</script><?php endif; ?>
<?php include 'partials/foot.php'; ?>