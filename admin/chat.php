<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isAdminLoggedIn()) { header('Location: index.php'); exit; }

// AJAX endpoints
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $db = getDB();

    if ($_GET['ajax'] === 'sessoes') {
        $sessoes = $db->query("
            SELECT cs.id, cs.visitante_nome, cs.ultima_atividade,
                   (SELECT COUNT(*) FROM chat_mensagens WHERE sessao_id=cs.id AND lido=0 AND de='visitante') as nao_lidas,
                   (SELECT mensagem FROM chat_mensagens WHERE sessao_id=cs.id ORDER BY id DESC LIMIT 1) as ultima_msg
            FROM chat_sessoes cs ORDER BY cs.ultima_atividade DESC
        ")->fetchAll();
        $totalNaoLidas = (int)$db->query("SELECT COUNT(*) FROM chat_mensagens WHERE lido=0 AND de='visitante'")->fetchColumn();
        echo json_encode(['sessoes' => $sessoes, 'total_nao_lidas' => $totalNaoLidas]);
        exit;
    }

    if ($_GET['ajax'] === 'msgs' && isset($_GET['sessao_id'])) {
        $sid = (int)$_GET['sessao_id'];
        $ultimoId = (int)($_GET['ultimo_id'] ?? 0);
        $stmt = $db->prepare("SELECT id, de, mensagem, criado_em FROM chat_mensagens WHERE sessao_id=? AND id>? ORDER BY id ASC");
        $stmt->execute([$sid, $ultimoId]);
        $msgs = $stmt->fetchAll();
        $db->prepare("UPDATE chat_mensagens SET lido=1 WHERE sessao_id=? AND de='visitante'")->execute([$sid]);
        echo json_encode(['msgs' => $msgs]);
        exit;
    }

    if ($_GET['ajax'] === 'responder' && $_SERVER['REQUEST_METHOD']==='POST') {
        $sid = (int)($_POST['sessao_id'] ?? 0);
        $msg = trim($_POST['resposta'] ?? '');
        if ($sid && $msg) {
            $db->prepare("INSERT INTO chat_mensagens (sessao_id, de, mensagem, lido) VALUES (?,?,?,1)")
               ->execute([$sid, 'admin', $msg]);
            $db->prepare("UPDATE chat_sessoes SET ultima_atividade=CURRENT_TIMESTAMP WHERE id=?")->execute([$sid]);
            echo json_encode(['ok' => true, 'id' => $db->lastInsertId()]);
        } else {
            echo json_encode(['erro' => 'Dados inválidos']);
        }
        exit;
    }

    echo json_encode(['erro' => 'Ação inválida']);
    exit;
}

$db = getDB();
$sessaoAtiva = isset($_GET['sessao']) ? (int)$_GET['sessao'] : 0;

$sessoes = $db->query("
    SELECT cs.*, 
           (SELECT COUNT(*) FROM chat_mensagens WHERE sessao_id=cs.id AND lido=0 AND de='visitante') as nao_lidas,
           (SELECT mensagem FROM chat_mensagens WHERE sessao_id=cs.id ORDER BY id DESC LIMIT 1) as ultima_msg
    FROM chat_sessoes cs ORDER BY cs.ultima_atividade DESC
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
    $db->prepare("UPDATE chat_mensagens SET lido=1 WHERE sessao_id=? AND de='visitante'")->execute([$sessaoAtiva]);
}

