<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar' || $acao === 'editar') {
        $cat = (int)$_POST['categoria_id'];
        $titulo = trim($_POST['titulo'] ?? '');
        $conteudo = trim($_POST['conteudo'] ?? '');
        $video = trim($_POST['video_url'] ?? '');
        $ordem = (int)($_POST['ordem'] ?? 0);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        if ($acao === 'criar') {
            $stmt = $db->prepare("INSERT INTO topicos_manual (categoria_id, titulo, conteudo, video_url, ativo, ordem) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$cat, $titulo, $conteudo, $video, $ativo, $ordem]);
            $msg = 'Tópico criado!';
        } else {
            $id = (int)$_POST['id'];
            $stmt = $db->prepare("UPDATE topicos_manual SET categoria_id=?,titulo=?,conteudo=?,video_url=?,ativo=?,ordem=? WHERE id=?");
            $stmt->execute([$cat, $titulo, $conteudo, $video, $ativo, $ordem, $id]);
            $msg = 'Tópico atualizado!';
        }
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM topico_passos WHERE topico_id=$id");
        $db->exec("DELETE FROM topicos_manual WHERE id=$id");
        $msg = 'Tópico eliminado.';
    }
}

$catFiltro = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
$sql = "SELECT t.*, c.nome as categoria_nome FROM topicos_manual t JOIN categorias_manual c ON t.categoria_id=c.id WHERE 1=1";
if ($catFiltro) $sql .= " AND t.categoria_id=$catFiltro";
$sql .= " ORDER BY c.nome, t.ordem, t.titulo";
$topicos = $db->query($sql)->fetchAll();
$categorias = getCategoriasManual(null, false);

$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM topicos_manual WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tópicos — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-file-alt"></i> Tópicos do Manual</h1>
        <div class="admin-header-actions">
            <select onchange="window.location='manual.php'+(this.value?'?cat='+this.value:'')" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:14px;">
                <option value="">Todas as Categorias</option>
                <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $catFiltro==$c['id']?'selected':'' ?>><?= h($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn-primary" onclick="openModal('modal-topico')" style="font-size:14px; padding:10px 20px;">
                <i class="fas fa-plus"></i> Novo Tópico
            </button>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Tópicos (<?= count($topicos) ?>)</h2>
            <table class="admin-table">
                <thead><tr><th>#</th><th>Título</th><th>Categoria</th><th>Visualizações</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($topicos as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><strong><?= h($t['titulo']) ?></strong></td>
                    <td><?= h($t['categoria_nome']) ?></td>
                    <td><?= $t['visualizacoes'] ?></td>
                    <td><span class="badge <?= $t['ativo'] ? 'badge-success' : 'badge-danger' ?>"><?= $t['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="actions">
                        <a href="manual.php?editar=<?= $t['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i></a>
                        <a href="passos.php?topico=<?= $t['id'] ?>" class="btn-sm success"><i class="fas fa-list-ol"></i> Passos</a>
                        <a href="../manual/topico.php?id=<?= $t['id'] ?>" target="_blank" class="btn-sm edit"><i class="fas fa-eye"></i></a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar tópico e todos os passos?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
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
<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-topico">
    <div class="modal">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar Tópico' : 'Novo Tópico' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-topico')">×</button>
        </div>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
            <div class="form-group">
                <label>Categoria *</label>
                <select name="categoria_id" required>
                    <option value="">— Selecionar —</option>
                    <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($editando['categoria_id'] ?? $catFiltro) == $c['id'] ? 'selected' : '' ?>><?= h($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Título *</label>
                <input type="text" name="titulo" required value="<?= h($editando['titulo'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Introdução / Conteúdo</label>
                <textarea name="conteudo" rows="4"><?= h($editando['conteudo'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>URL do Vídeo (YouTube Embed)</label>
                <input type="text" name="video_url" value="<?= h($editando['video_url'] ?? '') ?>" placeholder="https://www.youtube.com/embed/...">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>">
                </div>
                <div class="form-group" style="padding-top:32px;">
                    <label><input type="checkbox" name="ativo" <?= (!$editando || $editando['ativo']) ? 'checked' : '' ?>> Ativo</label>
                </div>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
        </form>
    </div>
</div>
<script src="../assets/js/main.js"></script>
<?php if ($editando): ?><script>openModal('modal-topico');</script><?php endif; ?>
</body>
</html>
