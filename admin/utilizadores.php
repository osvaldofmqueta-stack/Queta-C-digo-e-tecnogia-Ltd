<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

// Toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_ativo'])) {
    $uid = (int)$_POST['uid'];
    $db->prepare("UPDATE clientes SET ativo = CASE WHEN ativo=1 THEN 0 ELSE 1 END WHERE id=?")->execute([$uid]);
    $msg = 'Utilizador actualizado.';
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $uid = (int)$_POST['uid'];
    $db->prepare("DELETE FROM carrinho WHERE cliente_id=?")->execute([$uid]);
    $db->prepare("DELETE FROM clientes WHERE id=?")->execute([$uid]);
    $msg = 'Utilizador eliminado.';
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT c.*, 
    (SELECT COUNT(*) FROM carrinho WHERE cliente_id=c.id) as itens_carrinho,
    (SELECT COUNT(*) FROM encomendas WHERE cliente_id=c.id) as total_encomendas
    FROM clientes c WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (c.nome LIKE ? OR c.email LIKE ? OR c.escola LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY c.criado_em DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$utilizadores = $stmt->fetchAll();

$totalAtivos   = (int)$db->query("SELECT COUNT(*) FROM clientes WHERE ativo=1")->fetchColumn();
$totalInativos = (int)$db->query("SELECT COUNT(*) FROM clientes WHERE ativo=0")->fetchColumn();
$totalHoje     = (int)$db->query("SELECT COUNT(*) FROM clientes WHERE DATE(criado_em)=DATE('now')")->fetchColumn();

// View cart detail
$verCarrinho = null;
$carrinhoItems = [];
if (isset($_GET['carrinho'])) {
    $uid = (int)$_GET['carrinho'];
    $vc = $db->prepare("SELECT * FROM clientes WHERE id=?");
    $vc->execute([$uid]);
    $verCarrinho = $vc->fetch();
    $ci = $db->prepare("SELECT ca.id, p.nome, p.preco, p.periodo, p.cor, ca.adicionado_em FROM carrinho ca JOIN planos p ON ca.plano_id=p.id WHERE ca.cliente_id=?");
    $ci->execute([$uid]);
    $carrinhoItems = $ci->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilizadores Registados — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-mini { background: white; border: 1px solid var(--border); border-radius: var(--radius); padding: 18px 22px; }
        .stat-mini .val { font-size: 28px; font-weight: 800; color: var(--primary); }
        .stat-mini .lbl { font-size: 12px; color: var(--text-light); margin-top: 2px; }
        .user-table { width: 100%; border-collapse: collapse; background: white; border-radius: var(--radius); overflow: hidden; border: 1px solid var(--border); }
        .user-table th { background: var(--light-gray); padding: 11px 14px; text-align: left; font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: .5px; }
        .user-table td { padding: 12px 14px; font-size: 13.5px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .user-table tr:last-child td { border-bottom: none; }
        .user-table tr:hover td { background: rgba(0,102,255,0.03); }
        .badge-status { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; }
        .badge-ativo { background: #e6f9ee; color: #1a7a3a; }
        .badge-inativo { background: #fff0f0; color: #cc3333; }
        .avatar-cell { width: 34px; height: 34px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-bar input { flex: 1; border: 1.5px solid var(--border); border-radius: 8px; padding: 10px 14px; font-size: 14px; font-family: inherit; }
        .search-bar input:focus { outline: none; border-color: var(--primary); }
        .btn-sm { padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; font-family: inherit; display: inline-flex; align-items: center; gap: 5px; transition: var(--transition); text-decoration: none; }
        .btn-danger-sm { background: #fff0f0; color: #cc3333; border: 1px solid #ffc0c0; }
        .btn-danger-sm:hover { background: #ffd5d5; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .modal-box { background: white; border-radius: var(--radius); padding: 28px; max-width: 480px; width: 100%; max-height: 80vh; overflow-y: auto; }
        .cart-item-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }
        .cart-item-row:last-child { border-bottom: none; }
        .cart-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
    </style>
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <div>
            <h1><i class="fas fa-users"></i> Utilizadores Registados</h1>
            <p style="color:var(--text-light);font-size:14px;margin-top:4px;"><?= count($utilizadores) ?> utilizador(es) encontrado(s)</p>
        </div>
        <?php if ($msg): ?>
        <div class="alert alert-success" style="margin:0;"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div>
        <?php endif; ?>
    </div>
    <div class="admin-content">
        <div class="stats-row">
            <div class="stat-mini">
                <div class="val"><?= $totalAtivos ?></div>
                <div class="lbl"><i class="fas fa-circle" style="color:#00e676;font-size:9px;"></i> Utilizadores activos</div>
            </div>
            <div class="stat-mini">
                <div class="val" style="color:var(--gray);"><?= $totalInativos ?></div>
                <div class="lbl"><i class="fas fa-circle" style="color:#ccc;font-size:9px;"></i> Inactivos / suspensos</div>
            </div>
            <div class="stat-mini">
                <div class="val" style="color:var(--secondary);"><?= $totalHoje ?></div>
                <div class="lbl"><i class="fas fa-star" style="font-size:11px;color:var(--secondary);"></i> Novos hoje</div>
            </div>
        </div>

        <form method="get" class="search-bar">
            <input type="text" name="q" value="<?= h($search) ?>" placeholder="Pesquisar por nome, email ou escola...">
            <button type="submit" class="btn-primary" style="padding:10px 18px;"><i class="fas fa-search"></i></button>
            <?php if ($search): ?><a href="utilizadores.php" class="btn-primary" style="padding:10px 16px;background:var(--gray);border-color:var(--gray);"><i class="fas fa-times"></i></a><?php endif; ?>
        </form>

        <?php if (empty($utilizadores)): ?>
        <div style="text-align:center;padding:48px;color:var(--text-light);">
            <i class="fas fa-users" style="font-size:44px;color:var(--border);display:block;margin-bottom:12px;"></i>
            <?= $search ? 'Nenhum utilizador encontrado para "'  . h($search) . '".' : 'Ainda não há utilizadores registados.' ?>
        </div>
        <?php else: ?>
        <table class="user-table">
            <thead>
                <tr>
                    <th style="width:36px;"></th>
                    <th>Utilizador</th>
                    <th>Escola</th>
                    <th>Telefone</th>
                    <th>Carrinho</th>
                    <th>Estado</th>
                    <th>Registado em</th>
                    <th>Acções</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($utilizadores as $u): ?>
                <tr>
                    <td><div class="avatar-cell"><?= mb_strtoupper(mb_substr($u['nome'], 0, 1)) ?></div></td>
                    <td>
                        <strong style="display:block;"><?= h($u['nome']) ?></strong>
                        <span style="font-size:12px;color:var(--text-light);"><?= h($u['email']) ?></span>
                    </td>
                    <td><?= $u['escola'] ? h($u['escola']) : '<span style="color:var(--gray);">—</span>' ?></td>
                    <td><?= $u['telefone'] ? h($u['telefone']) : '<span style="color:var(--gray);">—</span>' ?></td>
                    <td>
                        <?php if ($u['itens_carrinho'] > 0): ?>
                        <a href="utilizadores.php?carrinho=<?= $u['id'] ?>" class="btn-sm" style="background:#f0f7ff;color:var(--primary);border:1px solid #cce0ff;">
                            <i class="fas fa-shopping-cart"></i> <?= $u['itens_carrinho'] ?> plano(s)
                        </a>
                        <?php else: ?>
                        <span style="color:var(--gray);font-size:12px;">Vazio</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                            <button type="submit" name="toggle_ativo" class="badge-status <?= $u['ativo'] ? 'badge-ativo' : 'badge-inativo' ?>" style="border:none;cursor:pointer;font-family:inherit;" title="Clique para activar/desactivar">
                                <?= $u['ativo'] ? 'Activo' : 'Inactivo' ?>
                            </button>
                        </form>
                    </td>
                    <td style="font-size:12px;color:var(--text-light);"><?= date('d/m/Y H:i', strtotime($u['criado_em'])) ?></td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Eliminar o utilizador <?= h(addslashes($u['nome'])) ?>? Esta acção não pode ser desfeita.');">
                            <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                            <button type="submit" name="eliminar" class="btn-sm btn-danger-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php if ($verCarrinho): ?>
<div class="modal-overlay" onclick="if(event.target===this)window.location='utilizadores.php'">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
            <h2 style="font-size:18px;font-weight:700;"><i class="fas fa-shopping-cart" style="color:var(--primary);"></i> Carrinho de <?= h($verCarrinho['nome']) ?></h2>
            <a href="utilizadores.php" style="color:var(--gray);font-size:20px;text-decoration:none;">×</a>
        </div>
        <?php if (empty($carrinhoItems)): ?>
        <p style="color:var(--text-light);text-align:center;padding:20px;">Carrinho vazio.</p>
        <?php else: ?>
        <?php foreach($carrinhoItems as $ci): ?>
        <div class="cart-item-row">
            <div class="cart-dot" style="background:<?= h($ci['cor']) ?>;"></div>
            <div style="flex:1;">
                <strong><?= h($ci['nome']) ?></strong>
                <div style="font-size:12px;color:var(--text-light);"><?= h($ci['preco']) ?><?= $ci['periodo'] ? ' / ' . h($ci['periodo']) : '' ?></div>
            </div>
            <div style="font-size:11px;color:var(--gray);"><?= date('d/m H:i', strtotime($ci['adicionado_em'])) ?></div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);">
            <a href="https://api.whatsapp.com/send?phone=244926219731&text=<?= urlencode('Olá! O utilizador ' . $verCarrinho['nome'] . ' (' . $verCarrinho['email'] . ') tem ' . count($carrinhoItems) . ' plano(s) no carrinho.') ?>" target="_blank" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:13px;">
                <i class="fab fa-whatsapp"></i> Contactar via WhatsApp
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script src="../assets/js/main.js"></script>
</body>
</html>
