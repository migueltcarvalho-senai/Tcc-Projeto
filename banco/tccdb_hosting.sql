-- ==============================================================================
-- PROJETO TCC: SKETCHBOOK EDUCACIONAL INTERATIVO (ENSINO FUNDAMENTAL 1)
-- Script Completo para Implantação em Hospedagem Externa (Hosting / Nuvem / cPanel)
-- ==============================================================================
--
-- GUIA DE INSTALAÇÃO EM HOSPEDAGEM EXTERNA:
-- 1. Se sua hospedagem permitir criação de banco via script (ex: VPS, Localhost, RDS):
--    - Mantenha as linhas CREATE DATABASE e USE abaixo ativas.
--
-- 2. Se for Hospedagem Compartilhada (cPanel / Hostinger / InfinityFree / Locaweb):
--    - O painel geralmente cria o banco com um prefixo (ex: u123456_tccdb).
--    - Nesse caso, selecione o banco já criado no phpMyAdmin e importe este arquivo.
--    - Comente as linhas "CREATE DATABASE" e "USE" se o phpMyAdmin acusar erro de privilégio (#1044).
--
-- 3. Após importar:
--    - Atualize as credenciais no arquivo 'config/config.php' da aplicação:
--      $host = "servidor_do_hosting"; (geralmente localhost na maioria dos cPanels)
--      $port = "3306"; (porta padrão de hosting; troque se o host usar outra)
--      $user = "seu_usuario_no_hosting";
--      $pass = "sua_senha_segura";
--      $db   = "seu_banco_no_hosting";
--
-- ==============================================================================

-- Configurações de Sessão e Charset Universal (compatível com MySQL 5.7+, 8.0+ e MariaDB)
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ------------------------------------------------------------------------------
-- CRIAÇÃO DO BANCO DE DADOS (Opcional se já criado pelo painel da hospedagem)
-- ------------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `tccdb`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `tccdb`;

-- ------------------------------------------------------------------------------
-- 1. TABELA: usuario (Autenticação / Acesso)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. TABELA: Jogo (Catálogo de Jogos do Sketchbook)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `Jogo`;
CREATE TABLE IF NOT EXISTS `Jogo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. TABELA: Tema (Categorias temáticas dos jogos)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `Tema`;
CREATE TABLE IF NOT EXISTS `Tema` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_jogo` INT DEFAULT NULL,
  `nome` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Tema_Jogo` (`id_jogo`),
  CONSTRAINT `fk_Tema_Jogo` FOREIGN KEY (`id_jogo`) REFERENCES `Jogo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. TABELA: Pergunta (Jogo 1: Complete the Sentence - Gap Fill Quiz)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `Pergunta`;
CREATE TABLE IF NOT EXISTS `Pergunta` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `primeira parte` VARCHAR(255) DEFAULT NULL,
  `resposta` VARCHAR(100) NOT NULL,
  `segunda parte` VARCHAR(255) DEFAULT NULL,
  `id_tema` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Pergunta_Tema` (`id_tema`),
  CONSTRAINT `fk_Pergunta_Tema` FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. TABELA: Conjunto de respostas erradas (Distratores do Jogo 1)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `Conjunto de respostas erradas`;
CREATE TABLE IF NOT EXISTS `Conjunto de respostas erradas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome do conjunto` VARCHAR(255) NOT NULL,
  `conjunto` JSON NOT NULL,
  `Pergunta_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Conjunto_Pergunta` (`Pergunta_id`),
  CONSTRAINT `fk_Conjunto_Pergunta` FOREIGN KEY (`Pergunta_id`) REFERENCES `Pergunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. TABELA: Frases (Jogo 2: Estoure os Balões - Sentence Builder)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `Frases`;
CREATE TABLE IF NOT EXISTS `Frases` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `frase` VARCHAR(500) NOT NULL,
  `id_tema` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Frases_Tema` (`id_tema`),
  CONSTRAINT `fk_Frases_Tema` FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- CARGA DE DADOS INICIAIS (SEEDING)
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- Dados da Tabela: Jogo
-- ------------------------------------------------------------------------------
INSERT INTO `Jogo` (`id`, `nome`, `descricao`) VALUES
(1, 'complete sentence', 'Complete the Sentence - English for Kids'),
(2, 'ballons', 'Estoure os Balões - Build the Sentence')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `descricao` = VALUES(`descricao`);

-- ------------------------------------------------------------------------------
-- Dados da Tabela: Tema
-- ------------------------------------------------------------------------------
INSERT INTO `Tema` (`id`, `id_jogo`, `nome`) VALUES
-- Temas do Jogo 1: Complete the Sentence (id_jogo = 1)
(1, 1, 'animals'),
(2, 1, 'colors'),
(3, 1, 'food'),
(4, 1, 'school'),
(5, 1, 'family'),
(6, 1, 'sports'),
-- Temas do Jogo 2: Estoure os Balões (id_jogo = 2)
(7, 2, 'daily life'),
(8, 2, 'nature'),
(9, 2, 'animals'),
(10, 2, 'food')
ON DUPLICATE KEY UPDATE `id_jogo` = VALUES(`id_jogo`), `nome` = VALUES(`nome`);

-- ------------------------------------------------------------------------------
-- Dados da Tabela: Pergunta (Tema 1: Animals - Jogo 1)
-- ------------------------------------------------------------------------------
INSERT INTO `Pergunta` (`id`, `primeira parte`, `resposta`, `segunda parte`, `id_tema`) VALUES
(1, 'I have a', 'golden fish', 'at my aquarium.', 1),
(2, 'My grandpa''s', 'cow', 'always does MOO MOO.', 1),
(3, 'The', 'Chicken', 'always goes CÓÓ CÓÓ at the farm.', 1),
(4, 'My uncle will give me a strong black', 'horse', '.', 1),
(5, 'We will ride a', 'horse', 'together!', 1),
(6, 'The fluffy long-eared', 'rabbit', 'loves eating fresh carrots.', 1),
(7, 'The ferocious male', 'lion', 'with a big mane roars in the savanna.', 1),
(8, 'The long-necked', 'giraffe', 'eats leaves from the highest tree branches.', 1),
(9, 'The tiny squeaking', 'mouse', 'nibbles on a small piece of cheese.', 1),
(10, 'My friendly pet', 'dog', 'barks happily and wags its tail.', 1),
(11, 'The agile', 'monkey', 'swings between branches and peels bananas.', 1),
(12, 'The massive gray', 'elephant', 'uses its long trunk to spray water.', 1),
(13, 'The black and white striped', 'zebra', 'grazes on the savanna grass.', 1),
(14, 'The purring', 'cat', 'drinks milk and chases laser lights.', 1),
(15, 'The slow-moving', 'turtle', 'retracts its head into its hard shell.', 1),
(16, 'The wild gray', 'wolf', 'howls at the full moon with its pack.', 1),
(17, 'The colorful feathered', 'parrot', 'mimics human speech and words.', 1),
(18, 'The furry brown', 'bear', 'catches salmon and hibernates in a cave.', 1),
(19, 'The intelligent aquatic', 'dolphin', 'leaps high out of the ocean water.', 1),
(20, 'The spotted', 'cheetah', 'is the fastest land animal on Earth.', 1)
ON DUPLICATE KEY UPDATE
  `primeira parte` = VALUES(`primeira parte`),
  `resposta` = VALUES(`resposta`),
  `segunda parte` = VALUES(`segunda parte`),
  `id_tema` = VALUES(`id_tema`);

-- ------------------------------------------------------------------------------
-- Dados da Tabela: Conjunto de respostas erradas (Distratores do Tema 1 - Jogo 1)
-- ------------------------------------------------------------------------------
INSERT INTO `Conjunto de respostas erradas` (`id`, `nome do conjunto`, `conjunto`, `Pergunta_id`) VALUES
(1, 'Animais comuns', '["horse","cow","dog","rabbit"]', 1),
(2, 'Animais comuns', '["dog","cat","chair","tree"]', 2),
(3, 'Animais comuns', '["bulldog","squirrel","apple","otter"]', 3),
(4, 'Animais comuns', '["chair","door","tree","ground"]', 4),
(5, 'Animais comuns', '["dog","bear","seal","soup"]', 5),
(6, 'Animais comuns', '["keyboard","refrigerator","brick"]', 6),
(7, 'Animais comuns', '["airplane","bathtub","stapler"]', 7),
(8, 'Animais comuns', '["toaster","hammer","pillow"]', 8),
(9, 'Animais comuns', '["umbrella","flashlight","microwave"]', 9),
(10, 'Animais comuns', '["calculator","curtain","screwdriver"]', 10),
(11, 'Animais comuns', '["suitcase","mirror","bicycle"]', 11),
(12, 'Animais comuns', '["bookshelf","television","wallet"]', 12),
(13, 'Animais comuns', '["laptop","armchair","frying pan"]', 13),
(14, 'Animais comuns', '["dishwasher","carpet","traffic light"]', 14),
(15, 'Animais comuns', '["scissors","headphone","stapler"]', 15),
(16, 'Animais comuns', '["telescope","sofa","doorbell"]', 16),
(17, 'Animais comuns', '["skateboard","lawnmower","notebook"]', 17),
(18, 'Animais comuns', '["chandelier","pencil","mattress"]', 18),
(19, 'Animais comuns', '["radiator","typewriter","broom"]', 19),
(20, 'Animais comuns', '["microwave","printer","bookshelf"]', 20)
ON DUPLICATE KEY UPDATE
  `nome do conjunto` = VALUES(`nome do conjunto`),
  `conjunto` = VALUES(`conjunto`),
  `Pergunta_id` = VALUES(`Pergunta_id`);

-- ------------------------------------------------------------------------------
-- Dados da Tabela: Frases (Jogo 2: Estoure os Balões - Sentence Builder)
-- ------------------------------------------------------------------------------
INSERT INTO `Frases` (`id`, `frase`, `id_tema`) VALUES
-- Tema 7: daily life
(1, 'The cat is sleeping', 7),
(2, 'I go to school', 7),
(3, 'She reads a book', 7),
(4, 'We play every day', 7),
(5, 'He drinks cold water', 7),
-- Tema 8: nature
(6, 'The sun is bright', 8),
(7, 'Birds fly very high', 8),
(8, 'Flowers grow in spring', 8),
(9, 'The river is cold', 8),
(10, 'Trees have green leaves', 8),
-- Tema 9: animals
(11, 'The dog runs fast', 9),
(12, 'Cats like warm milk', 9),
(13, 'Fish swim in water', 9),
(14, 'The rabbit eats carrots', 9),
(15, 'Birds sing every morning', 9),
-- Tema 10: food
(16, 'I like red apples', 10),
(17, 'She eats hot soup', 10),
(18, 'We drink orange juice', 10),
(19, 'He wants more cake', 10),
(20, 'Rice and beans please', 10)
ON DUPLICATE KEY UPDATE
  `frase` = VALUES(`frase`),
  `id_tema` = VALUES(`id_tema`);

-- Reativa a verificação de integridade referencial
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- FIM DO SCRIPT DE MIGRAÇÃO PARA HOSPEDAGEM
-- ==============================================================================
