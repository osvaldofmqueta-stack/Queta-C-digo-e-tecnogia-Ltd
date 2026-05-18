<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resposta'])) {
    $sessaoId = (int)$_POST['sessao_id'];
    $resposta = trim($_POST['resposta']);
    if ($sessaoId && $resposta) {
        $db->prepare("INSERT INTO chat_mensagens (sessao_id, de, mensagem, lido) VALUES (?,?,?,?)")
           ->execute([$sessaoId, 'admin', $resposta, 1]);
        $msg = 'Resposta enviada!';
    }
}

$sessaoAtiva = isset($_GET['sessao']) ? (int)$_GET['sessao'] : 0;

$sessoes = $db->query("
    SELECT cs.*, 
           (SELECT COUNT(*) FROM chat_mensagens WHERE sessao_id=cs.id AND lido=0 AND de='visitante') as nao_lidas,
           (SELECT mensagem FROM chat_mensagens WHERE sessao_id=cs.id ORDER BY id DESC LIMIT 1) as ultima_msg
    FROM chat_sessoes cs 
    ORDER BY cs.ultima_atividade DESC
")->fetchAll();

$mensagens = [];
$sessaoInfo = null;
if ($sessaoAtiva) {
    $s = $db->prepare("SELECT * FROM chat_sessoes WHERE id=?");
    $s->execute([$sessaoAtiva]);
    $sessaoInfo = $s->fetch();
    $sm = $db->prepare("SELECT * FROM chat_mensagens WHERE sessao_id=? ORDER BY id ASC");
    $sm->execute([$sessaoAtiva]);
    $mensagens = $sm->fetchAll();
    $db->prepare("UPDATE chat_mensagens SET lido=1 WHERE sessao_id=? AND de='visitante'")
       ->execute([$sessaoAtiva]);
}

$totalSessoes = count($sessoes);
$totalNaoLidas = (int)$db->query("SELECT COUNT(*) FROM chat_mensagens WHERE lido=0 AND de='visitante'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat de Suporte — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .chat-admin-layout { display: grid; grid-template-columns: 300px 1fr; gap: 20px; height: calc(100vh - 140px); }
        .chat-list { background: white; border: 1px solid var(--border); border-radius: var(--radius); overflow-y: auto; }
        .chat-list-item { padding: 14px 16px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.2s; text-decoration: none; display: block; color: var(--text); }
        .chat-list-item:hover, .chat-list-item.active { background: rgba(0,102,255,0.06); }
        .chat-list-item .nome { font-weight: 600; font-size: 14px; margin-bottom: 3px; display: flex; justify-content: space-between; align-items: center; }
        .chat-list-item .preview { font-size: 12px; color: var(--text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .chat-list-item .hora { font-size: 11px; color: var(--gray); }
        .badge-nl { background: var(--primary); color: white; border-radius: 50px; padding: 2px 7px; font-size: 11px; font-weight: 700; }
        .chat-panel { background: white; border: 1px solid var(--border); border-radius: var(--radius); display: flex; flex-direction: column; overflow: hidden; }
        .chat-panel-header { padding: 16px 20px; border-bottom: 1px solid var(--border); background: var(--light-gray); }
        .chat-panel-header h3 { font-size: 16px; font-weight: 700; }
        .chat-msgs { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
        .msg-bubble { max-width: 75%; padding: 10px 14px; border-radius: 12px; font-size: 14px; line-height: 1.4; }
        .msg-visitante { background: var(--light-gray); align-self: flex-start; border-bottom-left-radius: 4px; }
        .msg-admin { background: var(--primary); color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
        .msg-sistema { background: #f0f7ff; border: 1px solid #cce0ff; color: #0055cc; align-self: center; font-size: 12px; border-radius: 50px; padding: 6px 14px; }
        .msg-meta { font-size: 11px; opacity: 0.6; margin-top: 4px; }
        .chat-input-area { padding: 12px 16px; border-top: 1px solid var(--border); display: flex; gap: 8px; }
        .chat-input-area textarea { flex: 1; border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; font-size: 14px; resize: none; font-family: inherit; }
        .chat-input-area textarea:focus { outline: none; border-color: var(--primary); }
        .empty-chat { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-light); gap: 12px; }
        .empty-chat i { font-size: 48px; color: var(--border); }
    </style>
</head>
<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header">
        <div>
            <h1><i class="fas fa-comments"></i> Chat de Suporte
                <?php if ($totalNaoLidas > 0): ?>
                <span class="badge badge-danger" style="font-size:14px; margin-left:8px;"><?= $totalNaoLidas ?> não lida(s)</span>
                <?php endif; ?>
            </h1>
            <p style="color:var(--text-light);font-size:14px;margin-top:4px;"><?= $totalSessoes ?> conversa(s) no total</p>
        </div>
    </div>
    <div class="admin-content" style="padding:0;">
        <?php if ($msg): ?><div class="alert alert-success" style="margin:16px 24px 0;"><i class="fas fa-check-circle"></i> <?= h($msg) ?></div><?php endif; ?>
        <div class="chat-admin-layout" style="padding:20px; padding-top:12px;">
            <div class="chat-list">
                <?php if (empty($sessoes)): ?>
                <div style="padding:24px; text-align:center; color:var(--text-light); font-size:14px;">
                    <i class="fas fa-comments" style="font-size:32px; color:var(--border); display:block; margin-bottom:10px;"></i>
                    Ainda sem conversas
                </div>
                <?php endif; ?>
                <?php foreach($sessoes as $s): ?>
                <a href="chat.php?sessao=<?= $s['id'] ?>" class="chat-list-item <?= $sessaoAtiva==$s['id']?'active':'' ?>">
                    <div class="nome">
                        <?= h($s['visitante_nome'] ?? 'Visitante') ?>
                        <?php if ($s['nao_lidas'] > 0): ?>
                        <span class="badge-nl"><?= $s['nao_lidas'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="preview"><?= h(mb_substr($s['ultima_msg'] ?? '', 0, 55)) ?></div>
                    <div class="hora"><?= date('d/m H:i', strtotime($s['ultima_atividade'])) ?></div>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="chat-panel">
                <?php if ($sessaoAtiva && $sessaoInfo): ?>
                <div class="chat-panel-header">
                    <h3><i class="fas fa-user-circle"></i> <?= h($sessaoInfo['visitante_nome'] ?? 'Visitante') ?></h3>
                    <div style="font-size:12px;color:var(--text-light);">Iniciado em <?= date('d/m/Y H:i', strtotime($sessaoInfo['criado_em'])) ?></div>
                </div>
                <div class="chat-msgs" id="chat-msgs">
                    <?php foreach($mensagens as $m): ?>
                    <div>
                        <div class="msg-bubble msg-<?= h($m['de']) ?>">
                            <?= nl2br(h($m['mensagem'])) ?>
                        </div>
                        <div class="msg-meta" style="text-align:<?= $m['de']==='admin'?'right':'left' ?>">
                            <?= $m['de']==='admin'?'Você':'Visitante' ?> · <?= date('H:i', strtotime($m['criado_em'])) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="chat-input-area">
                    <form method="post" style="display:contents;" onsubmit="return submitResposta(event)">
                        <input type="hidden" name="sessao_id" value="<?= $sessaoAtiva ?>">
                        <textarea name="resposta" id="resposta-txt" rows="2" placeholder="Escreva a sua resposta..." required></textarea>
                        <button type="submit" class="btn-primary" style="padding:10px 18px; align-self:flex-end;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <p>Selecione uma conversa para responder</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
const chatMsgs = document.getElementById('chat-msgs');
if (chatMsgs) chatMsgs.scrollTop = chatMsgs.scrollHeight;

function submitResposta(e) {
    e.preventDefault();
    const form = e.target.closest('form');
    const fd = new FormData(form);
    fetch('chat.php', { method: 'POST', body: fd })
        .then(() => location.reload());
    return false;
}

// Auto-refresh every 8s to show new messages
<?php if ($sessaoAtiva): ?>
setInterval(() => location.reload(), 8000);
<?php endif; ?>
</script>
</body>
</html>
