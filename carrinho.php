<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

$rootPath  = '/';
$pageTitle = 'Carrinho de Compras';

if (!clienteLogado()) {
    header('Location: /login.php?redirect=/carrinho.php');
    exit;
}

$cliente = getClienteLogado();
$itens   = getCarrinho($_SESSION['cliente_id']);
$whatsapp = getWhatsappLink();

if (isset($_POST['remover'])) {
    removerCarrinho((int)$_POST['remover'], $_SESSION['cliente_id']);
    header('Location: /carrinho.php');
    exit;
}

// Build WhatsApp checkout message
function buildCheckoutMsg($cliente, $itens) {
    $msg = "Olá! Gostaria de contratar o(s) seguinte(s) plano(s) do Super Escola:\n\n";
    foreach ($itens as $i) {
        $msg .= "• " . $i['nome'] . " — " . $i['preco'] . ($i['periodo'] ? " / " . $i['periodo'] : "") . "\n";
    }
    $msg .= "\nNome: " . $cliente['nome'];
    if ($cliente['telefone']) $msg .= "\nTelefone: " . $cliente['telefone'];
    if ($cliente['escola'])   $msg .= "\nEscola: " . $cliente['escola'];
    $msg .= "\n\nAguardo informações sobre o processo de contratação. Obrigado!";
    return $msg;
}
$checkoutUrl = '';
if (!empty($itens)) {
    $checkoutUrl = 'https://api.whatsapp.com/send?phone=244926219731&text=' . urlencode(buildCheckoutMsg($cliente, $itens)) . '&type=phone_number&app_absent=0';
}
?>
<?php include 'includes/header.php'; ?>

<section class="auth-section" style="min-height: 80vh;">
    <div class="container" style="max-width: 700px; padding-top: 40px; padding-bottom: 60px;">
        <div class="section-header" style="text-align:left; margin-bottom: 24px;">
            <span class="section-badge"><i class="fas fa-shopping-cart"></i> Carrinho</span>
            <h1 class="section-title" style="font-size:28px;">O Meu Carrinho</h1>
            <p>Olá, <strong><?= h($cliente['nome']) ?></strong>! Reveja os planos antes de continuar.</p>
        </div>

        <?php if (empty($itens)): ?>
        <div class="carrinho-vazio">
            <i class="fas fa-shopping-cart"></i>
            <h2>O carrinho está vazio</h2>
            <p>Adicione um plano para continuar.</p>
            <a href="/#planos" class="auth-submit" style="text-decoration:none; display:inline-flex; width:auto; padding: 12px 28px;">
                <i class="fas fa-tags"></i> Ver Planos
            </a>
        </div>
        <?php else: ?>
        <div class="carrinho-itens">
            <?php foreach ($itens as $item): ?>
            <div class="carrinho-item" style="border-left: 4px solid <?= h($item['cor']) ?>;">
                <div class="carrinho-item-info">
                    <div class="carrinho-plano-nome" style="color: <?= h($item['cor']) ?>;"><?= h($item['nome']) ?></div>
                    <div class="carrinho-plano-preco">
                        <?= h($item['preco']) ?><?= $item['periodo'] ? ' / ' . h($item['periodo']) : '' ?>
                    </div>
                </div>
                <form method="post" onsubmit="return confirm('Remover este plano do carrinho?')">
                    <input type="hidden" name="remover" value="<?= $item['id'] ?>">
                    <button type="submit" class="carrinho-remover" title="Remover">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="carrinho-actions">
            <a href="/#planos" class="btn-secondary"><i class="fas fa-plus"></i> Adicionar mais</a>
            <a href="<?= h($checkoutUrl) ?>" target="_blank" class="auth-submit" style="text-decoration:none; display:inline-flex; width:auto; padding: 14px 32px; background: #25D366; border-color: #25D366;">
                <i class="fab fa-whatsapp"></i> Finalizar pelo WhatsApp
            </a>
        </div>
        <p style="text-align:right; font-size:13px; color:var(--text-light); margin-top:10px;">
            <i class="fas fa-info-circle"></i> A equipa Queta Tech irá confirmar a encomenda via WhatsApp.
        </p>
        <?php endif; ?>

        <div style="margin-top:32px; padding-top:20px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; font-size:13px; color:var(--text-light);">
            <span><i class="fas fa-user-circle"></i> <?= h($cliente['email']) ?></span>
            <a href="/logout.php" style="color:var(--text-light);"><i class="fas fa-sign-out-alt"></i> Sair da conta</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
