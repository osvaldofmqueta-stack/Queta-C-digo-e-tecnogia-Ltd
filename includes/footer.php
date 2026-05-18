<?php
$siteName = getConfig('site_nome', 'Queta Código e Tecnologia, Ltd');
$siteEmail = getConfig('site_email', 'geral@queta.ao');
$whatsapp = getWhatsappLink();
$rootPath = isset($rootPath) ? $rootPath : '/';
$aplicacoes = getAplicacoes();
$categorias = getCategoriasManual();
?>
<footer class="site-footer">
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <span class="logo-q">Q</span><span class="logo-rest">ueta</span>
                        <span class="logo-dot">·</span>
                        <span class="logo-tech">Tech</span>
                    </div>
                    <p><?= h($siteName) ?></p>
                    <p class="footer-desc">Soluções tecnológicas inovadoras para a gestão educacional moderna em Angola e África.</p>
                    <div class="footer-social">
                        <a href="<?= $whatsapp ?>" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="mailto:<?= h($siteEmail) ?>" title="Email"><i class="fas fa-envelope"></i></a>
                        <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="footer-links">
                    <h4>Aplicações</h4>
                    <ul>
                        <?php foreach($aplicacoes as $app): ?>
                        <li><a href="<?= $rootPath ?>aplicacao.php?id=<?= $app['id'] ?>"><i class="fas fa-angle-right"></i> <?= h($app['nome']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Manual de Apoio</h4>
                    <ul>
                        <?php foreach(array_slice($categorias, 0, 5) as $cat): ?>
                        <li><a href="<?= $rootPath ?>manual/categoria.php?id=<?= $cat['id'] ?>"><i class="fas fa-angle-right"></i> <?= h($cat['nome']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="footer-contact">
                    <h4>Contacto</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> <a href="mailto:<?= h($siteEmail) ?>"><?= h($siteEmail) ?></a></li>
                        <li><i class="fab fa-whatsapp"></i> <a href="<?= $whatsapp ?>" target="_blank">WhatsApp</a></li>
                        <li><i class="fas fa-map-marker-alt"></i> Angola</li>
                    </ul>
                    <a href="<?= $whatsapp ?>" target="_blank" class="btn-footer-demo">
                        <i class="fas fa-calendar-alt"></i> Agendar Demonstração
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= h($siteName) ?>. Todos os direitos reservados.</p>
            <div class="footer-bottom-links">
                <a href="<?= $rootPath ?>privacidade.php">Política de Privacidade</a>
                <a href="<?= $rootPath ?>termos.php">Termos de Uso</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= $rootPath ?>assets/js/main.js"></script>
<?= isset($extraScripts) ? $extraScripts : '' ?>
</body>
</html>
