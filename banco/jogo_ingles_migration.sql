-- ============================================================
-- MIGRAÇÃO: Jogo de Inglês — Gap Fill Quiz
-- Schema: tccdb
-- Executar no phpMyAdmin ou terminal MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS `tccdb`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE `tccdb`;

-- --------------------------------------------------------
-- Tabela: Jogo (Catálogo)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Jogo` (
  `id`       INT          NOT NULL AUTO_INCREMENT,
  `nome`     VARCHAR(255) NOT NULL,
  `descricao` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Tema
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Tema` (
  `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  `id_jogo` INT,
  `nome` varchar(255) NOT NULL,
  FOREIGN KEY (`id_jogo`) REFERENCES `Jogo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Pergunta (Jogo 1)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Pergunta` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `primeira parte` VARCHAR(255) NULL,
  `resposta`     VARCHAR(100) NOT NULL,
  `segunda parte` VARCHAR(255) NULL,
  `id_tema`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Pergunta_Jogo1`
    FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Conjunto de respostas erradas (Jogo 1)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Conjunto de respostas erradas` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `nome do conjunto` VARCHAR(255) NOT NULL,
  `conjunto`         JSON         NOT NULL,
  `Pergunta_id`      INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Conjunto_Pergunta1`
    FOREIGN KEY (`Pergunta_id`) REFERENCES `Pergunta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: Frases (Jogo 2)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `Frases` (
  `id`      INT          NOT NULL AUTO_INCREMENT,
  `frase`   VARCHAR(255) NOT NULL,
  `id_tema` INT          NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Frases_Tema`
    FOREIGN KEY (`id_tema`) REFERENCES `Tema` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================================
-- Catálogo de Jogos (Sem temas ou frases automáticas)
-- ============================================================
INSERT INTO `Jogo` (`id`, `nome`, `descricao`) VALUES
(1, 'complete sentence', 'Complete the Sentence - English for Kids'),
(2, 'ballons', 'Estoure os Balões - Build the Sentence')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `descricao` = VALUES(`descricao`);

-- ============================================================
-- NOTA:
-- Inserções automáticas de temas e frases foram removidas.
-- Eles serão criados manualmente pelo administrador.
-- ============================================================

-- Tema: Animals (ID 1)
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The tiger is a very strong animal', 1),
('The panda is eating green bamboo', 1),
('The turtle walks very slowly', 1),
('The spider has eight long legs', 1),
('The penguin lives in the cold ice', 1),
('The bear is sleeping in the dark cave', 1),
('The horse is running on the big farm', 1),
('The snake is green and long', 1),
('The dolphin is swimming in the blue ocean', 1),
('The butterfly has beautiful colorful wings', 1),
('The capybara is chilling like a boss', 1),
('The little dog is doing a funny TikTok dance', 1),
('The cat is hiding inside the cardboard box', 1),
('The pink pig loves to play in the mud', 1),
('The zebra has black and white stripes', 1),
('The kangaroo is jumping very high', 1),
('The wise owl is looking at the night sky', 1),
('The little mouse is eating yellow cheese', 1),
('The bee is making sweet honey', 1),
('The shark is the big monster of the sea', 1);

-- Tema: Sports (ID 2)
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The boy is playing soccer in the green field', 2),
('The tall player is playing basketball like an NBA star', 2),
('The girl is swimming very fast in the blue pool', 2),
('The runner is using cool sneakers to win the race', 2),
('The volleyball match is very famous on Instagram', 2),
('The yellow tennis ball is flying over the big net', 2),
('The cool boy is doing a skateboard trick for TikTok', 2),
('The gamer is playing an e-sports tournament on his computer', 2),
('The champion is happy with the shiny gold medal', 2),
('The surfer is riding a giant wave at the beautiful beach', 2),
('The famous soccer player is scoring a beautiful goal', 2),
('The kids are playing ping pong on the big table', 2),
('The karate master is breaking the wood with a fast kick', 2),
('The strong man is lifting heavy weights in the gym', 2),
('The gymnastics girl is jumping very high in the air', 2),
('The bicycle is super fast in the amazing street race', 2),
('The baseball player is hitting the ball with a big bat', 2),
('The snowboarder is sliding down the cold snow mountain', 2),
('The happy fans are making a viral video in the big stadium', 2),
('The boxing champion is training hard for the great fight', 2);

-- Tema: School (ID 3)
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The yellow pencil is inside the case', 3),
('The open book is on the student desk', 3),
('The teacher is writing on the blackboard', 3),
('The heavy backpack is full of notebooks', 3),
('The kids are playing soccer during the break', 3),
('The yellow school bus is waiting outside', 3),
('The blue pen is next to the white eraser', 3),
('The students are using the computer in the lab', 3),
('The physical education class is very fun', 3),
('The girl is drawing a beautiful picture in art class', 3),
('The friends are making a TikTok dance at school', 3),
('The math test is easy like a video game', 3),
('The school bell is ringing for the recess', 3),
('The smart boy is doing his English homework', 3),
('The children are eating pizza in the cafeteria', 3),
('The scissors are for cutting the colorful paper', 3),
('The principal is talking in the big microphone', 3),
('The science project is a cool volcano', 3),
('The dictionary is helping me learn new words', 3),
('The music class is my favorite part of the day', 3);

-- Tema: Family (ID 4)
INSERT INTO `Frases` (`frase`, `id_tema`) VALUES  
('The loving mother is cooking a delicious pizza', 4),
('The cool father is playing a racing video game', 4),
('My big brother is watching a funny video on TikTok', 4),
('The little sister is drawing a colorful rainbow', 4),
('The sweet grandmother is making a big chocolate cake', 4),
('The old grandfather is sleeping on the comfortable sofa', 4),
('The happy family is taking a beautiful photo for Instagram', 4),
('My funny uncle is dancing at the birthday party', 4),
('The young aunt is buying a new toy at the mall', 4),
('My baby cousin is playing with the cute dog', 4),
('The father is driving the big blue car', 4),
('The mother is helping me with my English homework', 4),
('My sister is listening to pop music on her phone', 4),
('The brother is playing soccer in the green park', 4),
('The grandfather is telling an amazing old story', 4),
('The grandmother loves her beautiful flower garden', 4),
('My cousin is wearing a cool superhero t-shirt', 4),
('The whole family is watching a fun movie together', 4),
('My parents are buying sweet ice cream for us', 4),
('The twin brothers are doing a crazy viral dance', 4);

INSERT INTO `Pergunta` (`id`, `primeira parte`, `resposta`, `segunda parte`, `tema_id`) VALUES
-- Tema: Animals (tema_id: 1)
(1, 'The friendly pet ', 'dog', ' barks at the mailman.', 1),
(2, 'The small ', 'cat', ' likes to drink milk and purr.', 1),
(3, 'The large gray ', 'elephant', ' uses its trunk to drink water.', 1),
(4, 'The little golden ', 'fish', ' swims in the bowl.', 1),
(5, 'The colorful ', 'bird', ' flies high in the blue sky.', 1),
(6, 'The tall ', 'giraffe', ' eats leaves from the high trees.', 1),
(7, 'The king of the jungle is the fierce ', 'lion', '.', 1),
(8, 'The funny ', 'monkey', ' loves to eat yellow bananas.', 1),
(9, 'The farm ', 'cow', ' gives us white milk to drink.', 1),
(10, 'You can ride a fast ', 'horse', ' on the farm.', 1),
(11, 'The pink ', 'pig', ' likes to play in the mud.', 1),
(12, 'The fluffy white ', 'rabbit', ' eats orange carrots.', 1),
(13, 'The tiny squeaking ', 'mouse', ' loves to eat cheese.', 1),
(14, 'The long green ', 'snake', ' slithers on the ground.', 1),
(15, 'The slow moving ', 'turtle', ' carries its house on its back.', 1),
(16, 'The big brown ', 'bear', ' sleeps all winter in a cave.', 1),
(17, 'The beautiful ', 'tiger', ' has black and orange stripes.', 1),
(18, 'The black and white ', 'zebra', ' looks like a horse.', 1),
(19, 'The yellow ', 'duck', ' swims in the lake and says quack.', 1),
(20, 'The green ', 'frog', ' jumps very high near the pond.', 1),

-- Tema: Sports (tema_id: 2)
(21, 'In soccer, you kick the ', 'ball', ' to score a goal.', 2),
(22, 'To go swimming, you need a ', 'pool', ' full of water.', 2),
(23, 'In tennis, we use a ', 'racket', ' to hit the ball.', 2),
(24, 'You wear a helmet to ride your ', 'bike', ' safely.', 2),
(25, 'In basketball, you throw the ball into the ', 'hoop', '.', 2),
(26, 'In volleyball, you use your ', 'hands', ' to hit the ball over the net.', 2),
(27, 'When playing baseball, you use a ', 'bat', ' to hit the small ball.', 2),
(28, 'People ', 'run', ' very fast in a marathon race.', 2),
(29, 'You wear big red gloves in a ', 'boxing', ' match.', 2),
(30, 'In golf, you try to put the ball in a small ', 'hole', '.', 2),
(31, 'In table tennis, the ball bounces on a green ', 'table', '.', 2),
(32, 'You use a board to surf on the big ocean ', 'waves', '.', 2),
(33, 'A soccer team has eleven ', 'players', ' on the green field.', 2),
(34, 'Gymnasts love to run, stretch, and ', 'jump', ' very high.', 2),
(35, 'You need a lot of cold snow to go ', 'skiing', '.', 2),
(36, 'The person who catches the soccer ball is the ', 'goalkeeper', '.', 2),
(37, 'In judo, you tie a colored ', 'belt', ' around your waist.', 2),
(38, 'A large grassy area to play sports is called a ', 'field', '.', 2),
(39, 'To play ice hockey, you need to slide on the ', 'ice', '.', 2),
(40, 'When you win first place, you get a gold ', 'medal', '!', 2),

-- Tema: School (tema_id: 3)
(41, 'We read stories from a big ', 'book', ' during reading time.', 3),
(42, 'The ', 'teacher', ' writes lessons on the whiteboard.', 3),
(43, 'I use a yellow ', 'pencil', ' to write in my notebook.', 3),
(44, 'We sit on a wooden ', 'chair', ' at our desk.', 3),
(45, 'I carry all my school supplies in a ', 'backpack', '.', 3),
(46, 'You use an ', 'eraser', ' to clean your mistakes.', 3),
(47, 'I need a blue ', 'pen', ' to write my name in ink.', 3),
(48, 'We put our books and papers on top of the ', 'desk', '.', 3),
(49, 'To draw straight lines, I use a plastic ', 'ruler', '.', 3),
(50, 'I write all my homework inside my spiral ', 'notebook', '.', 3),
(51, 'We cut colored paper with a pair of ', 'scissors', '.', 3),
(52, 'The teacher uses white ', 'chalk', ' on the old blackboard.', 3),
(53, 'We look at the smart ', 'board', ' to see the pictures.', 3),
(54, 'Our desks and chairs are inside the ', 'classroom', '.', 3),
(55, 'A child who goes to school to learn is a ', 'student', '.', 3),
(56, 'After school, we have to do our ', 'homework', ' at home.', 3),
(57, 'We learn how to count numbers in ', 'math', ' class.', 3),
(58, 'We paint colorful pictures in ', 'art', ' class.', 3),
(59, 'We sing happy songs in ', 'music', ' class.', 3),
(60, 'We learn about plants and animals in ', 'science', ' class.', 3),

-- Tema: Family (tema_id: 4)
(61, 'My mother and father are my loving ', 'parents', '.', 4),
(62, 'The father of my dad is my ', 'grandfather', '.', 4),
(63, 'The sister of my mom is my ', 'aunt', '.', 4),
(64, 'My parents'' son is my ', 'brother', '.', 4),
(65, 'The baby girl of my parents is my ', 'sister', '.', 4),
(66, 'The woman who gave birth to me is my ', 'mother', '.', 4),
(67, 'The man who is married to my mom is my ', 'father', '.', 4),
(68, 'The brother of my dad is my ', 'uncle', '.', 4),
(69, 'The mother of my dad is my ', 'grandmother', '.', 4),
(70, 'The child of my aunt is my ', 'cousin', '.', 4),
(71, 'A very young crying child is a ', 'baby', '.', 4),
(72, 'My parents, siblings, and I make a happy ', 'family', '.', 4),
(73, 'A boy is the ', 'son', ' of his mom and dad.', 4),
(74, 'A girl is the ', 'daughter', ' of her mom and dad.', 4),
(75, 'My brother and I are the ', 'children', ' of our parents.', 4),
(76, 'My grandma and grandpa together are my ', 'grandparents', '.', 4),
(77, 'Two brothers born on the exact same day are ', 'twins', '.', 4),
(78, 'The woman married to my uncle is his ', 'wife', '.', 4),
(79, 'The man married to my aunt is her ', 'husband', '.', 4),
(80, 'My dad''s mom and dad are my ', 'grandparents', '.', 4);

INSERT INTO `Conjunto de respostas erradas` (`id`, `nome do conjunto`, `conjunto`, `Pergunta_id`) VALUES
-- Tema: Animals
(1, 'animals', '["cat","horse","cow","pig"]', 1),
(2, 'animals', '["dog","bird","horse","cow"]', 2),
(3, 'animals', '["mouse","cat","dog","rabbit"]', 3),
(4, 'animals', '["bird","cat","dog","horse"]', 4),
(5, 'animals', '["dog","cat","fish","cow"]', 5),
(6, 'animals', '["dog","cat","pig","mouse"]', 6),
(7, 'animals', '["rabbit","turtle","fish","bird"]', 7),
(8, 'animals', '["cat","dog","horse","cow"]', 8),
(9, 'animals', '["lion","tiger","monkey","bird"]', 9),
(10, 'animals', '["fish","turtle","snake","frog"]', 10),
(11, 'animals', '["lion","tiger","bear","wolf"]', 11),
(12, 'animals', '["lion","tiger","bear","wolf"]', 12),
(13, 'animals', '["horse","cow","pig","sheep"]', 13),
(14, 'animals', '["dog","cat","bird","fish"]', 14),
(15, 'animals', '["lion","tiger","horse","cow"]', 15),
(16, 'animals', '["rabbit","mouse","frog","duck"]', 16),
(17, 'animals', '["dog","cat","pig","cow"]', 17),
(18, 'animals', '["lion","tiger","monkey","bear"]', 18),
(19, 'animals', '["lion","tiger","horse","cow"]', 19),
(20, 'animals', '["lion","tiger","bear","wolf"]', 20),

-- Tema: Sports
(21, 'sports', '["shoe","car","book","apple"]', 21),
(22, 'sports', '["bed","car","tree","house"]', 22),
(23, 'sports', '["shoe","book","pencil","hat"]', 23),
(24, 'sports', '["car","boat","train","airplane"]', 24),
(25, 'sports', '["tree","window","car","door"]', 25),
(26, 'sports', '["feet","knees","eyes","teeth"]', 26),
(27, 'sports', '["book","shoe","racket","pillow"]', 27),
(28, 'sports', '["sleep","fly","sing","eat"]', 28),
(29, 'sports', '["swimming","sleeping","reading","cooking"]', 29),
(30, 'sports', '["tree","window","car","cloud"]', 30),
(31, 'sports', '["bed","chair","wall","door"]', 31),
(32, 'sports', '["street","house","desert","forest"]', 32),
(33, 'sports', '["dogs","cars","trees","apples"]', 33),
(34, 'sports', '["sleep","cry","cook","read"]', 34),
(35, 'sports', '["swimming","surfing","sleeping","shopping"]', 35),
(36, 'sports', '["teacher","doctor","singer","driver"]', 36),
(37, 'sports', '["hat","shoe","sock","glove"]', 37),
(38, 'sports', '["kitchen","bathroom","bedroom","store"]', 38),
(39, 'sports', '["fire","sand","grass","mud"]', 39),
(40, 'sports', '["apple","chair","sock","potato"]', 40),

-- Tema: School
(41, 'school', '["shoe","apple","car","dog"]', 41),
(42, 'school', '["doctor","farmer","singer","pilot"]', 42),
(43, 'school', '["shoe","tree","apple","plate"]', 43),
(44, 'school', '["cloud","flower","water","bird"]', 44),
(45, 'school', '["refrigerator","window","television","bed"]', 45),
(46, 'school', '["spoon","fork","apple","cat"]', 46),
(47, 'school', '["car","dog","tree","apple"]', 47),
(48, 'school', '["cloud","flower","water","bird"]', 48),
(49, 'school', '["banana","fish","hat","sock"]', 49),
(50, 'school', '["shoe","apple","car","dog"]', 50),
(51, 'school', '["cats","shoes","apples","books"]', 51),
(52, 'school', '["milk","water","juice","bread"]', 52),
(53, 'school', '["shoe","apple","car","dog"]', 53),
(54, 'school', '["bathroom","moon","hospital","supermarket"]', 54),
(55, 'school', '["doctor","farmer","singer","pilot"]', 55),
(56, 'school', '["sleeping","running","eating","swimming"]', 56),
(57, 'school', '["sleeping","running","eating","swimming"]', 57),
(58, 'school', '["gym","lunch","sleeping","running"]', 58),
(59, 'school', '["math","science","eating","sleeping"]', 59),
(60, 'school', '["food","animal","toy","color"]', 60),

-- Tema: Family
(61, 'family', '["dogs","cats","birds","fishes"]', 61),
(62, 'family', '["hat","shoe","spoon","plate"]', 62),
(63, 'family', '["horse","door","window","table"]', 63),
(64, 'family', '["chair","fish","tree","cloud"]', 64),
(65, 'family', '["car","boat","flower","moon"]', 65),
(66, 'family', '["apple","banana","orange","grape"]', 66),
(67, 'family', '["car","dog","book","shoe"]', 67),
(68, 'family', '["cat","bird","apple","pencil"]', 68),
(69, 'family', '["dog","cat","house","car"]', 69),
(70, 'family', '["book","pen","desk","ruler"]', 70),
(71, 'family', '["car","house","tree","star"]', 71),
(72, 'family', '["book","shoe","hat","sock"]', 72),
(73, 'family', '["cat","dog","bird","fish"]', 73),
(74, 'family', '["car","bus","train","plane"]', 74),
(75, 'family', '["apples","chairs","shoes","cars"]', 75),
(76, 'family', '["cats","dogs","cars","trees"]', 76),
(77, 'family', '["apples","chairs","shoes","cars"]', 77),
(78, 'family', '["cat","dog","bird","fish"]', 78),
(79, 'family', '["apple","banana","car","dog"]', 79),
(80, 'family', '["cats","dogs","cars","trees"]', 80);
