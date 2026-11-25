# 🎨 Sketchbook Educacional Interativo — English for Kids (TCC)

> Plataforma web gamificada e interativa em formato de **caderno escolar cartum**, desenvolvida para o ensino lúdico de língua inglesa voltado a crianças de 6 a 9 anos (Ensino Fundamental 1).

---

## 📋 Sumário
1. [Visão Geral e Proposta Pedagógica](#-visão-geral-e-proposta-pedagógica)
2. [Identidade Visual & Design System](#-identidade-visual--design-system)
3. [Módulos e Jogos Educacionais](#-módulos-e-jogos-educacionais)
4. [Recursos de Acessibilidade e Inclusão](#-recursos-de-acessibilidade-e-inclusão)
5. [Arquitetura de Software e Tecnologias](#-arquitetura-de-software-e-tecnologias)
6. [Banco de Dados (MySQL / MariaDB)](#-banco-de-dados-mysql--mariadb)
7. [Compatibilidade com Servidores Linux & Apache](#-compatibilidade-com-servidores-linux--apache)
8. [Instalação e Execução Local](#-instalação-e-execução-local)
9. [Implantação em Hospedagem Externa (cPanel / VPS)](#-implantação-em-hospedagem-externa-cpanel--vps)
10. [Estrutura de Pastas do Repositório](#-estrutura-de-pastas-do-repositório)

---

## 🎯 Visão Geral e Proposta Pedagógica

O projeto foi concebido como um Trabalho de Conclusão de Curso (TCC) com foco no aprendizado de vocabulário, ortografia, construção sintática e percepção auditiva da língua inglesa por meio da gamificação afetiva e lúdica.

* **Público-Alvo**: Crianças de 6 a 9 anos (fase de alfabetização e primeiros passos em língua estrangeira).
* **Conceito Chave**: Transformar a tela em um **caderno escolar de folhas pautadas**, com espirais no topo, post-its coloridos e animações suaves de fundo kraft.
* **Segurança e Progressão**: O aluno acumula pontuação, responde quizzes temáticos, sobe no mural de conquistas (*Top Sabidos*) e recebe reforço positivo com sons e animações.

---

## 🎨 Identidade Visual & Design System

* **Folha Central de Caderno**: Páginas brancas (`#FFFFFF`) com linhas horizontais sutis (`#E2E8F0`), bordas escuras em tinta cartum (`#0F172A`) e espirais metálicos realistas no cabeçalho.
* **Fundo da Aplicação**: Tom papelão/kraft (`#c6a175`) com elementos oscilantes animados via CSS `@keyframes` que deslizam de um lado a outro sem poluir a visão.
* **Paleta de Cores de Alto Contraste**:
  * 🟢 **Verde Vibrante** (`#00C853` / `#054D20`): Ações de sucesso, botões principais de jogar.
  * 🟠 **Laranja Vibrante** (`#FF6D00` / `#803700`): Destaques, jogo dos balões e botões secundários.
  * 🔵 **Azul Vibrante** (`#00B0FF` / `#004B87`): Área do céu dos balões, ranking e cabeçalhos.
  * 🟡 **Amarelo Vibrante** (`#FFD600` / `#806B00`): Badges, estrelas e alertas de incentivo.
* **Tipografia Personalizada**:
  * **Gooddog** (`fonts/good_dog/gooddog.ttf`): Títulos principais, pontuações e botões de grande impacto.
  * **Bingo** (`fonts/bingo/bingo.ttf`): Subtítulos, enunciados, post-its, cards e feedbacks.
  * **Cute Dolphin** (`fonts/cute_dolphin/cute_dolphin.ttf`): Títulos de post-its e categorias.
  * **Nunito** & **Fredoka**: Fontes auxiliares Google Fonts para leitura inclusiva.
* **Física Lúdica**: Cards inclinados em ângulos alternados (-3° a +6°) que se "desentortam" suavemente ao passar o mouse (`hover`) com transições elásticas.

---

## 🕹️ Módulos e Jogos Educacionais

### 1. Sistema de Autenticação & Sessão
* **`registro.php`**: Criação de novas contas com validação de nome, e-mail e confirmação de senha. Criptografia segura via `password_hash(PASSWORD_DEFAULT)`.
* **`login.php`**: Entrada com Nome de Usuário ou E-mail. Suporta re-hash automático e feedback visual amigável.
* **`config/proteger.php`**: Middleware de proteção presente em todas as páginas dinâmicas, bloqueando acessos não autenticados e redirecionando para o login.
* **`logout.php`**: Encerramento seguro da sessão com limpeza de cookies.

### 2. Jogo 01: Complete the Sentence (`jogo_ingles.php` & `temas_ingles.php`)
* **Mecânica**: Quiz de preenchimento de lacunas (*Gap Fill*).
* A frase é dividida em três partes (início, lacuna e final).
* **Dupla Randomização**:
  * Ordem das perguntas: Embaralhada no servidor via `shuffle()` no PHP.
  * Ordem das opções de resposta: Embaralhada no cliente via algoritmo Fisher-Yates no JavaScript.
* Feedback com tremor e cor vermelha ao errar; cor verde, som de acerto e avanço automático ao acertar.

### 3. Jogo 02: Estoure os Balões (`jogo_baloes.php` & `temas_baloes.php`)
* **Mecânica**: Construtor sintático (*Sentence Builder*).
* Cenário de céu azul com nuvens em segundo plano e balões coloridos em movimento contínuo.
* **Motor de Sprites GIF**:
  * Flutuação contínua com ciclagem de 37 frames (75ms).
  * Animação de explosão (*Pop*) em 9 frames ao clicar.
* **Algoritmo de Segurança Anti-Colisão (*Zero Overlap*)**:
  * Distribuição geométrica calibrada (linhas isoladas para até 3 balões, padrão quinconce para 5 balões), impedindo qualquer sobreposição entre palavras.
* Ao selecionar todas as palavras, o sistema valida a ordem correta da frase e calcula o progresso.

### 4. Jogo 03: Montagem de Palavras (`jogo_palavras.php` & `temas_palavras.php`)
* **Mecânica**: Formação de vocabulário e soletração (*Spelling*), estimulando a associação de letras e fonemas.

### 5. Jogo 04: Ouça e Escolha (`jogo_ouvir.php` & `temas_ouvir.php`)
* **Mecânica**: Compreensão auditiva (*Listening*). O aluno ouve a pronúncia correta em inglês e clica na imagem/palavra correspondente.

### 6. Mural de Conquistas & Gamificação (`ranking.php` & `salvar_pontuacao.php`)
* **`ranking.php`**: Quadro interativo "Top Sabidos", listando os melhores alunos ranqueados por acertos, pontuação e precisão (com cálculo de questões respondidas versus acertos).
* **`salvar_pontuacao.php`**: Endpoint assíncrono (JSON) que consolida em tempo real as vitórias e acertos do aluno no banco de dados.

---

## ♿ Recursos de Acessibilidade e Inclusão

Localizados em `js/acessibilidade.js` e `css/acessibilidade.css`:
* **Síntese de Voz (TTS)**: Botão de áudio para ler os comandos na tela para crianças em fase inicial de alfabetização.
* **Alto Contraste**: Ajuste visual para facilitação de leitura por estudantes com baixa visão.
* **Ajuste de Escala de Fonte**: Aumento dinâmico das fontes para leitura confortável.
* **Navegação por Teclado e Foco Visível**: Compatível com leitores de tela e navegação assistiva.

---

## 🛠️ Arquitetura de Software e Tecnologias

* **Back-end**: PHP 8.x (testado em PHP 8.1, 8.2 e 8.3) com extensão nativa `mysqli`.
* **Segurança**: Consultas com *Prepared Statements* (parametrizadas) em 100% dos fluxos de banco, protegendo contra *SQL Injection*.
* **Front-end**:
  * HTML5 semântico.
  * Tailwind CSS (via CDN) com temas customizados.
  * CSS3 modularizado por tela (`css/auth.css`, `css/index_php.css`, `css/jogo_baloes.css`, `css/ranking.css`, etc.).
  * Vanilla JavaScript (Zero dependências pesadas, alta velocidade).
* **Sessões HTTP**: Gerenciamento de estado por `$_SESSION` do PHP vinculada a cookies de sessão isolados.

---

## 🗄️ Banco de Dados (MySQL / MariaDB)

* **Nome padrão do banco**: `tccdb`
* **Charset**: `utf8mb4` com collation `utf8mb4_unicode_ci`

### Principais Tabelas:
1. **`usuario`**: Armazena as credenciais e o histórico de pontuação do aluno.
   * `id` (INT AUTO_INCREMENT PRIMARY KEY)
   * `nome` (VARCHAR)
   * `senha` (VARCHAR — Hash Bcrypt)
   * `email` (VARCHAR)
   * `pontuacao` (INT DEFAULT 0)
   * `questoesRespondidas` (INT DEFAULT 0)
   * `acertos` (INT DEFAULT 0)
2. **`Jogo`**: Catálogo dos 4 jogos da plataforma (`id`, `nome`, `descricao`).
3. **`Tema`**: Categorias temáticas (animals, colors, food, daily life, nature, etc.) vinculadas aos jogos.
4. **`Pergunta`**: Perguntas em formato de lacuna do Jogo 01.
5. **`Conjunto de respostas erradas`**: Distratores armazenados em formato JSON por pergunta.
6. **`Frases`**: Frases completas em inglês decompostas dinamicamente para o Jogo dos Balões.
7. **`palavras`**: Dicionário de vocabulário e traduções para o Jogo 03.

---

## 🐧 Compatibilidade com Servidores Linux & Apache

O sistema foi especialmente refatorado para garantir execução sem falhas tanto no Windows (Laragon) quanto em hospedagens Linux (Ubuntu, Debian, cPanel, Cloud Linux):

1. **Nomes de Arquivos em `snake_case`**:
   * Todos os nomes de arquivos PHP, CSS e assets usam estritamente letras minúsculas e sublinhados (`_`), prevenindo falhas de arquivo não encontrado em sistemas de arquivos *case-sensitive* (`ext4`).
2. **Resolução Dinâmica de Tabelas (`db_table`)**:
   * Em `config/config.php`, a função `db_table($conn, $tabela)` detecta dinamicamente as tabelas existentes via `SHOW TABLES`, eliminando problemas de maiúsculas/minúsculas no MySQL Linux (`lower_case_table_names=0`) e fornecendo fallback automático entre `usuario` e `usuarios`.
3. **Proteção contra Falha de Favicon**:
   * Todos os cabeçalhos HTML contêm a tag `<link rel="icon">` via Data URI SVG.
   * O `.htaccess` intercepta requisições automáticas para `favicon.ico`, evitando que o servidor execute `index.php` ou crie loops com `proteger.php`.
4. **Prioridade de Arquivo (`DirectoryIndex`)**:
   * O `.htaccess` define `DirectoryIndex index.php index.html`, garantindo que o servidor entregue a versão dinâmica e logada em vez do protótipo estático.

---

## 🚀 Instalação e Execução Local

### Pré-requisitos
* **Laragon** (recomendado para Windows), **XAMPP** ou ambiente com **PHP 8.1+** e **MySQL 8.x**.

### Passo a Passo
1. Clone ou extraia este repositório dentro da pasta web do seu servidor:
   * No Laragon: `C:\laragon\www\Tcc-Projeto`
   * No XAMPP: `C:\xampp\htdocs\Tcc-Projeto`
2. Inicie os serviços do **Apache** e **MySQL**.
3. Acesse o **phpMyAdmin** (`http://localhost/phpmyadmin`):
   * Crie o banco de dados `tccdb`.
   * Importe o script [banco/tccdb_hosting.sql](file:///c:/Users/sesi2b/laragon/www/Tcc-Projeto/banco/tccdb_hosting.sql).
   * Importe a carga de perguntas e frases [banco/inserts.sql](file:///c:/Users/sesi2b/laragon/www/Tcc-Projeto/banco/inserts.sql).
4. Abra o navegador e acesse:
   ```text
   http://localhost/Tcc-Projeto/login.php
   ```

---

## ☁️ Implantação em Hospedagem Externa (cPanel / VPS)

1. Faça o upload de todos os arquivos do projeto para o diretório `public_html` da sua hospedagem.
2. No painel da hospedagem (cPanel / Hostinger / etc.), crie um banco MySQL e um usuário com permissões completas.
3. Importe o arquivo [banco/tccdb_hosting.sql](file:///c:/Users/sesi2b/laragon/www/Tcc-Projeto/banco/tccdb_hosting.sql) no phpMyAdmin da hospedagem.
4. Ajuste as credenciais do banco em `config/config.php` (ou configure variáveis de ambiente no painel):
   * `DB_HOST`: normalmente `localhost`
   * `DB_USER`: usuário do banco criado no painel
   * `DB_PASS`: senha do usuário
   * `DB_NAME`: nome do banco com o prefixo da hospedagem
5. **Ajuste na Tabela Usuario (se já possuir dados legados)**:
   Caso o banco já possua a tabela legada, execute:
   ```sql
   ALTER TABLE `usuario`
     ADD COLUMN IF NOT EXISTS `pontuacao` INT NOT NULL DEFAULT 0,
     ADD COLUMN IF NOT EXISTS `questoesRespondidas` INT NOT NULL DEFAULT 0,
     ADD COLUMN IF NOT EXISTS `acertos` INT NOT NULL DEFAULT 0;
   ```

---

## 📁 Estrutura de Pastas do Repositório

```text
Tcc-Projeto/
├── .htaccess                      # Configuração Apache: DirectoryIndex e tratamento de favicon
├── README.md                      # Esta documentação completa do projeto
├── RESUMO_PROJETO.txt             # Histórico de desenvolvimento e decisões arquiteturais
├── DESIGN.md                      # Especificação do Design System e tokens de design
│
├── index.php                      # Página principal (Dashboard pós-login com 3 cards centrais)
├── index.html                     # Protótipo estático original (preservado como referência)
├── login.php                      # Tela de login estilizada em caderno escolar
├── registro.php                   # Tela de cadastro com validações e hash de senha
├── logout.php                     # Script de encerramento de sessão
├── ranking.php                    # Mural de pontuações "Top Sabidos"
├── salvar_pontuacao.php           # API de gravação de pontuações e estatísticas
│
├── temas_ingles.php               # Seleção de temas do Jogo 01 (Post-its lúdicos)
├── jogo_ingles.php                # Jogo 01: Complete the Sentence (Gap Fill Quiz)
├── temas_baloes.php               # Seleção de temas do Jogo 02 (Post-its lúdicos)
├── jogo_baloes.php                # Jogo 02: Estoure os Balões (Sentence Builder)
├── temas_palavras.php             # Seleção de temas do Jogo 03
├── jogo_palavras.php              # Jogo 03: Montagem de Palavras (Spelling)
├── temas_ouvir.php                # Seleção de temas do Jogo 04
├── jogo_ouvir.php                 # Jogo 04: Ouça e Escolha (Listening)
│
├── config/
│   ├── config.php                 # Conexão MySQL com suporte a env vars e função db_table()
│   └── proteger.php               # Middleware de verificação de autenticação
│
├── banco/
│   ├── tccdb_hosting.sql          # Estrutura completa para criação no servidor/hosting
│   ├── usuario_migration.sql      # Script de criação e migração não-destrutiva da tabela usuario
│   ├── inserts.sql                # Seed com perguntas, distratores e frases
│   ├── jogo_ingles_migration.sql  # Migração incremental do quiz de inglês
│   └── palavras_migration.sql     # Migração incremental do jogo de palavras
│
├── css/
│   ├── auth.css                   # Estilos visuais das páginas de login e cadastro
│   ├── index_php.css              # Estilos do caderno escolar da página inicial
│   ├── temas_ingles.css           # Estilos dos post-its inclinados
│   ├── jogo_ingles.css            # Estilos das lacunas e cartões do quiz
│   ├── jogo_baloes.css            # Estilos do céu, nuvens e slots de palavras
│   ├── ranking.css                # Estilos do pódio e cards do ranking
│   └── acessibilidade.css         # Estilos inclusivos e alto contraste
│
├── js/
│   └── acessibilidade.js          # Módulo de acessibilidade e leitura por voz (TTS)
│
├── fonts/                         # Tipografias infantis personalizadas
│   ├── good_dog/gooddog.ttf
│   ├── bingo/bingo.ttf
│   ├── cute_dolphin/cute_dolphin.ttf
│   └── child_dream_2/dream.ttf
│
├── baloes/                        # Sprites e animações GIF dos balões coloridos
│   ├── cores/ (amarelo, laranja, rosa, verde, vermelho)
│   └── pop.gif (explosão)
│
├── post_it/                       # Assets gráficos de post-its coloridos (1.png a 6.png)
├── fundo_resposta_completar/      # Fitas de papel decorativas para as lacunas
└── mesa/                          # Textura de fundo e mesa do caderno
```

---

## 👨‍💻 Autoria & Créditos
* **Projeto**: Sketchbook Educacional Interativo — English for Kids
* **Finalidade**: Trabalho de Conclusão de Curso (TCC) — Ensino Fundamental 1
* **Instituição**: SENAI
* **Ambiente de Desenvolvimento**: Laragon / PHP 8.x / MySQL 8.x
