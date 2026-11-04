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
-- 1. TABELA: usuario (Autenticação / Pontuações e Estatísticas Gerais)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `pontuacao` INT NOT NULL DEFAULT 0,
  `questoesRespondidas` INT NOT NULL DEFAULT 0,
  `acertos` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
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

-- ------------------------------------------------------------------------------
-- 7. TABELA: palavras (Jogo 3: Montagem de Palavras - Spell the Word)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `palavras`;
CREATE TABLE IF NOT EXISTS `palavras` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `palavra` VARCHAR(255) NOT NULL,
  `traducao` VARCHAR(255) NOT NULL,
  `tema_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_palavras_tema` (`tema_id`),
  CONSTRAINT `fk_palavras_tema` FOREIGN KEY (`tema_id`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- CARGA DE DADOS INICIAIS (SEEDING)
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- Dados da Tabela: Jogo (Catálogo dos 4 Jogos do Sistema)
-- ------------------------------------------------------------------------------
INSERT INTO `Jogo` (`id`, `nome`, `descricao`) VALUES
(1, 'complete sentence', 'Complete the Sentence - English for Kids'),
(2, 'ballons', 'Estoure os Balões - Build the Sentence'),
(3, 'word spelling', 'Montagem de Palavras - English for Kids'),
(4, 'listen and choose', 'Ouça e Escolha - English for Kids')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `descricao` = VALUES(`descricao`);

-- ------------------------------------------------------------------------------
-- OBSERVAÇÃO:
-- As tabelas `Tema`, `Frases`, `Pergunta` e `palavras` foram criadas sem dados
-- iniciais automáticos (seeders removidos). Os temas e seus respectivos
-- conteúdos devem ser cadastrados manualmente.
-- ------------------------------------------------------------------------------

-- Reativa a verificação de integridade referencial
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- FIM DO SCRIPT DE MIGRAÇÃO PARA HOSPEDAGEM
-- ==============================================================================
