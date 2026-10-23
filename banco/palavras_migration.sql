-- ============================================================
-- palavras_migration.sql — Jogo 03: Montagem de Palavras
-- English for Kids | TCC Sketchbook Educacional
-- Compatível com MySQL Linux (case-sensitive) e Windows
-- ============================================================

SET NAMES utf8mb4;
USE `tccdb`;

-- ============================================================
-- 1. Inserir Jogo 03 (Catálogo de Jogos)
-- ============================================================
INSERT INTO `Jogo` (`id`, `nome`, `descricao`)
VALUES (3, 'word spelling', 'Montagem de Palavras - English for Kids')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `descricao` = VALUES(`descricao`);

-- ============================================================
-- 2. Criar tabela palavras (se não existir)
-- ============================================================
CREATE TABLE IF NOT EXISTS `palavras` (
    `id`       INT          AUTO_INCREMENT PRIMARY KEY,
    `palavra`  VARCHAR(255) NOT NULL,
    `traducao` VARCHAR(255) NOT NULL,
    `tema_id`  INT          NOT NULL,
    CONSTRAINT `fk_palavras_tema`
        FOREIGN KEY (`tema_id`) REFERENCES `Tema`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTA:
-- Temas e palavras foram removidos deste migration.
-- Eles serão cadastrados manualmente conforme solicitado.
-- ============================================================
