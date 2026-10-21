-- ============================================================
-- MIGRAÇÃO FINAL: Jogo de Inglês — Gap Fill Quiz
-- Schema: tccdb
-- Executar no phpMyAdmin ou terminal MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS `tccdb`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE `tccdb`;

-- --------------------------------------------------------
-- Tabela: Jogo
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `usuario`(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  nome INT NOT NULL,
  senha INT NOT NULL,
  email INT NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `Jogo` (
  `id`       INT          NOT NULL AUTO_INCREMENT,
  `nome`     VARCHAR(255)          NOT NULL,
  `descricao` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS Tema (
id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
id_jogo INT,
nome varchar(255) NOT NULL,
FOREIGN KEY (id_jogo) REFERENCES jogo (id)
)ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- --------------------------------------------------------
-- Tabela: Pergunta
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Pergunta` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `primeira parte` VARCHAR(255) NULL,
  `resposta`     VARCHAR(100) NOT NULL,
  `segunda parte` VARCHAR(255) NULL,
  `id_tema`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Pergunta_Jogo1`
    FOREIGN KEY (`id_tema`) REFERENCES `tema` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Conjunto de respostas erradas
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Conjunto de respostas erradas` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `nome do conjunto` VARCHAR(255) NOT NULL,
  `conjunto`         JSON         NOT NULL,
  `Pergunta_id`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Conjunto_Pergunta1`
    FOREIGN KEY (`Pergunta_id`) REFERENCES `Pergunta` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================================
-- DADOS: Jogo 1 — Complete the Sentence (Farm Animals)
-- ============================================================

INSERT INTO `Jogo` (`nome`, `descricao`) VALUES
('complete sentence', 'Complete the Sentence - English for Kids');

INSERT INTO `Tema` (`id`, `id_jogo`, `nome`) VALUES
(1, 1, 'animals'),
(2, 1, 'colors'),
(3, 1, 'food'),
(4, 1, 'school'),
(5, 1, 'family'),
(6, 1, 'sports')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

INSERT INTO `Pergunta` (`id`, `primeira parte`, `resposta`, `segunda parte`, `id_tema`) VALUES
(1, 'I have a',             'golden fish', 'at my aquarium.',                    1),
(2, 'My grandpa''s',        'cow',         'always does MOO MOO.',               1),
(3, 'The',                  'Chicken',     'always goes CÓÓ CÓÓ at the farm.',   1),
(4, 'My uncle will give me a strong black', 'horse', '.',                        1),
(5, 'We will ride a',       'horse',       'together!',                          1),
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
(20, 'The spotted', 'cheetah', 'is the fastest land animal on Earth.', 1);

INSERT INTO `Conjunto de respostas erradas` (`id`, `nome do conjunto`, `conjunto`, `Pergunta_id`) VALUES
(1, 'Animais comuns', '["horse","cow","dog","rabbit"]',        1),
(2, 'Animais comuns', '["dog","cat","chair","tree"]',          2),
(3, 'Animais comuns', '["bulldog","squirrel","apple","otter"]',3),
(4, 'Animais comuns', '["chair","door","tree","ground"]',      4),
(5, 'Animais comuns', '["dog","bear","seal","soup"]',          5),
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
(20, 'Animais comuns', '["microwave","printer","bookshelf"]', 20);

-- ============================================================
-- JOGO 2: Estoure os Balões (Build the Sentence)
-- ============================================================

-- --------------------------------------------------------
-- Dados: Jogo 2 (Estoure os Balões)
-- --------------------------------------------------------
INSERT INTO `Jogo` (`id`, `nome`, `descricao`) VALUES
(2, 'ballons', 'Estoure os Balões - Build the Sentence')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

-- --------------------------------------------------------
-- Dados: Temas do Jogo 2
-- --------------------------------------------------------
INSERT INTO `Tema` (`id`, `id_jogo`, `nome`) VALUES
(7, 2, 'daily life'),
(8, 2, 'nature'),
(9, 2, 'animals'),
(10, 2, 'food')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

-- --------------------------------------------------------
-- Tabela: Frases
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Frases` (
  `id`      INT          NOT NULL AUTO_INCREMENT,
  `frase`   VARCHAR(500) NOT NULL,
  `id_tema` INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Frases_Tema`
    FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Dados: Frases de Exemplo (Jogo 2)
-- --------------------------------------------------------
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES
-- daily life (id_tema=7)
('The cat is sleeping', 7),
('I go to school', 7),
('She reads a book', 7),
('We play every day', 7),
('He drinks cold water', 7),
-- nature (id_tema=8)
('The sun is bright', 8),
('Birds fly very high', 8),
('Flowers grow in spring', 8),
('The river is cold', 8),
('Trees have green leaves', 8),
-- animals (id_tema=9)
('The dog runs fast', 9),
('Cats like warm milk', 9),
('Fish swim in water', 9),
('The rabbit eats carrots', 9),
('Birds sing every morning', 9),
-- food (id_tema=10)
('I like red apples', 10),
('She eats hot soup', 10),
('We drink orange juice', 10),
('He wants more cake', 10),
('Rice and beans please', 10);


