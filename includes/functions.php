<?php
require_once __DIR__ . '/db.php';

function getConfig($chave, $default = '') {
    $db = getDB();
    $stmt = $db->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
    $stmt->execute([$chave]);
    $row = $stmt->fetch();
    return $row ? $row['valor'] : $default;
}

function setConfig($chave, $valor) {
    $db = getDB();
    $stmt = $db->prepare("INSERT OR REPLACE INTO configuracoes (chave, valor) VALUES (?, ?)");
    $stmt->execute([$chave, $valor]);
}

function getAplicacoes($apenasAtivas = true) {
    $db = getDB();
    $sql = "SELECT * FROM aplicacoes" . ($apenasAtivas ? " WHERE ativo=1" : "") . " ORDER BY ordem, nome";
    return $db->query($sql)->fetchAll();
}

function getCarousel($apenasAtivos = true) {
    $db = getDB();
    $sql = "SELECT * FROM carousel" . ($apenasAtivos ? " WHERE ativo=1" : "") . " ORDER BY ordem";
    return $db->query($sql)->fetchAll();
}

function getCategoriasManual($aplicacaoId = null, $apenasAtivas = true) {
    $db = getDB();
    $sql = "SELECT cm.*, a.nome as aplicacao_nome FROM categorias_manual cm LEFT JOIN aplicacoes a ON cm.aplicacao_id = a.id WHERE 1=1";
    $params = [];
    if ($apenasAtivas) { $sql .= " AND cm.ativo=1"; }
    if ($aplicacaoId) { $sql .= " AND cm.aplicacao_id=?"; $params[] = $aplicacaoId; }
    $sql .= " ORDER BY cm.ordem, cm.nome";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getCategoria($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT cm.*, a.nome as aplicacao_nome FROM categorias_manual cm LEFT JOIN aplicacoes a ON cm.aplicacao_id = a.id WHERE cm.id=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getTopicosManual($categoriaId, $apenasAtivos = true) {
    $db = getDB();
    $sql = "SELECT * FROM topicos_manual WHERE categoria_id=?";
    if ($apenasAtivos) $sql .= " AND ativo=1";
    $sql .= " ORDER BY ordem, titulo";
    $stmt = $db->prepare($sql);
    $stmt->execute([$categoriaId]);
    return $stmt->fetchAll();
}

function getTopico($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT t.*, c.nome as categoria_nome, c.id as categoria_id, a.nome as aplicacao_nome FROM topicos_manual t JOIN categorias_manual c ON t.categoria_id=c.id LEFT JOIN aplicacoes a ON c.aplicacao_id=a.id WHERE t.id=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getPassosTopico($topicoId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM topico_passos WHERE topico_id=? ORDER BY ordem");
    $stmt->execute([$topicoId]);
    return $stmt->fetchAll();
}

function incrementarVisualizacoes($topicoId) {
    $db = getDB();
    $db->exec("UPDATE topicos_manual SET visualizacoes = visualizacoes + 1 WHERE id=$topicoId");
}

function getTopicosRecentes($limite = 5) {
    $db = getDB();
    $stmt = $db->prepare("SELECT t.*, c.nome as categoria_nome FROM topicos_manual t JOIN categorias_manual c ON t.categoria_id=c.id WHERE t.ativo=1 ORDER BY t.criado_em DESC LIMIT ?");
    $stmt->execute([$limite]);
    return $stmt->fetchAll();
}

function getFuncionalidades($aplicacaoId = null, $apenasDestaque = false, $limite = null) {
    $db = getDB();
    $sql = "SELECT f.*, a.nome as aplicacao_nome FROM funcionalidades f LEFT JOIN aplicacoes a ON f.aplicacao_id=a.id WHERE f.ativo=1";
    $params = [];
    if ($aplicacaoId) { $sql .= " AND f.aplicacao_id=?"; $params[] = $aplicacaoId; }
    if ($apenasDestaque) $sql .= " AND f.destaque=1";
    $sql .= " ORDER BY f.ordem";
    if ($limite) $sql .= " LIMIT $limite";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTargetAudience($aplicacaoId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM target_audience WHERE aplicacao_id=? ORDER BY ordem");
    $stmt->execute([$aplicacaoId]);
    return $stmt->fetchAll();
}

function getPerguntasPublicadas($topicoId = null, $limite = null) {
    $db = getDB();
    $sql = "SELECT p.*, t.titulo as topico_titulo FROM perguntas p LEFT JOIN topicos_manual t ON p.topico_id=t.id WHERE p.publicado=1 AND p.respondido=1";
    $params = [];
    if ($topicoId) { $sql .= " AND p.topico_id=?"; $params[] = $topicoId; }
    $sql .= " ORDER BY p.criado_em DESC";
    if ($limite) $sql .= " LIMIT $limite";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function enviarPergunta($nome, $email, $pergunta, $topicoId = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO perguntas (nome, email, pergunta, topico_id) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$nome, $email, $pergunta, $topicoId]);
}

function slugify($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $chars = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c','ñ'=>'n'];
    $text = strtr($text, $chars);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uploadImagem($file, $destino = 'uploads/') {
    $tipos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $tipos)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nome = uniqid() . '.' . $ext;
    $caminho = $destino . $nome;
    if (move_uploaded_file($file['tmp_name'], $caminho)) return $caminho;
    return false;
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

function adminLogin($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admin_usuarios WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_nome'] = $user['nome'];
        return true;
    }
    return false;
}

function getWhatsappLink() {
    $numero = getConfig('whatsapp_numero', '244923000000');
    $msg = urlencode(getConfig('whatsapp_mensagem', 'Olá!'));
    return "https://wa.me/$numero?text=$msg";
}

function getAplicacao($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM aplicacoes WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function timeAgo($datetime) {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);
    if ($diff->y > 0) return "há " . $diff->y . " ano" . ($diff->y > 1 ? "s" : "");
    if ($diff->m > 0) return "há " . $diff->m . " mês" . ($diff->m > 1 ? "es" : "");
    if ($diff->d > 0) return "há " . $diff->d . " dia" . ($diff->d > 1 ? "s" : "");
    if ($diff->h > 0) return "há " . $diff->h . " hora" . ($diff->h > 1 ? "s" : "");
    if ($diff->i > 0) return "há " . $diff->i . " minuto" . ($diff->i > 1 ? "s" : "");
    return "agora mesmo";
}
