-- ==============================================================================
-- usuario_migration.sql — Estrutura e Migração da Tabela usuario
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
  `acertos` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Se a tabela 'usuarios' existir no seu banco e você desejar migrá-la para 'usuario':
-- RENAME TABLE `usuarios` TO `usuario`;

-- 3. Caso sua tabela 'usuario' seja antiga (com pontuacaoPrimeiroJogo):
-- ALTER TABLE `usuario`
--   ADD COLUMN `pontuacao` INT NOT NULL DEFAULT 0,
--   ADD COLUMN `questoesRespondidas` INT NOT NULL DEFAULT 0,
--   ADD COLUMN `acertos` INT NOT NULL DEFAULT 0;
