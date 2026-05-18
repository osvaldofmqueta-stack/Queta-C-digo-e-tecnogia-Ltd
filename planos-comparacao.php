<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

$rootPath   = '/';
$pageTitle  = 'Comparação de Planos';
$pageDesc   = 'Compare os planos Premium, Golden e Ruby do Super Escola e escolha o mais adequado para a sua instituição de ensino.';

$grupos = [
    'Gestão Académica' => [
        ['feat'=>'Dashboard Principal',           'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Gestão de Alunos',              'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Gestão de Professores',         'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Turmas e Salas de Aula',        'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Horário Escolar',               'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Admissões e Matrículas',        'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Organizar Turmas',              'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Abertura de Avaliações',        'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Notas, Pautas e Presenças',     'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Acta de Presença em Provas',    'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Consulta de Aluno',             'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Acompanhamento de Pautas',      'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Histórico Académico',           'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Grelha Curricular',             'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Disciplinas',                   'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Quadro de Honra',               'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Diário de Classe',              'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Director de Turma',             'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Visão Geral Multi-Ano',         'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Análise de Desempenho',         'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Transferências de Alunos',      'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Finalistas',                    'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Trabalhos Finais / PAP',        'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Bolsas e Subsídios',            'p'=>0,'g'=>1,'r'=>1],
    ],
    'Portais e Comunicação' => [
        ['feat'=>'Painel da Secretaria',          'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Portal do Estudante',           'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Portal do Encarregado',         'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Painel do Professor',           'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Notificações do Sistema',       'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Mensagens (Professor)',          'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Chat Interno',                  'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Calendário Escolar / Eventos',  'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Calendário Académico',          'p'=>0,'g'=>1,'r'=>1],
    ],
    'Professor e Pedagogia' => [
        ['feat'=>'Minhas Turmas (Professor)',      'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Pautas & Notas (Professor)',     'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Sumário / Presenças (Professor)','p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Materiais Didáticos',            'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Exclusões de Faltas',            'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Relatório de Faltas',            'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Plano de Aula',                  'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Avaliação de Professores',       'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Área Pedagógica',                'p'=>0,'g'=>1,'r'=>1],
    ],
    'Documentação' => [
        ['feat'=>'Geração de PDFs',               'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Editor e Hub de Documentos',    'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Boletim de Matrícula',          'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Boletim de Propina',            'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Solicitações de Documentos',    'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Arquivo de Documentos',         'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Processos da Secretaria',       'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Gestão Académica (Hub)',        'p'=>1,'g'=>1,'r'=>1],
        ['feat'=>'Validação Financeira de Docs',  'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Estúdio de Emissão',            'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Centro de Emissão',             'p'=>0,'g'=>1,'r'=>1],
    ],
    'Gestão Financeira' => [
        ['feat'=>'Dashboard CEO',                 'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Gestão Financeira Completa',    'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Hub de Pagamentos',             'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Extrato de Propinas',           'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Relatórios Financeiros',        'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Relatórios & Análise Avançados','p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Tesouraria',                    'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Histórico de RUPE',             'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Bolsas e Subsídios',            'p'=>0,'g'=>1,'r'=>1],
    ],
    'Biblioteca' => [
        ['feat'=>'Biblioteca Escolar',            'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Gestão da Biblioteca',          'p'=>0,'g'=>1,'r'=>1],
    ],
    'Recursos Humanos' => [
        ['feat'=>'Hub de Recursos Humanos',       'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Registo de Funcionários',       'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Alterar Tipo de Vínculo',       'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Controlo de Faltas e Tempos',   'p'=>0,'g'=>1,'r'=>1],
        ['feat'=>'Controlo de RH',                'p'=>0,'g'=>0,'r'=>1],
        ['feat'=>'Processamento de Salários (Payroll)', 'p'=>0,'g'=>0,'r'=>1],
    ],
    'Controlo Avançado' => [
        ['feat'=>'Auditoria do Sistema',          'p'=>0,'g'=>0,'r'=>1],
        ['feat'=>'Controlo & Supervisão',         'p'=>0,'g'=>0,'r'=>1],
        ['feat'=>'Gestão de Acessos',             'p'=>0,'g'=>0,'r'=>1],
        ['feat'=>'Super Administração',           'p'=>0,'g'=>0,'r'=>1],
        ['feat'=>'Integração MED / SIGE Gov',     'p'=>0,'g'=>0,'r'=>1],
        ['feat'=>'Gestão de Planos de Subscrição','p'=>0,'g'=>0,'r'=>1],
    ],
];