$totalNaoLidas = (int)$db->query("SELECT COUNT(*) FROM chat_mensagens WHERE lido=0 AND de='visitante'")->fetchColumn();
$totalSessoes = count($sessoes);
$ultimoMsgId = $mensagens ? max(array_column($mensagens,'id')) : 0;
?>
<?php $pageTitle = 'Chat de Suporte'; include 'partials/head.php'; ?>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
    <div class="admin-header" style="padding: 16px 20px 0;">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:20px;"><i class="fas fa-comments"></i> Chat de Suporte
                    <span id="badge-total" class="badge badge-danger" style="font-size:13px;margin-left:8px;<?= $totalNaoLidas ? '' : 'display:none' ?>"><?= $totalNaoLidas ?> não lida(s)</span>
                </h1>
                <p style="color:var(--text-light);font-size:13px;margin-top:3px;"><?= $totalSessoes ?> conversa(s) · Responde abaixo em tempo real</p>
            </div>
            <button class="sound-btn on" id="sound-toggle" onclick="toggleSound()" title="Ativar/desativar som">
                <i class="fas fa-volume-up"></i> <span id="sound-label">Som ativo</span>
            </button>
        </div>
    </div>

    <div class="chat-admin-layout">
        <!-- Lista de conversas -->
        <div class="chat-list-panel">
            <div class="chat-list-header">
                <h3><i class="fas fa-inbox"></i> Conversas</h3>
                <p id="lista-status">A actualizar...</p>
            </div>
            <div class="chat-list-scroll" id="lista-sessoes">
                <?php foreach($sessoes as $s): ?>
                <a href="chat.php?sessao=<?= $s['id'] ?>" class="chat-list-item <?= $sessaoAtiva==$s['id']?'active':'' ?>" data-sessao="<?= $s['id'] ?>">
                    <div class="cli-nome">
                        <?= htmlspecialchars($s['visitante_nome'] ?? 'Visitante') ?>
                        <?php if ($s['nao_lidas'] > 0): ?>
                        <span class="badge-nl"><?= $s['nao_lidas'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="cli-preview"><?= htmlspecialchars(mb_substr($s['ultima_msg'] ?? '—', 0, 50)) ?></div>
                    <div class="cli-hora"><?= date('d/m H:i', strtotime($s['ultima_atividade'])) ?></div>
                </a>
                <?php endforeach; ?>
                <?php if (empty($sessoes)): ?>
                <div style="padding:24px;text-align:center;color:var(--text-light);font-size:13px;">
                    <i class="fas fa-comments" style="font-size:28px;color:#dde;display:block;margin-bottom:8px;"></i>
                    Ainda sem conversas
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Painel principal -->
        <div class="chat-main-panel">
            <?php if ($sessaoAtiva && $sessaoInfo): ?>
            <div class="chat-main-header">
                <div class="avatar"><?= mb_strtoupper(mb_substr($sessaoInfo['visitante_nome'] ?? 'V', 0, 1)) ?></div>
                <div>
                    <h3><?= htmlspecialchars($sessaoInfo['visitante_nome'] ?? 'Visitante') ?></h3>
                    <p>Iniciou em <?= date('d/m/Y \à\s H:i', strtotime($sessaoInfo['criado_em'])) ?></p>
                </div>
                <div style="margin-left:auto;font-size:12px;color:var(--text-light);">
                    <i class="fas fa-circle" style="color:#00e676;font-size:9px;"></i> Online
                </div>
            </div>
            <div class="chat-msgs-area" id="chat-msgs-area">
                <?php foreach($mensagens as $m): ?>
                <div class="msg-row from-<?= htmlspecialchars($m['de']) ?>" data-id="<?= $m['id'] ?>">
                    <div class="msg-bubble"><?= nl2br(htmlspecialchars($m['mensagem'])) ?></div>
                    <div class="msg-meta"><?= $m['de']==='admin'?'Você':'Visitante' ?> · <?= date('H:i', strtotime($m['criado_em'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="chat-input-area">
                <textarea id="resposta-txt" placeholder="Escreva a resposta aqui... (Enter = nova linha, Ctrl+Enter = enviar)" rows="2"
                    onkeydown="if(event.ctrlKey&&event.key==='Enter'){enviarResposta();return false;}"></textarea>
                <button class="btn-send" id="btn-send" onclick="enviarResposta()" title="Enviar (Ctrl+Enter)">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <strong>Selecione uma conversa</strong>
                <p style="font-size:13px;text-align:center;max-width:200px;">Clique numa conversa à esquerda para responder ao visitante.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// ---- Sound System ----
let _soundOn = localStorage.getItem('admin_sound') !== 'off';
const AudioCtx = window.AudioContext || window.webkitAudioContext;
let _actx = null;

function getAudioCtx() {
    if (!_actx) _actx = new AudioCtx();
    return _actx;
}

function playNotifSound(type = 'msg') {
    if (!_soundOn) return;
    try {
        const ctx = getAudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        if (type === 'msg') {
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.1);
        } else {
            osc.frequency.setValueAtTime(660, ctx.currentTime);
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.08);
            osc.frequency.setValueAtTime(1320, ctx.currentTime + 0.16);
        }
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.4);
    } catch(e) {}
}

function toggleSound() {
    _soundOn = !_soundOn;
    localStorage.setItem('admin_sound', _soundOn ? 'on' : 'off');
    const btn = document.getElementById('sound-toggle');
    const lbl = document.getElementById('sound-label');
    if (_soundOn) {
        btn.classList.add('on');
        btn.querySelector('i').className = 'fas fa-volume-up';
        lbl.textContent = 'Som ativo';
        playNotifSound('new');
    } else {
        btn.classList.remove('on');
        btn.querySelector('i').className = 'fas fa-volume-mute';
        lbl.textContent = 'Som mudo';
    }
}
if (!_soundOn) {
    document.getElementById('sound-toggle').classList.remove('on');
    document.getElementById('sound-toggle').querySelector('i').className = 'fas fa-volume-mute';
    document.getElementById('sound-label').textContent = 'Som mudo';
}

// ---- Chat Polling ----
const SESSAO_ATIVA = <?= $sessaoAtiva ?: 'null' ?>;
let _ultimoMsgId = <?= $ultimoMsgId ?>;
let _prevNaoLidas = <?= $totalNaoLidas ?>;
let _prevSessoes = <?= json_encode(array_column($sessoes, 'id')) ?>;

function scrollBottom() {
    const area = document.getElementById('chat-msgs-area');
    if (area) area.scrollTop = area.scrollHeight;
}
scrollBottom();

function adicionarMsg(m) {
    const area = document.getElementById('chat-msgs-area');
    if (!area) return;
    const div = document.createElement('div');
    div.className = 'msg-row from-' + m.de;
    div.dataset.id = m.id;
    const hora = m.criado_em ? new Date(m.criado_em.replace(' ','T')).toLocaleTimeString('pt', {hour:'2-digit',minute:'2-digit'}) : '';
    const quem = m.de === 'admin' ? 'Você' : (m.de === 'sistema' ? 'Sistema' : 'Visitante');
    div.innerHTML = `<div class="msg-bubble">${escHtml(m.mensagem).replace(/\n/g,'<br>')}</div><div class="msg-meta">${quem} · ${hora}</div>`;
    area.appendChild(div);
    area.scrollTop = area.scrollHeight;
}

function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Poll for new messages in active conversation
function pollMsgs() {
    if (!SESSAO_ATIVA) return;
    fetch(`chat.php?ajax=msgs&sessao_id=${SESSAO_ATIVA}&ultimo_id=${_ultimoMsgId}`)
        .then(r => r.json()).then(d => {
            if (d.msgs && d.msgs.length) {
                d.msgs.forEach(m => {
                    if (m.id > _ultimoMsgId) {
                        _ultimoMsgId = m.id;
                        adicionarMsg(m);
                        if (m.de === 'visitante') playNotifSound('msg');
                    }
                });
            }
        }).catch(() => {});
}

// Poll sidebar for new conversations
function pollLista() {
    fetch('chat.php?ajax=sessoes')
        .then(r => r.json()).then(d => {
            const total = d.total_nao_lidas || 0;
            const badge = document.getElementById('badge-total');
            if (badge) {
                badge.style.display = total > 0 ? '' : 'none';
                badge.textContent = total + ' não lida(s)';
            }
            // Check for new conversations
            const novosIds = d.sessoes.map(s => s.id);
            const novas = novosIds.filter(id => !_prevSessoes.includes(id));
            if (novas.length > 0) {
                playNotifSound('new');
                // title flash
                flashTitle('💬 Nova conversa!');
            } else if (total > _prevNaoLidas) {
                playNotifSound('msg');
                flashTitle('💬 Nova mensagem!');
            }
            _prevNaoLidas = total;
            _prevSessoes = novosIds;
            // Update list
            atualizarLista(d.sessoes);
            document.getElementById('lista-status').textContent = 'Actualizado ' + new Date().toLocaleTimeString('pt', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
        }).catch(() => {});
}

function atualizarLista(sessoes) {
    const container = document.getElementById('lista-sessoes');
    if (!container) return;
    sessoes.forEach(s => {
        const existing = container.querySelector(`[data-sessao="${s.id}"]`);
        if (existing) {
            const nl = existing.querySelector('.badge-nl');
            if (s.nao_lidas > 0) {
                if (nl) { nl.textContent = s.nao_lidas; }
                else {
                    const nome = existing.querySelector('.cli-nome');
                    if (nome) {
                        const b = document.createElement('span');
                        b.className = 'badge-nl new-pulse';
                        b.textContent = s.nao_lidas;
                        nome.appendChild(b);
                    }
                    existing.classList.add('flash');
                    setTimeout(() => existing.classList.remove('flash'), 1000);
                }
            } else if (nl) nl.remove();
        } else {
            // New conversation – add to top
            const a = document.createElement('a');
            a.href = `chat.php?sessao=${s.id}`;
            a.className = 'chat-list-item flash';
            a.dataset.sessao = s.id;
            a.innerHTML = `
                <div class="cli-nome">${escHtml(s.visitante_nome || 'Visitante')} <span class="badge-nl new-pulse">${s.nao_lidas}</span></div>
                <div class="cli-preview">${escHtml((s.ultima_msg||'').substring(0,50))}</div>
                <div class="cli-hora">Agora</div>`;
            container.prepend(a);
            setTimeout(() => a.classList.remove('flash'), 1000);
        }
    });
}

// Title flash for browser tab notification
let _origTitle = document.title;
let _flashInterval = null;
function flashTitle(msg) {
    if (_flashInterval) clearInterval(_flashInterval);
    let on = true;
    _flashInterval = setInterval(() => {
        document.title = on ? msg : _origTitle;
        on = !on;
    }, 900);
    setTimeout(() => { clearInterval(_flashInterval); document.title = _origTitle; }, 8000);
}

// Start polling
setInterval(pollMsgs, 3000);
setInterval(pollLista, 5000);
pollLista();

// ---- Send Reply ----
function enviarResposta() {
    const txt = document.getElementById('resposta-txt');
    const msg = txt.value.trim();
    if (!msg || !SESSAO_ATIVA) return;
    const btn = document.getElementById('btn-send');
    btn.disabled = true;
    txt.value = '';
    const fd = new FormData();
    fd.append('sessao_id', SESSAO_ATIVA);
    fd.append('resposta', msg);
    fetch('chat.php?ajax=responder', { method: 'POST', body: fd })
        .then(r => r.json()).then(d => {
            if (d.ok) {
                adicionarMsg({ id: d.id, de: 'admin', mensagem: msg, criado_em: new Date().toISOString().replace('T',' ') });
                _ultimoMsgId = d.id;
            }
            btn.disabled = false;
            txt.focus();
        }).catch(() => { btn.disabled = false; });
}
</script>
<?php include 'partials/foot.php'; ?>