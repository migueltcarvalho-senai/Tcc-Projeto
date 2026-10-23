-- ============================================================
-- usuarios_migration.sql — Nova estrutura da tabela usuarios
-- ============================================================
SET NAMES utf8mb4;
USE `tccdb`;

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `pontuacao` INT NOT NULL DEFAULT 0,
  `questoesRespondidas` INT NOT NULL DEFAULT 0,
  `acertos` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
