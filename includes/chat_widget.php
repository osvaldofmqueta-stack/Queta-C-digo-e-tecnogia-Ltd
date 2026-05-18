<?php
// Register visit
if (session_status() === PHP_SESSION_NONE) session_start();
$_chatRootPath = isset($rootPath) ? $rootPath : '/';
?>
<!-- CHAT WIDGET -->
<div id="chat-widget">
    <button id="chat-toggle" onclick="chatToggle()" title="Suporte em Tempo Real" aria-label="Abrir chat de suporte">
        <i class="fas fa-comments" id="chat-icon-open"></i>
        <i class="fas fa-times" id="chat-icon-close" style="display:none;"></i>
        <span id="chat-badge" class="chat-notif-dot" style="display:none;"></span>
    </button>

    <div id="chat-panel" style="display:none;">
        <div class="chat-header">
            <div class="chat-header-info">
                <div class="chat-status-dot"></div>
                <div>
                    <strong>Suporte Queta Tech</strong>
                    <span>Resposta em tempo real</span>
                </div>
            </div>
            <button onclick="chatToggle()" class="chat-close-btn">×</button>
        </div>

        <!-- Step 1: Nome -->
        <div id="chat-step-nome" class="chat-body">
            <div class="chat-welcome">
                <div class="chat-avatar"><i class="fas fa-headset"></i></div>
                <p>Olá! Antes de começar, diz-nos o teu nome:</p>
            </div>
            <div class="chat-input-row">
                <input type="text" id="chat-nome-input" placeholder="O teu nome..." maxlength="60" onkeydown="if(event.key==='Enter')chatIniciar()">
                <button onclick="chatIniciar()" class="chat-send-btn"><i class="fas fa-arrow-right"></i></button>
            </div>
        </div>

        <!-- Step 2: Chat -->
        <div id="chat-step-chat" style="display:none;" class="chat-body chat-body-full">
            <div id="chat-msgs-list" class="chat-msgs-list"></div>
            <div class="chat-typing" id="chat-typing" style="display:none;">
                <span></span><span></span><span></span>
            </div>
            <div class="chat-input-row">
                <input type="text" id="chat-msg-input" placeholder="Escreve a tua mensagem..." maxlength="500" onkeydown="if(event.key==='Enter')chatEnviar()">
                <button onclick="chatEnviar()" class="chat-send-btn"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
let _chatToken = localStorage.getItem('chat_token') || null;
let _chatUltimoId = 0;
let _chatInterval = null;
let _chatAberto = false;

function chatToggle() {
    _chatAberto = !_chatAberto;
    document.getElementById('chat-panel').style.display = _chatAberto ? 'flex' : 'none';
    document.getElementById('chat-icon-open').style.display = _chatAberto ? 'none' : 'block';
    document.getElementById('chat-icon-close').style.display = _chatAberto ? 'block' : 'none';
    document.getElementById('chat-badge').style.display = 'none';
    if (_chatAberto) {
        if (_chatToken) {
            document.getElementById('chat-step-nome').style.display = 'none';
            document.getElementById('chat-step-chat').style.display = 'flex';
            chatCarregarMensagens(true);
        } else {
            setTimeout(() => document.getElementById('chat-nome-input').focus(), 100);
        }
    }
}

function chatIniciar() {
    const nome = document.getElementById('chat-nome-input').value.trim();
    if (!nome) return;
    const fd = new FormData();
    fd.append('acao', 'iniciar');
    fd.append('nome', nome);
    fetch('<?= $_chatRootPath ?>api/chat.php', { method: 'POST', body: fd })
        .then(r => r.json()).then(d => {
            if (d.ok) {
                _chatToken = d.token;
                localStorage.setItem('chat_token', d.token);
                document.getElementById('chat-step-nome').style.display = 'none';
                document.getElementById('chat-step-chat').style.display = 'flex';
                chatCarregarMensagens(true);
                iniciarPolling();
            }
        });
}

function chatEnviar() {
    const input = document.getElementById('chat-msg-input');
    const msg = input.value.trim();
    if (!msg || !_chatToken) return;
    input.value = '';
    chatAdicionarMsg({ de: 'visitante', mensagem: msg, criado_em: new Date().toISOString() });
    const fd = new FormData();
    fd.append('acao', 'enviar');
    fd.append('token', _chatToken);
    fd.append('mensagem', msg);
    fetch('<?= $_chatRootPath ?>api/chat.php', { method: 'POST', body: fd });
}

function chatCarregarMensagens(inicial = false) {
    if (!_chatToken) return;
    const url = `<?= $_chatRootPath ?>api/chat.php?acao=receber&token=${_chatToken}&ultimo_id=${_chatUltimoId}`;
    fetch(url).then(r => r.json()).then(d => {
        if (d.msgs && d.msgs.length) {
            d.msgs.forEach(m => {
                if (m.id > _chatUltimoId) {
                    _chatUltimoId = m.id;
                    chatAdicionarMsg(m);
                    if (!_chatAberto && m.de !== 'visitante') {
                        document.getElementById('chat-badge').style.display = 'flex';
                    }
                }
            });
        }
        if (inicial && d.msgs) {
            d.msgs.forEach(m => { if (m.id > _chatUltimoId) _chatUltimoId = m.id; });
        }
    });
}

function chatAdicionarMsg(m) {
    const lista = document.getElementById('chat-msgs-list');
    if (!lista) return;
    const div = document.createElement('div');
    div.className = 'chat-msg-wrap chat-msg-' + m.de;
    const hora = m.criado_em ? new Date(m.criado_em.replace(' ','T')).toLocaleTimeString('pt', {hour:'2-digit',minute:'2-digit'}) : '';
    div.innerHTML = `<div class="chat-msg-bubble">${escHtml(m.mensagem)}</div><div class="chat-msg-time">${hora}</div>`;
    lista.appendChild(div);
    lista.scrollTop = lista.scrollHeight;
}

function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function iniciarPolling() {
    if (_chatInterval) return;
    _chatInterval = setInterval(() => chatCarregarMensagens(), 4000);
}

// Auto-init if token exists
if (_chatToken) { iniciarPolling(); }

// Register visit
fetch('<?= $_chatRootPath ?>api/chat.php', {
    method: 'POST',
    body: (() => { const f = new FormData(); f.append('acao','visita'); f.append('pagina', location.pathname); return f; })()
}).then(r => r.json()).then(d => {
    if (d.total !== undefined) {
        const el = document.getElementById('visitor-count');
        if (el) el.textContent = d.total.toLocaleString('pt');
    }
});
</script>
