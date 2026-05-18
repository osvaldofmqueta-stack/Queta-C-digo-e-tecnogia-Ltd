<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

switch ($acao) {
    case 'registar':
        $nome     = trim($_POST['nome'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $tel      = trim($_POST['telefone'] ?? '');
        $escola   = trim($_POST['escola'] ?? '');
        if (!$nome || !$email || !$password) { echo json_encode(['erro' => 'Preencha todos os campos obrigatórios.']); exit; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['erro' => 'Email inválido.']); exit; }
        if (strlen($password) < 6) { echo json_encode(['erro' => 'A senha deve ter pelo menos 6 caracteres.']); exit; }
        $res = registarCliente($nome, $email, $password, $tel, $escola);
        if (isset($res['erro'])) { echo json_encode(['erro' => $res['erro']]); exit; }
        loginCliente($email, $password);
        echo json_encode(['ok' => true, 'nome' => $nome]);
        break;

    case 'login':
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) { echo json_encode(['erro' => 'Preencha email e senha.']); exit; }
        $c = loginCliente($email, $password);
        if (!$c) { echo json_encode(['erro' => 'Email ou senha incorrectos.']); exit; }
        echo json_encode(['ok' => true, 'nome' => $c['nome']]);
        break;

    case 'logout':
        logoutCliente();
        echo json_encode(['ok' => true]);
        break;

    case 'carrinho_adicionar':
        if (!clienteLogado()) { echo json_encode(['erro' => 'login_required']); exit; }
        $planoId = (int)($_POST['plano_id'] ?? 0);
        if (!$planoId) { echo json_encode(['erro' => 'Plano inválido.']); exit; }
        $r = adicionarCarrinho($_SESSION['cliente_id'], $planoId);
        $total = contarCarrinho($_SESSION['cliente_id']);
        echo json_encode(['ok' => true, 'novo' => $r, 'total' => $total]);
        break;

    case 'carrinho_remover':
        if (!clienteLogado()) { echo json_encode(['erro' => 'login_required']); exit; }
        $id = (int)($_POST['id'] ?? 0);
        removerCarrinho($id, $_SESSION['cliente_id']);
        $total = contarCarrinho($_SESSION['cliente_id']);
        echo json_encode(['ok' => true, 'total' => $total]);
        break;

    default:
        echo json_encode(['erro' => 'Ação inválida']);
}
