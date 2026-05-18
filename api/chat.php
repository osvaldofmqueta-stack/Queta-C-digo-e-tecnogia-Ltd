<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../includes/db.php';

$db = getDB();
$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

function gerarToken() {
    return bin2hex(random_bytes(16));
}

function registarVisita($pagina = '/') {
    global $db;
    $ip = md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $sessao = md5(session_id());
    $hoje = date('Y-m-d');
    $existe = $db->prepare("SELECT id FROM visitas WHERE sessao_hash=? AND DATE(criado_em)=?");
    $existe->execute([$sessao, $hoje]);
    if (!$existe->fetch()) {
        $db->prepare("INSERT INTO visitas (ip_hash, pagina, sessao_hash) VALUES (?,?,?)")
           ->execute([$ip, $pagina, $sessao]);
    }
}

switch ($acao) {

    case 'iniciar':
        $nome = trim($_POST['nome'] ?? '');
        if (!$nome) { echo json_encode(['erro' => 'Nome obrigatório']); exit; }
        $token = gerarToken();
        $db->prepare("INSERT INTO chat_sessoes (token, visitante_nome) VALUES (?,?)")
           ->execute([$token, $nome]);
        $sessaoId = $db->lastInsertId();
        $db->prepare("INSERT INTO chat_mensagens (sessao_id, de, mensagem, lido) VALUES (?,?,?,?)")
           ->execute([$sessaoId, 'sistema', 'Olá ' . $nome . '! Como posso ajudar?', 1]);
        echo json_encode(['ok' => true, 'token' => $token, 'sessao_id' => $sessaoId]);
        break;

    case 'enviar':
        $token   = trim($_POST['token'] ?? '');
        $msg     = trim($_POST['mensagem'] ?? '');
        if (!$token || !$msg) { echo json_encode(['erro' => 'Dados inválidos']); exit; }
        $s = $db->prepare("SELECT id FROM chat_sessoes WHERE token=?");
        $s->execute([$token]);
        $sessao = $s->fetch();
        if (!$sessao) { echo json_encode(['erro' => 'Sessão inválida']); exit; }
        $db->prepare("UPDATE chat_sessoes SET ultima_atividade=CURRENT_TIMESTAMP WHERE token=?")
           ->execute([$token]);
        $db->prepare("INSERT INTO chat_mensagens (sessao_id, de, mensagem) VALUES (?,?,?)")
           ->execute([$sessao['id'], 'visitante', $msg]);
        $msgId = $db->lastInsertId();
        echo json_encode(['ok' => true, 'id' => (int)$msgId]);
        break;

    case 'receber':
        $token    = trim($_GET['token'] ?? '');
        $ultimoId = (int)($_GET['ultimo_id'] ?? 0);
        if (!$token) { echo json_encode(['msgs' => []]); exit; }
        $s = $db->prepare("SELECT id FROM chat_sessoes WHERE token=?");
        $s->execute([$token]);
        $sessao = $s->fetch();
        if (!$sessao) { echo json_encode(['msgs' => []]); exit; }
        $stmt = $db->prepare("SELECT id, de, mensagem, criado_em FROM chat_mensagens WHERE sessao_id=? AND id>? ORDER BY id ASC");
        $stmt->execute([$sessao['id'], $ultimoId]);
        $msgs = $stmt->fetchAll();
        $db->prepare("UPDATE chat_mensagens SET lido=1 WHERE sessao_id=? AND de IN ('admin','sistema')")
           ->execute([$sessao['id']]);
        echo json_encode(['msgs' => $msgs]);
        break;

    case 'visita':
        registarVisita($_POST['pagina'] ?? '/');
        $total = $db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas")->fetchColumn();
        echo json_encode(['ok' => true, 'total' => (int)$total]);
        break;

    case 'total_visitas':
        $total = $db->query("SELECT COUNT(DISTINCT sessao_hash) FROM visitas")->fetchColumn();
        echo json_encode(['total' => (int)$total]);
        break;

    default:
        echo json_encode(['erro' => 'Ação inválida']);
}
