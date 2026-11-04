-- ==============================================================================
-- usuario_migration.sql — Estrutura e Migração da Tabela usuario
-- Permite que múltiplos alunos tenham o mesmo nome (apenas email é único)
-- Compatível com Linux (ext4, case-sensitive) e Windows (Laragon)
-- ==============================================================================
SET NAMES utf8mb4;
USE `tccdb`;

-- 1. Criação da tabela oficial `usuario` (se não existir)
CREATE TABLE IF NOT EXISTS `usuario` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `pontuacao` INT NOT NULL DEFAULT 0,
  `questoesRespondidas` INT NOT NULL DEFAULT 0,
  `acertos` INT NOT NULL DEFAULT 0,
  UNIQUE KEY `uk_usuario_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Caso a tabela já possua o índice UNIQUE em 'nome', remova-o:
ALTER TABLE `usuario` DROP INDEX `uk_usuario_nome`;
