<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $appId  = (int)($_POST['aplicacao_id'] ?? 0);
        $nome   = trim($_POST['nome'] ?? '');
        $preco  = trim($_POST['preco'] ?? '');
        $periodo= trim($_POST['periodo'] ?? 'mês');
        $desc   = trim($_POST['descricao'] ?? '');
        $cor    = trim($_POST['cor'] ?? '#0066FF');
        $badge  = trim($_POST['badge'] ?? '');
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $ativo  = isset($_POST['ativo']) ? 1 : 0;
        $ordem  = (int)($_POST['ordem'] ?? 0);

        if ($acao === 'criar') {
            $stmt = $db->prepare("INSERT INTO planos (aplicacao_id,nome,preco,periodo,descricao,cor,badge,destaque,ativo,ordem) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$appId ?: null, $nome, $preco, $periodo, $desc, $cor, $badge, $destaque, $ativo, $ordem]);
            $novoId = $db->lastInsertId();

            // Guardar itens
            $itens = array_filter(explode("\n", trim($_POST['itens'] ?? '')));
            $itens_inc = $_POST['itens_incluido'] ?? [];
            foreach ($itens as $i => $item) {
                $item = trim($item);
                if ($item) {
                    $incluido = isset($itens_inc[$i]) ? 1 : 0;
                    $db->prepare("INSERT INTO plano_itens (plano_id,descricao,incluido,ordem) VALUES (?,?,?,?)")->execute([$novoId, $item, $incluido, $i+1]);
                }
            }
            $msg = 'Plano criado com sucesso!';
        } else {
            $id = (int)$_POST['id'];
            $stmt = $db->prepare("UPDATE planos SET aplicacao_id=?,nome=?,preco=?,periodo=?,descricao=?,cor=?,badge=?,destaque=?,ativo=?,ordem=? WHERE id=?");
            $stmt->execute([$appId ?: null, $nome, $preco, $periodo, $desc, $cor, $badge, $destaque, $ativo, $ordem, $id]);

            // Atualizar itens
            $db->exec("DELETE FROM plano_itens WHERE plano_id=$id");
            $itens = array_filter(explode("\n", trim($_POST['itens'] ?? '')));
            $itens_inc = $_POST['itens_incluido'] ?? [];
            foreach ($itens as $i => $item) {
                $item = trim($item);
                if ($item) {
                    $incluido = isset($itens_inc[$i]) ? 1 : 0;
                    $db->prepare("INSERT INTO plano_itens (plano_id,descricao,incluido,ordem) VALUES (?,?,?,?)")->execute([$id, $item, $incluido, $i+1]);
                }
            }
            $msg = 'Plano atualizado!';
        }
    } elseif ($acao === 'eliminar') {
        $id = (int)$_POST['id'];
        $db->exec("DELETE FROM plano_itens WHERE plano_id=$id");
        $db->exec("DELETE FROM planos WHERE id=$id");
        $msg = 'Plano eliminado.';
    } elseif ($acao === 'toggle_destaque') {
        $id = (int)$_POST['id'];
        $db->exec("UPDATE planos SET destaque = 1 - destaque WHERE id=$id");
        $msg = 'Atualizado.';
    }
}

$planos = getPlanos(null, false);
$apps   = getAplicacoes(false);