$planos = [
    ['nome'=>'Premium','emoji'=>'⭐','cor'=>'#0066FF','itens'=>32],
    ['nome'=>'Golden', 'emoji'=>'🥇','cor'=>'#F5A623','itens'=>70],
    ['nome'=>'Ruby',   'emoji'=>'💎','cor'=>'#C0392B','itens'=>79],
];
$whatsapp = getWhatsappLink();
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero" style="background: linear-gradient(135deg, var(--dark) 0%, #1a2340 100%); padding: 64px 0 48px; text-align:center; color:white;">
    <div class="container">
        <span class="section-badge" style="background:rgba(255,255,255,.1); color:white; margin-bottom:16px; display:inline-block;">
            <i class="fas fa-table"></i> Comparação Completa
        </span>
        <h1 style="font-size:clamp(28px,4vw,44px); font-weight:800; margin-bottom:16px;">Comparação de Planos</h1>
        <p style="font-size:18px; opacity:.8; max-width:580px; margin:0 auto 28px;">
            Veja em detalhe todas as funcionalidades incluídas em cada plano do Super Escola.
        </p>
        <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
            <?php foreach ($planos as $pl): ?>
            <div style="background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); border-radius:12px; padding:12px 24px; text-align:center;">
                <div style="font-size:24px;"><?= $pl['emoji'] ?></div>
                <div style="font-weight:800; font-size:16px;"><?= h($pl['nome']) ?></div>
                <div style="font-size:12px; opacity:.7;"><?= $pl['itens'] ?> funcionalidades</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="container" style="padding: 48px 16px 80px;">

    <!-- Sticky Plan Headers -->
    <div class="comp-table-wrap">
        <table class="comp-table">
            <thead>
                <tr>
                    <th class="comp-feat-col">Funcionalidade</th>
                    <?php foreach ($planos as $pl): ?>
                    <th class="comp-plan-col" style="border-top: 4px solid <?= h($pl['cor']) ?>;">
                        <div class="comp-plan-emoji"><?= $pl['emoji'] ?></div>
                        <div class="comp-plan-name"><?= h($pl['nome']) ?></div>
                        <div class="comp-plan-count"><?= $pl['itens'] ?> funcionalidades</div>
                        <a href="<?= $whatsapp ?>" target="_blank" class="comp-plan-cta" style="background:<?= h($pl['cor']) ?>;">
                            <i class="fab fa-whatsapp"></i> Escolher
                        </a>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grupos as $grupo => $feats): ?>
                <tr class="comp-group-row">
                    <td colspan="4"><i class="fas fa-chevron-right" style="color:var(--primary); margin-right:8px;"></i><?= h($grupo) ?></td>
                </tr>
                <?php foreach ($feats as $f): ?>
                <tr class="comp-feat-row">
                    <td class="comp-feat-name"><?= h($f['feat']) ?></td>
                    <td class="comp-check <?= $f['p'] ? 'comp-yes' : 'comp-no' ?>">
                        <?= $f['p'] ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>' ?>
                    </td>
                    <td class="comp-check <?= $f['g'] ? 'comp-yes' : 'comp-no' ?>">
                        <?= $f['g'] ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>' ?>
                    </td>
                    <td class="comp-check <?= $f['r'] ? 'comp-yes' : 'comp-no' ?>">
                        <?= $f['r'] ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="comp-feat-col" style="font-weight:700;">Saber Mais</td>
                    <?php foreach ($planos as $pl): ?>
                    <td style="text-align:center; padding:20px 12px;">
                        <a href="<?= $whatsapp ?>" target="_blank" class="comp-plan-cta" style="background:<?= h($pl['cor']) ?>; display:inline-flex;">
                            <i class="fab fa-whatsapp"></i> <?= h($pl['nome']) ?>
                        </a>
                    </td>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
        </table>
    </div>

    <p style="text-align:center; margin-top:32px; color:var(--text-light); font-size:14px;">
        <i class="fas fa-info-circle" style="color:var(--primary);"></i>
        Precisa de uma proposta personalizada?
        <a href="<?= $whatsapp ?>" target="_blank" style="color:var(--primary); font-weight:600;">Fale connosco pelo WhatsApp</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
