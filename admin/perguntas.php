<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'responder') {
        $id = (int)$_POST['id'];
        $resposta = trim($_POST['resposta'] ?? '');
        $publicado = isset($_POST['publicado']) ? 1 : 0;
        $stmt = $db->prepare("UPDATE perguntas SET resposta=?, respondido=1, publicado=?, respondido_em=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([$resposta, $publicado, $id]);
        $msg = 'Resposta guardada com sucesso!';
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM perguntas WHERE id=$id");
        $msg = 'Pergunta eliminada.';
    } elseif ($acao === 'toggle_publicado') {
        $id = (int)$_POST['id'];
        $db->exec("UPDATE perguntas SET publicado = 1 - publicado WHERE id=$id");
        $msg = 'Estado alterado.';
    }
}

$filtro = $_GET['filtro'] ?? 'todas';
$sql = "SELECT p.*, t.titulo as topico_titulo FROM perguntas p LEFT JOIN topicos_manual t ON p.topico_id=t.id";
if ($filtro === 'pendentes') $sql .= " WHERE p.respondido=0";
elseif ($filtro === 'respondidas') $sql .= " WHERE p.respondido=1";
$sql .= " ORDER BY p.criado_em DESC";
$perguntas = $db->query($sql)->fetchAll();

$responder = null;
if (isset($_GET['responder'])) {
    $stmt = $db->prepare("SELECT * FROM perguntas WHERE id=?");
    $stmt->execute([(int)$_GET['responder']]);
    $responder = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perguntas — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-comments"></i> Perguntas & Respostas</h1>
        <div class="admin-header-actions">
            <a href="?filtro=todas" class="btn-sm <?= $filtro=='todas'?'edit':'' ?>">Todas</a>
            <a href="?filtro=pendentes" class="btn-sm <?= $filtro=='pendentes'?'edit':'' ?>">Pendentes</a>
            <a href="?filtro=respondidas" class="btn-sm <?= $filtro=='respondidas'?'edit':'' ?>">Respondidas</a>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>

        <?php if ($responder): ?>
        <div class="admin-card" style="border:2px solid var(--primary);">
            <h2><i class="fas fa-reply"></i> Responder Pergunta</h2>
            <div style="background:var(--light-gray); border-radius:var(--radius-sm); padding:16px; margin-bottom:20px;">
                <strong><?= h($responder['pergunta']) ?></strong>
                <p style="font-size:13px; color:var(--gray); margin-top:6px;"><i class="fas fa-user"></i> <?= h($responder['nome']) ?> · <?= h($responder['email']) ?></p>
            </div>
            <form method="post">
                <input type="hidden" name="acao" value="responder">
                <input type="hidden" name="id" value="<?= $responder['id'] ?>">
                <div class="form-group">
                    <label>Resposta *</label>
                    <textarea name="resposta" required rows="5" placeholder="Escreva aqui a sua resposta..."><?= h($responder['resposta'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="publicado" checked> Publicar (visível no site)</label>
                </div>
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Resposta</button>
                    <a href="perguntas.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <h2><i class="fas fa-list"></i> <?= count($perguntas) ?> Pergunta(s)</h2>
            <?php if (empty($perguntas)): ?>
            <p style="color:var(--text-light);">Nenhuma pergunta encontrada.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Pergunta</th><th>Utilizador</th><th>Tópico</th><th>Estado</th><th>Data</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($perguntas as $p): ?>
                <tr>
                    <td style="max-width:280px;"><?= h(mb_substr($p['pergunta'], 0, 80)) ?>...</td>
                    <td><?= h($p['nome']) ?><?php if($p['email']): ?><br><small style="color:var(--gray);"><?= h($p['email']) ?></small><?php endif; ?></td>
                    <td><?= $p['topico_titulo'] ? h($p['topico_titulo']) : '<span style="color:var(--gray)">—</span>' ?></td>
                    <td>
                        <?php if ($p['respondido']): ?>
                        <span class="badge badge-success">Respondida</span>
                        <?php else: ?>
                        <span class="badge badge-danger">Pendente</span>
                        <?php endif; ?>
                        <?php if ($p['publicado']): ?>
                        <span class="badge badge-info" style="margin-left:4px;">Público</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;"><?= date('d/m/Y', strtotime($p['criado_em'])) ?></td>
                    <td class="actions">
                        <a href="?responder=<?= $p['id'] ?>" class="btn-sm success"><i class="fas fa-reply"></i></a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button class="btn-sm delete" type="submit"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
