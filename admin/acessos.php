<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

// Ensure perfis_acesso table exists
$db->exec("CREATE TABLE IF NOT EXISTS perfis_acesso (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    aplicacao_id INTEGER NOT NULL,
    icone TEXT DEFAULT 'fa-user',
    titulo TEXT NOT NULL,
    descricao TEXT,
    cor TEXT DEFAULT '#0066FF',
    ordem INTEGER DEFAULT 0,
    ativo INTEGER DEFAULT 1,
    FOREIGN KEY (aplicacao_id) REFERENCES aplicacoes(id)
)");

$apps = getAplicacoes(false);
$appId = isset($_GET['app']) ? (int)$_GET['app'] : ($apps[0]['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'criar' || $acao === 'editar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $desc   = trim($_POST['descricao'] ?? '');
        $icone  = trim($_POST['icone'] ?? 'fa-user');
        $cor    = trim($_POST['cor'] ?? '#0066FF');
        $ordem  = (int)($_POST['ordem'] ?? 0);
        $ativo  = isset($_POST['ativo']) ? 1 : 0;
        $aid    = (int)($_POST['aplicacao_id'] ?? $appId);
        if ($acao === 'criar') {
            $db->prepare("INSERT INTO perfis_acesso (aplicacao_id,icone,titulo,descricao,cor,ordem,ativo) VALUES (?,?,?,?,?,?,?)")
               ->execute([$aid,$icone,$titulo,$desc,$cor,$ordem,$ativo]);
            $msg = 'Perfil criado com sucesso!';
        } else {
            $id = (int)$_POST['id'];
            $db->prepare("UPDATE perfis_acesso SET icone=?,titulo=?,descricao=?,cor=?,ordem=?,ativo=? WHERE id=?")
               ->execute([$icone,$titulo,$desc,$cor,$ordem,$ativo,$id]);
            $msg = 'Perfil atualizado!';
        }
    } elseif ($acao === 'eliminar') {
        $db->exec("DELETE FROM perfis_acesso WHERE id=" . (int)$_POST['id']);
        $msg = 'Perfil eliminado.';
    }
}

$perfis = $db->prepare("SELECT * FROM perfis_acesso WHERE aplicacao_id=? ORDER BY ordem, titulo");
$perfis->execute([$appId]);
$perfis = $perfis->fetchAll();

$editando = null;
if (isset($_GET['editar'])) {
    $s = $db->prepare("SELECT * FROM perfis_acesso WHERE id=?");
    $s->execute([(int)$_GET['editar']]);
    $editando = $s->fetch();
}

$appAtual = null;
foreach ($apps as $a) { if ($a['id'] == $appId) { $appAtual = $a; break; } }

