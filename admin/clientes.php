<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';
$uploadDir = __DIR__ . '/../assets/uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $nome   = trim($_POST['nome_escola'] ?? '');
        $plano  = trim($_POST['plano'] ?? '');
        $cor    = trim($_POST['plano_cor'] ?? '#0066FF');
        $emoji  = trim($_POST['plano_emoji'] ?? '⭐');
        $loc    = trim($_POST['localizacao'] ?? '');
        $ativo  = isset($_POST['ativo']) ? 1 : 0;
        $ordem  = (int)($_POST['ordem'] ?? 0);

        $logo = trim($_POST['logo_atual'] ?? '');
        if (!empty($_FILES['logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp','svg'])) {
                $fname = 'cliente_' . time() . '_' . rand(100,999) . '.' . $ext;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $fname)) {
                    $logo = 'assets/uploads/' . $fname;
                }
            }
        }

        if ($acao === 'criar') {
            $db->prepare("INSERT INTO clientes_destaque (nome_escola,logo,plano,plano_cor,plano_emoji,localizacao,ativo,ordem) VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$nome, $logo, $plano, $cor, $emoji, $loc, $ativo, $ordem]);
            $msg = 'Cliente adicionado com sucesso!';
        } else {
            $id = (int)$_POST['id'];
            $db->prepare("UPDATE clientes_destaque SET nome_escola=?,logo=?,plano=?,plano_cor=?,plano_emoji=?,localizacao=?,ativo=?,ordem=? WHERE id=?")
               ->execute([$nome, $logo, $plano, $cor, $emoji, $loc, $ativo, $ordem, $id]);
            $msg = 'Cliente atualizado!';
        }
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM clientes_destaque WHERE id=$id");
        $msg = 'Removido.';
    } elseif ($acao === 'toggle') {
        $id = (int)$_POST['id'];
        $db->exec("UPDATE clientes_destaque SET ativo = 1 - ativo WHERE id=$id");
    }
}

$clientes = getClientesDestaque(false);
$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM clientes_destaque WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}

$planosDisponiveis = [
    ['nome'=>'Premium', 'emoji'=>'⭐', 'cor'=>'#0066FF'],
    ['nome'=>'Golden',  'emoji'=>'🥇', 'cor'=>'#F5A623'],
    ['nome'=>'Ruby',    'emoji'=>'💎', 'cor'=>'#C0392B'],
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes em Destaque — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-school"></i> Clientes em Destaque</h1>
        <button class="btn-primary" onclick="openModal('modal-cliente')" style="font-size:14px; padding:10px 20px;">
            <i class="fas fa-plus"></i> Novo Cliente
        </button>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>

        <div class="alert alert-info" style="margin-bottom:20px;">
            <i class="fas fa-info-circle"></i>
            Estes clientes aparecem na <strong>notificação de prova social</strong> que passa automaticamente no website. Adicione o logótipo da escola e o plano que adquiriram para aumentar a confiança dos visitantes.
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Clientes (<?= count($clientes) ?>)</h2>
            <?php if (empty($clientes)): ?>
            <p style="color:var(--text-light);">Nenhum cliente adicionado ainda.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Logo</th><th>Escola</th><th>Plano</th><th>Localização</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td>
                        <?php if ($c['logo'] && file_exists(__DIR__ . '/../' . $c['logo'])): ?>
                        <img src="../<?= h($c['logo']) ?>" alt="" style="width:40px;height:40px;object-fit:contain;border-radius:8px;border:1px solid var(--border);">
                        <?php else: ?>
                        <div style="width:40px;height:40px;border-radius:8px;background:<?= h($c['plano_cor']) ?>;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;">
                            <?= mb_strtoupper(mb_substr($c['nome_escola'], 0, 2)) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= h($c['nome_escola']) ?></strong></td>
                    <td>
                        <span style="background:<?= h($c['plano_cor']) ?>;color:white;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                            <?= h($c['plano_emoji']) ?> <?= h($c['plano']) ?>
                        </span>
                    </td>
                    <td><?= $c['localizacao'] ? h($c['localizacao']) : '—' ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="acao" value="toggle">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn-sm <?= $c['ativo'] ? 'success' : '' ?>">
                                <?= $c['ativo'] ? 'Ativo' : 'Inativo' ?>
                            </button>
                        </form>
                    </td>
                    <td class="actions">
                        <a href="clientes.php?editar=<?= $c['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i> Editar</a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar este cliente?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
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

<!-- MODAL -->
<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-cliente">
    <div class="modal" style="max-width:560px;">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar Cliente' : 'Novo Cliente' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-cliente')">×</button>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>
            <input type="hidden" name="logo_atual" value="<?= h($editando['logo'] ?? '') ?>">

            <div class="form-group">
                <label>Nome da Escola *</label>
                <input type="text" name="nome_escola" required value="<?= h($editando['nome_escola'] ?? '') ?>" placeholder="Ex: Escola Primária Nações Unidas">
            </div>

            <div class="form-group">
                <label>Logótipo da Escola</label>
                <?php if (!empty($editando['logo']) && file_exists(__DIR__ . '/../' . $editando['logo'])): ?>
                <div style="margin-bottom:10px;">
                    <img src="../<?= h($editando['logo']) ?>" alt="" style="height:60px;border-radius:8px;border:1px solid var(--border);">
                </div>
                <?php endif; ?>
                <input type="file" name="logo" accept="image/*">
                <small style="color:var(--gray);">PNG, JPG, SVG recomendado. Deixar vazio para usar iniciais.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Plano Adquirido *</label>
                    <select name="plano" id="plano-sel" onchange="updatePlanoFields(this)">
                        <?php foreach ($planosDisponiveis as $p): ?>
                        <option value="<?= h($p['nome']) ?>"
                            data-cor="<?= h($p['cor']) ?>"
                            data-emoji="<?= h($p['emoji']) ?>"
                            <?= ($editando['plano'] ?? '') === $p['nome'] ? 'selected' : '' ?>>
                            <?= h($p['emoji']) ?> <?= h($p['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Localização</label>
                    <input type="text" name="localizacao" value="<?= h($editando['localizacao'] ?? '') ?>" placeholder="Ex: Luanda, Angola">
                </div>
            </div>

            <input type="hidden" name="plano_cor" id="plano_cor" value="<?= h($editando['plano_cor'] ?? '#0066FF') ?>">
            <input type="hidden" name="plano_emoji" id="plano_emoji" value="<?= h($editando['plano_emoji'] ?? '⭐') ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>">
                </div>
                <div class="form-group" style="padding-top:28px;">
                    <label><input type="checkbox" name="ativo" <?= (!$editando || $editando['ativo']) ? 'checked' : '' ?>> Mostrar no website</label>
                </div>
            </div>

            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar</button>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function updatePlanoFields(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('plano_cor').value = opt.dataset.cor || '#0066FF';
    document.getElementById('plano_emoji').value = opt.dataset.emoji || '⭐';
}
</script>
<?php if ($editando): ?><script>openModal('modal-cliente');</script><?php endif; ?>
</body>
</html>