$editando = null;
$editandoItens = [];
if (isset($_GET['editar'])) {
    $stmt = $db->prepare("SELECT * FROM planos WHERE id=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
    if ($editando) $editandoItens = getPlanoItens($editando['id']);
}
?>
<?php $pageTitle = 'Planos & Preços'; include 'partials/head.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <h1><i class="fas fa-tags"></i> Planos & Preços</h1>
        <button class="btn-primary" onclick="openModal('modal-plano')" style="font-size:14px; padding:10px 20px;">
            <i class="fas fa-plus"></i> Novo Plano
        </button>
    </div>
    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>

        <div class="alert alert-info" style="margin-bottom:20px;">
            <i class="fas fa-info-circle"></i>
            Os planos aparecem na página inicial e na página de cada aplicação. O plano marcado como <strong>Destaque</strong> fica realçado visualmente.
        </div>

        <div class="admin-card">
            <h2><i class="fas fa-list"></i> Planos (<?= count($planos) ?>)</h2>
            <?php if (empty($planos)): ?>
            <p style="color:var(--text-light);">Nenhum plano criado ainda.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>#</th><th>Nome</th><th>Preço</th><th>Aplicação</th><th>Itens</th><th>Destaque</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($planos as $p):
                    $stmt2 = $db->prepare("SELECT COUNT(*) FROM plano_itens WHERE plano_id=?");
                    $stmt2->execute([$p['id']]);
                    $numItens = $stmt2->fetchColumn();
                ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:14px;height:14px;border-radius:3px;background:<?= h($p['cor']) ?>;flex-shrink:0;"></div>
                            <strong><?= h($p['nome']) ?></strong>
                            <?php if ($p['badge']): ?><span class="badge badge-info"><?= h($p['badge']) ?></span><?php endif; ?>
                        </div>
                    </td>
                    <td><strong><?= h($p['preco']) ?></strong> / <?= h($p['periodo']) ?></td>
                    <td><?php
                        $appNome = '';
                        foreach ($apps as $a) { if ($a['id'] == $p['aplicacao_id']) { $appNome = $a['nome']; break; } }
                        echo $appNome ? h($appNome) : '—';
                    ?></td>
                    <td><?= $numItens ?> item(s)</td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="acao" value="toggle_destaque">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-sm <?= $p['destaque'] ? 'success' : '' ?>" title="Clique para alternar destaque">
                                <i class="fas fa-star"></i> <?= $p['destaque'] ? 'Sim' : 'Não' ?>
                            </button>
                        </form>
                    </td>
                    <td><span class="badge <?= $p['ativo'] ? 'badge-success' : 'badge-danger' ?>"><?= $p['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="actions">
                        <a href="planos.php?editar=<?= $p['id'] ?>" class="btn-sm edit"><i class="fas fa-edit"></i> Editar</a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar este plano e todos os seus itens?')">
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

<!-- MODAL CRIAR/EDITAR -->
<div class="modal-overlay <?= $editando ? 'active' : '' ?>" id="modal-plano">
    <div class="modal" style="max-width:640px;">
        <div class="modal-header">
            <h3><?= $editando ? 'Editar Plano' : 'Novo Plano' ?></h3>
            <button class="modal-close" onclick="closeModal('modal-plano')">×</button>
        </div>
        <form method="post">
            <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'criar' ?>">
            <?php if ($editando): ?><input type="hidden" name="id" value="<?= $editando['id'] ?>"><?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Nome do Plano *</label>
                    <input type="text" name="nome" required value="<?= h($editando['nome'] ?? '') ?>" placeholder="Ex: Profissional">
                </div>
                <div class="form-group">
                    <label>Aplicação</label>
                    <select name="aplicacao_id">
                        <option value="">— Geral —</option>
                        <?php foreach ($apps as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($editando['aplicacao_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= h($a['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Preço *</label>
                    <input type="text" name="preco" required value="<?= h($editando['preco'] ?? '') ?>" placeholder="Ex: 55.000 Kz">
                </div>
                <div class="form-group">
                    <label>Período</label>
                    <input type="text" name="periodo" value="<?= h($editando['periodo'] ?? 'mês') ?>" placeholder="mês / ano / utilizador">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Cor do plano</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" name="cor" value="<?= h($editando['cor'] ?? '#0066FF') ?>" style="width:44px;height:36px;border:1px solid var(--border);border-radius:6px;padding:2px;cursor:pointer;">
                        <small style="color:var(--gray);">Cor do cabeçalho</small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Badge / Etiqueta</label>
                    <input type="text" name="badge" value="<?= h($editando['badge'] ?? '') ?>" placeholder="Ex: Mais Popular">
                </div>
            </div>

            <div class="form-group">
                <label>Descrição curta</label>
                <input type="text" name="descricao" value="<?= h($editando['descricao'] ?? '') ?>" placeholder="Uma frase sobre este plano">
            </div>

            <div class="form-group">
                <label>Itens incluídos/excluídos <small style="color:var(--gray);">(um por linha)</small></label>
                <textarea name="itens" rows="8" placeholder="Até 200 alunos&#10;Gestão de matrículas&#10;App para pais&#10;Suporte prioritário"><?php
                    if ($editando) {
                        echo implode("\n", array_map(fn($i) => h($i['descricao']), $editandoItens));
                    }
                ?></textarea>
                <small style="color:var(--gray); display:block; margin-top:6px;">
                    <i class="fas fa-info-circle"></i> Para definir quais estão incluídos, edite o plano após criar.
                </small>
            </div>

            <?php if ($editando && !empty($editandoItens)): ?>
            <div class="form-group">
                <label>Marcar quais estão incluídos</label>
                <div style="display:flex;flex-direction:column;gap:8px;background:var(--light-gray);border-radius:8px;padding:12px;">
                    <?php foreach ($editandoItens as $i => $item): ?>
                    <label style="display:flex;align-items:center;gap:10px;font-size:14px;font-weight:400;cursor:pointer;">
                        <input type="checkbox" name="itens_incluido[<?= $i ?>]" <?= $item['incluido'] ? 'checked' : '' ?>>
                        <?= h($item['descricao']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label>Ordem</label>
                    <input type="number" name="ordem" value="<?= $editando['ordem'] ?? 0 ?>">
                </div>
                <div class="form-group" style="display:flex;gap:16px;padding-top:28px;">
                    <label><input type="checkbox" name="destaque" <?= ($editando['destaque'] ?? 0) ? 'checked' : '' ?>> Destaque</label>
                    <label><input type="checkbox" name="ativo" <?= (!$editando || $editando['ativo']) ? 'checked' : '' ?>> Ativo</label>
                </div>
            </div>

            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Guardar Plano</button>
        </form>
    </div>
</div>

<?php if ($editando): ?><script>openModal('modal-plano');</script><?php endif; ?>
<?php include 'partials/foot.php'; ?>