$icones_disponiveis = [
    'fa-crown' => 'Coroa (Admin/Diretor)',
    'fa-building-columns' => 'Edifício (Secretaria)',
    'fa-chalkboard-user' => 'Professor',
    'fa-user-graduate' => 'Aluno',
    'fa-people-roof' => 'Encarregado',
    'fa-user-tie' => 'Funcionário',
    'fa-user-shield' => 'Administrador',
    'fa-user' => 'Utilizador Genérico',
    'fa-users' => 'Grupo',
    'fa-laptop' => 'Técnico',
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfis de Acesso — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <div>
            <h1><i class="fas fa-sign-in-alt"></i> Perfis de Acesso</h1>
            <?php if ($appAtual): ?>
            <p style="color:var(--text-light); font-size:14px; margin-top:4px;">
                Aplicação: <strong><?= h($appAtual['nome']) ?></strong>
                — <a href="../aceder.php?app=<?= $appId ?>" target="_blank" style="font-size:13px;"><i class="fas fa-external-link-alt"></i> Ver página de acesso</a>
            </p>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <?php if (count($apps) > 1): ?>
            <select onchange="location='acessos.php?app='+this.value" style="padding:8px 14px; border:1px solid var(--border); border-radius:8px; font-size:14px;">
                <?php foreach($apps as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $a['id']==$appId?'selected':'' ?>><?= h($a['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button class="btn-primary" onclick="openModal('modal-perfil')" style="font-size:14px; padding:10px 20px;">
                <i class="fas fa-plus"></i> Novo Perfil
            </button>
        </div>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>

        <?php if (empty($perfis)): ?>
        <div class="admin-card" style="text-align:center; padding:48px 24px;">
            <i class="fas fa-users" style="font-size:48px; color:var(--border); margin-bottom:16px; display:block;"></i>
            <h3 style="color:var(--text-light); margin-bottom:8px;">Sem perfis configurados</h3>
            <p style="color:var(--text-light); font-size:14px; margin-bottom:20px;">
                Crie perfis de utilizador para esta aplicação. Os perfis aparecem na página de acesso para ajudar os utilizadores a escolher como entrar.
            </p>
            <button class="btn-primary" onclick="openModal('modal-perfil')"><i class="fas fa-plus"></i> Criar Primeiro Perfil</button>
        </div>
        <?php else: ?>
        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Perfis configurados (<?= count($perfis) ?>)</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; margin-top:16px;">
                <?php foreach($perfis as $p): ?>
                <div style="border:2px solid <?= h($p['cor']) ?>22; border-left:4px solid <?= h($p['cor']) ?>; border-radius:12px; padding:18px; background:white;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                        <div style="width:42px;height:42px;border-radius:10px;background:<?= h($p['cor']) ?>22;display:flex;align-items:center;justify-content:center;font-size:18px;color:<?= h($p['cor']) ?>;">
                            <i class="fas <?= h($p['icone']) ?>"></i>
                        </div>
                        <div>
                            <strong style="font-size:15px;"><?= h($p['titulo']) ?></strong>
                            <div><span class="badge <?= $p['ativo']?'badge-success':'badge-danger' ?>"><?= $p['ativo']?'Ativo':'Inativo' ?></span></div>
                        </div>
                    </div>
                    <p style="font-size:13px;color:var(--text-light);margin-bottom:14px;line-height:1.4;"><?= h($p['descricao']) ?></p>
                    <div style="display:flex; gap:8px;">
                        <a href="acessos.php?app=<?= $appId ?>&editar=<?= $p['id'] ?>" class="btn-sm edit" style="flex:1;text-align:center;"><i class="fas fa-edit"></i> Editar</a>
                        <form method="post" style="flex:1;" onsubmit="return confirm('Eliminar este perfil?')">
                            <input type="hidden" name="acao" value="eliminar">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button class="btn-sm delete" type="submit" style="width:100%;"><i class="fas fa-trash"></i> Eliminar</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="admin-card" style="margin-top:20px;">
            <h2><i class="fas fa-eye"></i> Pré-visualização da página de acesso</h2>
            <p style="color:var(--text-light);font-size:14px;margin-bottom:16px;">É assim que os utilizadores vão ver a página de acesso ao <?= h($appAtual['nome'] ?? '') ?>.</p>
            <a href="../aceder.php?app=<?= $appId ?>" target="_blank" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-external-link-alt"></i> Abrir página de acesso
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-perfil">
    <div class="modal">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar Perfil' : 'Novo Perfil de Acesso' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-perfil')">×</button>
        </div>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <input type="hidden" name="aplicacao_id" value="<?= $appId ?>">
            <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>

            <div class="form-group">
                <label>Título do Perfil *</label>
                <input type="text" name="titulo" required value="<?= h($editando['titulo'] ?? '') ?>" placeholder="Ex: Diretor / Admin">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3" placeholder="Descreva o que este utilizador pode fazer..."><?= h($editando['descricao'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ícone</label>
                    <select name="icone" id="icone-sel" onchange="updateIconPreview(this.value)">
                        <?php foreach($icones_disponiveis as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($editando['icone'] ?? 'fa-user')===$val?'selected':'' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="margin-top:8px; display:flex; align-items:center; gap:8px;">
                        <div id="icon-preview" style="width:40px;height:40px;border-radius:10px;background:#e8f0fe;display:flex;align-items:center;justify-content:center;font-size:20px;color:#0066FF;">
                            <i id="icon-preview-i" class="fas <?= h($editando['icone'] ?? 'fa-user') ?>"></i>
                        </div>
                        <span style="font-size:13px;color:var(--text-light);">Pré-visualização</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Cor</label>
                    <input type="color" name="cor" value="<?= h($editando['cor'] ?? '#0066FF') ?>" style="height:42px; padding:4px; border:1px solid var(--border); border-radius:8px; width:100%; cursor:pointer;">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>" min="0">
                </div>
                <div class="form-group" style="display:flex; align-items:center; padding-top:24px;">
                    <label><input type="checkbox" name="ativo" <?= (!$editando || $editando['ativo']) ? 'checked' : '' ?>> Perfil ativo (visível)</label>
                </div>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Perfil</button>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function updateIconPreview(val) {
    document.getElementById('icon-preview-i').className = 'fas ' + val;
}
<?php if ($editando): ?>openModal('modal-perfil');<?php endif; ?>
</script>
</body>
</html>
