-- ==============================================================================
-- banco/atualizar_temas_e_usuarios.sql
-- 1. Permite que múltiplos usuários possam cadastrar o mesmo nome (remove UNIQUE em nome).
-- 2. Mantém unicidade exclusivamente no email (identificador único da conta).
-- 3. Padroniza os temas em todos os jogos para APENAS:
--    - ID 7: animals
--    - ID 8: sports
--    - ID 9: school
--    - ID 10: family
-- ==============================================================================

USE `tccdb`;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. PERMITIR MESMO NOME (Remove restrição de nome único se existir)
-- ------------------------------------------------------------------------------
-- Remove o índice UNIQUE na coluna nome (caso tenha sido criado)
ALTER TABLE `usuario` DROP INDEX `uk_usuario_nome`;
-- ALTER TABLE `usuarios` DROP INDEX `uk_usuario_nome`;

-- Garante que apenas o e-mail seja único para diferenciar as contas
ALTER TABLE `usuario` ADD UNIQUE KEY `uk_usuario_email` (`email`);


-- ------------------------------------------------------------------------------
-- 2. PADRONIZAÇÃO DOS 4 TEMAS OFICIAIS (IDs 7, 8, 9 e 10)
-- ------------------------------------------------------------------------------
-- Insere ou atualiza os 4 temas oficiais sem restrição de id_jogo (id_jogo = NULL)
INSERT INTO `Tema` (`id`, `id_jogo`, `nome`) VALUES
(7, NULL, 'animals'),
(8, NULL, 'sports'),
(9, NULL, 'school'),
(10, NULL, 'family')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `id_jogo` = NULL;

-- Atualiza dados de frases, perguntas e palavras para os novos IDs caso venham de 1, 2, 3, 4
UPDATE `Frases` SET `id_tema` = 7 WHERE `id_tema` = 1;
UPDATE `Frases` SET `id_tema` = 8 WHERE `id_tema` = 2;
UPDATE `Frases` SET `id_tema` = 9 WHERE `id_tema` = 3;
UPDATE `Frases` SET `id_tema` = 10 WHERE `id_tema` = 4;

UPDATE `Pergunta` SET `id_tema` = 7 WHERE `id_tema` = 1;
UPDATE `Pergunta` SET `id_tema` = 8 WHERE `id_tema` = 2;
UPDATE `Pergunta` SET `id_tema` = 9 WHERE `id_tema` = 3;
UPDATE `Pergunta` SET `id_tema` = 10 WHERE `id_tema` = 4;

UPDATE `palavras` SET `tema_id` = 7 WHERE `tema_id` = 1;
UPDATE `palavras` SET `tema_id` = 8 WHERE `tema_id` = 2;
UPDATE `palavras` SET `tema_id` = 9 WHERE `tema_id` = 3;
UPDATE `palavras` SET `tema_id` = 10 WHERE `tema_id` = 4;

-- Remove quaisquer outros temas que não sejam os 4 oficiais
DELETE FROM `Tema` WHERE `id` NOT IN (7, 8, 9, 10);

SET FOREIGN_KEY_CHECKS = 1;
