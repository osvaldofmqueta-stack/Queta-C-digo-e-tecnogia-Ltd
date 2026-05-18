<?php
define('DB_PATH', __DIR__ . '/../db/queta.db');

function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA foreign_keys = ON;');
        initDB($db);
    }
    return $db;
}

function initDB($db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS configuracoes (
            chave TEXT PRIMARY KEY,
            valor TEXT
        );

        CREATE TABLE IF NOT EXISTS aplicacoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            descricao TEXT,
            imagem TEXT,
            url TEXT,
            ativo INTEGER DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS carousel (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo TEXT NOT NULL,
            descricao TEXT,
            imagem TEXT,
            link TEXT,
            ativo INTEGER DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS categorias_manual (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            descricao TEXT,
            icone TEXT DEFAULT 'fa-book',
            aplicacao_id INTEGER,
            ativo INTEGER DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (aplicacao_id) REFERENCES aplicacoes(id)
        );

        CREATE TABLE IF NOT EXISTS topicos_manual (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            categoria_id INTEGER NOT NULL,
            titulo TEXT NOT NULL,
            conteudo TEXT,
            video_url TEXT,
            ativo INTEGER DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            visualizacoes INTEGER DEFAULT 0,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (categoria_id) REFERENCES categorias_manual(id)
        );

        CREATE TABLE IF NOT EXISTS topico_passos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            topico_id INTEGER NOT NULL,
            titulo TEXT,
            descricao TEXT,
            imagem TEXT,
            ordem INTEGER DEFAULT 0,
            FOREIGN KEY (topico_id) REFERENCES topicos_manual(id)
        );

        CREATE TABLE IF NOT EXISTS perguntas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            topico_id INTEGER,
            nome TEXT NOT NULL,
            email TEXT,
            pergunta TEXT NOT NULL,
            resposta TEXT,
            respondido INTEGER DEFAULT 0,
            publicado INTEGER DEFAULT 1,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            respondido_em DATETIME,
            FOREIGN KEY (topico_id) REFERENCES topicos_manual(id)
        );

        CREATE TABLE IF NOT EXISTS funcionalidades (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            aplicacao_id INTEGER,
            titulo TEXT NOT NULL,
            descricao TEXT,
            imagem TEXT,
            destaque INTEGER DEFAULT 0,
            ativo INTEGER DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (aplicacao_id) REFERENCES aplicacoes(id)
        );

        CREATE TABLE IF NOT EXISTS admin_usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            nome TEXT,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS target_audience (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            aplicacao_id INTEGER,
            titulo TEXT NOT NULL,
            descricao TEXT,
            icone TEXT DEFAULT 'fa-user',
            ordem INTEGER DEFAULT 0,
            FOREIGN KEY (aplicacao_id) REFERENCES aplicacoes(id)
        );

        CREATE TABLE IF NOT EXISTS clientes_destaque (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome_escola TEXT NOT NULL,
            logo TEXT DEFAULT '',
            plano TEXT NOT NULL,
            plano_cor TEXT DEFAULT '#0066FF',
            plano_emoji TEXT DEFAULT '⭐',
            localizacao TEXT DEFAULT '',
            ativo INTEGER DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS planos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            aplicacao_id INTEGER,
            nome TEXT NOT NULL,
            preco TEXT NOT NULL,
            periodo TEXT DEFAULT 'mês',
            descricao TEXT,
            destaque INTEGER DEFAULT 0,
            ativo INTEGER DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            cor TEXT DEFAULT '#0066FF',
            badge TEXT DEFAULT '',
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (aplicacao_id) REFERENCES aplicacoes(id)
        );

        CREATE TABLE IF NOT EXISTS plano_itens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            plano_id INTEGER NOT NULL,
            descricao TEXT NOT NULL,
            incluido INTEGER DEFAULT 1,
            ordem INTEGER DEFAULT 0,
            FOREIGN KEY (plano_id) REFERENCES planos(id)
        );
    ");

    $check = $db->query("SELECT COUNT(*) as c FROM admin_usuarios")->fetch();
    if ($check['c'] == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO admin_usuarios (username, password, nome) VALUES ('admin', '$hash', 'Administrador')");
    }

    $checkApp = $db->query("SELECT COUNT(*) as c FROM aplicacoes")->fetch();
    if ($checkApp['c'] == 0) {
        $db->exec("INSERT INTO aplicacoes (nome, descricao, imagem, url, ativo, ordem)
            VALUES ('Super Escola', 'Sistema ERP de Gestão Académica. Simplifique a gestão da sua escola e gere mais receita!', 'super-escola.png', '#demo', 1, 1)");

        $appId = $db->lastInsertId();

        $db->exec("INSERT INTO carousel (titulo, descricao, imagem, link, ativo, ordem) VALUES
            ('Bem-vindo à Queta Código e Tecnologia', 'Soluções tecnológicas para a educação moderna', 'carousel1.jpg', '#sobre', 1, 1),
            ('Super Escola — ERP Académico', 'Gerencie matrículas, notas, finanças e muito mais numa só plataforma', 'carousel2.jpg', '#demo', 1, 2),
            ('Simplifique a Gestão da sua Escola', 'Aumente a eficiência administrativa e gere mais receita com o Super Escola', 'carousel3.jpg', '#contacto', 1, 3)");

        $db->exec("INSERT INTO categorias_manual (nome, descricao, icone, aplicacao_id, ativo, ordem) VALUES
            ('Primeiros Passos', 'Configurações iniciais e introdução ao sistema', 'fa-rocket', $appId, 1, 1),
            ('Gestão de Alunos', 'Matrículas, transferências e historial académico', 'fa-user-graduate', $appId, 1, 2),
            ('Gestão Financeira', 'Propinas, pagamentos e relatórios financeiros', 'fa-money-bill-wave', $appId, 1, 3),
            ('Relatórios e Estatísticas', 'Dashboards e relatórios de desempenho', 'fa-chart-bar', $appId, 1, 4),
            ('Configurações do Sistema', 'Parâmetros gerais, utilizadores e permissões', 'fa-cog', $appId, 1, 5)");

        $cat1 = $db->query("SELECT id FROM categorias_manual WHERE nome='Primeiros Passos'")->fetch();
        $catId = $cat1['id'];

        $db->exec("INSERT INTO topicos_manual (categoria_id, titulo, conteudo, ativo, ordem) VALUES
            ($catId, 'Como fazer o primeiro login', 'Aprenda a aceder ao sistema pela primeira vez e configurar a sua conta.', 1, 1),
            ($catId, 'Configurar os dados da escola', 'Preencha todas as informações básicas da sua instituição de ensino.', 1, 2),
            ($catId, 'Criar o ano lectivo', 'Defina o ano lectivo, trimestres e calendário escolar.', 1, 3)");

        $topicoId = $db->query("SELECT id FROM topicos_manual WHERE titulo='Como fazer o primeiro login'")->fetch()['id'];

        $db->exec("INSERT INTO topico_passos (topico_id, titulo, descricao, ordem) VALUES
            ($topicoId, 'Aceder ao endereço do sistema', 'Abra o seu navegador e aceda ao endereço fornecido pela equipa de suporte. Ex: https://suaescola.superescola.ao', 1),
            ($topicoId, 'Introduzir as credenciais', 'Na página de login, insira o seu nome de utilizador e a senha temporária enviada por email.', 2),
            ($topicoId, 'Alterar a senha no primeiro acesso', 'O sistema vai pedir que altere a senha temporária. Escolha uma senha segura com pelo menos 8 caracteres.', 3),
            ($topicoId, 'Explorar o painel principal', 'Após o login, será redirecionado para o painel principal onde pode ver um resumo de toda a atividade escolar.', 4)");

        $db->exec("INSERT INTO funcionalidades (aplicacao_id, titulo, descricao, destaque, ativo, ordem) VALUES
            ($appId, 'Gestão de Matrículas', 'Processo digital completo de matrículas com geração automática de fichas e contratos.', 1, 1, 1),
            ($appId, 'Controlo de Propinas', 'Acompanhe pagamentos, gere recibos e envie notificações automáticas de atraso.', 1, 1, 2),
            ($appId, 'Livro de Notas Digital', 'Lançamento de notas online com cálculo automático de médias e aprovação/reprovação.', 1, 1, 3),
            ($appId, 'Comunicação Interna', 'Sistema de mensagens entre professores, alunos e encarregados de educação.', 1, 1, 4),
            ($appId, 'Relatórios Automáticos', 'Gere relatórios detalhados de desempenho escolar, financeiro e administrativo.', 0, 1, 5),
            ($appId, 'App para Pais', 'Os encarregados acompanham as notas, presenças e mensagens pelo telemóvel.', 0, 1, 6)");

        $db->exec("INSERT INTO target_audience (aplicacao_id, titulo, descricao, icone, ordem) VALUES
            ($appId, 'Escolas Primárias', 'Ideal para gerir turmas, notas e comunicação com os encarregados de educação.', 'fa-school', 1),
            ($appId, 'Escolas Secundárias', 'Controlo completo de matrículas, avaliações e propinas para o ensino secundário.', 'fa-graduation-cap', 2),
            ($appId, 'Institutos e Faculdades', 'Solução escalável para instituições de ensino superior com múltiplos cursos.', 'fa-university', 3),
            ($appId, 'Centros de Formação', 'Gestão de turmas, certificados e pagamentos para centros de formação profissional.', 'fa-chalkboard-teacher', 4)");

        $checkClientes = $db->query("SELECT COUNT(*) as c FROM clientes_destaque")->fetch();
        if ($checkClientes['c'] == 0) {
            $db->exec("INSERT INTO clientes_destaque (nome_escola, logo, plano, plano_cor, plano_emoji, localizacao, ativo, ordem) VALUES
                ('Colégio Sagrado Coração', '', 'Golden', '#F5A623', '🥇', 'Luanda, Angola', 1, 1),
                ('Instituto Superior Politécnico do Huambo', '', 'Ruby', '#C0392B', '💎', 'Huambo, Angola', 1, 2),
                ('Escola Primária Nações Unidas', '', 'Premium', '#0066FF', '⭐', 'Benguela, Angola', 1, 3),
                ('Colégio Internacional de Cabinda', '', 'Golden', '#F5A623', '🥇', 'Cabinda, Angola', 1, 4),
                ('Instituto Médio Comercial', '', 'Premium', '#0066FF', '⭐', 'Luanda, Angola', 1, 5)");
        }

        $checkPlanos = $db->query("SELECT COUNT(*) as c FROM planos")->fetch();
        if ($checkPlanos['c'] == 0) {
            $db->exec("INSERT INTO planos (aplicacao_id, nome, preco, periodo, descricao, destaque, ativo, ordem, cor, badge) VALUES
                ($appId, 'Premium', 'Consultar', 'mês', 'A base completa para gerir a sua escola: alunos, professores, turmas, notas, presenças e muito mais.', 0, 1, 1, '#0066FF', '32 Funcionalidades'),
                ($appId, 'Golden', 'Consultar', 'mês', 'Tudo do Premium mais gestão financeira, biblioteca, recursos humanos, relatórios avançados e emissão de documentos.', 1, 1, 2, '#F5A623', '70 Funcionalidades'),
                ($appId, 'Ruby', 'Consultar', 'mês', 'A solução completa e sem limites: payroll, auditoria, super administração, integração MED/SIGE Gov e muito mais.', 0, 1, 3, '#C0392B', '79 Funcionalidades')");

            $p1 = $db->lastInsertId() - 2;
            $p2 = $p1 + 1;
            $p3 = $p1 + 2;

            // Premium — 32 funcionalidades (destaques para o card)
            $db->exec("INSERT INTO plano_itens (plano_id, descricao, incluido, ordem) VALUES
                ($p1, 'Dashboard Principal', 1, 1),
                ($p1, 'Gestão de Alunos', 1, 2),
                ($p1, 'Gestão de Professores', 1, 3),
                ($p1, 'Turmas e Salas de Aula', 1, 4),
                ($p1, 'Notas, Pautas e Presenças', 1, 5),
                ($p1, 'Horário Escolar', 1, 6),
                ($p1, 'Portal do Estudante', 1, 7),
                ($p1, 'Portal do Encarregado', 1, 8),
                ($p1, 'Painel da Secretaria', 1, 9),
                ($p1, 'Admissões e Matrículas', 1, 10),
                ($p1, 'Editor e Hub de Documentos', 1, 11),
                ($p1, 'Geração de PDFs', 1, 12),
                ($p1, 'Boletim de Matrícula e Propina', 1, 13),
                ($p1, 'Notificações do Sistema', 1, 14),
                ($p1, 'Painel do Professor', 1, 15),
                ($p1, 'Materiais Didáticos', 1, 16),
                ($p1, 'Calendário Escolar / Eventos', 1, 17),
                ($p1, 'Solicitações de Documentos', 1, 18),
                ($p1, 'Arquivo de Documentos', 1, 19),
                ($p1, 'Abertura de Avaliações', 1, 20),
                ($p1, 'Organizar Turmas', 1, 21),
                ($p1, 'Acta de Presença em Provas', 1, 22),
                ($p1, 'Mensagens (Professor)', 1, 23),
                ($p1, 'Sumário / Presenças (Professor)', 1, 24),
                ($p1, 'Pautas & Notas (Professor)', 1, 25),
                ($p1, 'Minhas Turmas (Professor)', 1, 26),
                ($p1, 'Processos da Secretaria', 1, 27),
                ($p1, 'Gestão Académica (Hub)', 1, 28),
                ($p1, 'Boletim de Matrícula', 1, 29),
                ($p1, 'Boletim de Propina', 1, 30),
                ($p1, 'Acompanhamento de Pautas', 1, 31),
                ($p1, 'Consulta de Aluno', 1, 32)");

            // Golden — 70 funcionalidades (Premium + 38 extras)
            $db->exec("INSERT INTO plano_itens (plano_id, descricao, incluido, ordem) VALUES
                ($p2, 'Tudo do Plano Premium (32 funcionalidades)', 1, 1),
                ($p2, 'Dashboard CEO', 1, 2),
                ($p2, 'Gestão Financeira Completa', 1, 3),
                ($p2, 'Hub de Pagamentos', 1, 4),
                ($p2, 'Extrato de Propinas', 1, 5),
                ($p2, 'Relatórios Financeiros', 1, 6),
                ($p2, 'Validação Financeira de Documentos', 1, 7),
                ($p2, 'Biblioteca Escolar', 1, 8),
                ($p2, 'Gestão da Biblioteca', 1, 9),
                ($p2, 'Transferências de Alunos', 1, 10),
                ($p2, 'Histórico Académico', 1, 11),
                ($p2, 'Grelha Curricular', 1, 12),
                ($p2, 'Disciplinas', 1, 13),
                ($p2, 'Quadro de Honra', 1, 14),
                ($p2, 'Exclusões de Faltas', 1, 15),
                ($p2, 'Diário de Classe', 1, 16),
                ($p2, 'Director de Turma', 1, 17),
                ($p2, 'Relatório de Faltas', 1, 18),
                ($p2, 'Chat Interno', 1, 19),
                ($p2, 'Calendário Académico', 1, 20),
                ($p2, 'Bolsas e Subsídios', 1, 21),
                ($p2, 'Análise de Desempenho', 1, 22),
                ($p2, 'Visão Geral Multi-Ano', 1, 23),
                ($p2, 'Relatórios & Análise Avançados', 1, 24),
                ($p2, 'Hub de Recursos Humanos', 1, 25),
                ($p2, 'Área Pedagógica', 1, 26),
                ($p2, 'Plano de Aula', 1, 27),
                ($p2, 'Avaliação de Professores', 1, 28),
                ($p2, 'Trabalhos Finais / PAP', 1, 29),
                ($p2, 'Registo de Funcionários', 1, 30),
                ($p2, 'Alterar Tipo de Vínculo', 1, 31),
                ($p2, 'Finalistas', 1, 32),
                ($p2, 'Controlo de Faltas e Tempos (RH)', 1, 33),
                ($p2, 'Histórico de RUPE', 1, 34),
                ($p2, 'Tesouraria', 1, 35),
                ($p2, 'Acompanhamento de Pautas', 1, 36),
                ($p2, 'Estúdio de Emissão', 1, 37),
                ($p2, 'Centro de Emissão', 1, 38),
                ($p2, 'Consulta de Aluno', 1, 39)");

            // Ruby — 79 funcionalidades (Golden + 9 extras)
            $db->exec("INSERT INTO plano_itens (plano_id, descricao, incluido, ordem) VALUES
                ($p3, 'Tudo do Plano Golden (70 funcionalidades)', 1, 1),
                ($p3, 'Controlo de RH', 1, 2),
                ($p3, 'Processamento de Salários (Payroll)', 1, 3),
                ($p3, 'Auditoria do Sistema', 1, 4),
                ($p3, 'Controlo & Supervisão', 1, 5),
                ($p3, 'Gestão de Acessos', 1, 6),
                ($p3, 'Super Administração', 1, 7),
                ($p3, 'Integração MED / SIGE Gov', 1, 8),
                ($p3, 'Gestão de Planos de Subscrição', 1, 9)");
        }

        $db->exec("INSERT INTO configuracoes (chave, valor) VALUES
            ('site_nome', 'Queta Código e Tecnologia, Ltd'),
            ('site_slogan', 'Tecnologia ao serviço da educação'),
            ('site_email', 'geral@queta.ao'),
            ('whatsapp_numero', '244923000000'),
            ('whatsapp_mensagem', 'Olá! Gostaria de saber mais sobre o Super Escola.'),
            ('youtube_video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
            ('demo_link', '#contacto'),
            ('logo', '')");
    }
}
