-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20261014.d250d31a41
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2026 at 12:54 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mini-quizz`
--

-- --------------------------------------------------------

--
-- Table structure for table `answer_options`
--
CREATE TABLE `answer_options` (
  `option_id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `is_correct` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `answer_options`
--

INSERT INTO `answer_options` (`option_id`, `question_id`, `option_text`, `is_correct`) VALUES
(1, 1, 'Au cartes de tarots', 1),
(2, 1, 'A différent titre de musique', 0),
(3, 1, 'Au manieur qui décide du nom', 0),
(4, 1, 'A la force mental du manieur', 0),
(5, 2, 'Yoshikage Kira', 0),
(6, 2, 'Dio Brando', 1),
(7, 2, 'La fondation Speedwagon', 0),
(8, 2, 'Diavolo', 0),
(9, 3, 'La respiration', 0),
(10, 3, 'Les stands', 0),
(11, 3, 'L\'intelligence', 0),
(12, 3, 'L\'onde', 1),
(13, 4, 'Jean-Pierre Polnareff', 0),
(14, 4, 'Rohan Kishibe', 1),
(15, 4, 'Kakyoin Noriaki', 0),
(16, 4, 'César Zeppeli', 0),
(17, 5, 'Silver Chariot', 1),
(18, 5, 'Star Platinum', 0),
(19, 5, 'Killer Queen', 0),
(20, 5, 'Golden Experience', 0),
(21, 6, 'Liyue', 1),
(22, 6, 'Natlan', 0),
(23, 6, 'Nod-Krai', 0),
(24, 6, 'Shneznaya', 0),
(25, 7, 'Fontaine', 1),
(26, 7, 'Mondstad', 0),
(27, 7, 'Sumeru', 0),
(28, 7, 'Inazuma', 0),
(29, 8, '7', 1),
(30, 8, '4', 0),
(31, 8, '10', 0),
(32, 8, '5', 0),
(33, 9, 'Venti', 1),
(34, 9, 'Kahedera Kazuah', 0),
(35, 9, 'Varka', 0),
(36, 9, 'Neuvillete', 0),
(37, 10, 'Un catalyseur', 1),
(38, 10, 'Une épée à une main', 0),
(39, 10, 'Un arc', 0),
(40, 10, 'Une lance', 0),
(41, 11, 'Skull', 0),
(42, 11, 'Fox', 0),
(43, 11, 'Joker', 1),
(44, 11, 'Crow', 0),
(45, 12, 'Les Phantom Thieves', 1),
(46, 12, 'Les Shadow Hunters', 0),
(47, 12, 'Les Velvet Fighters', 0),
(48, 12, 'Les Midnight Heroes', 0),
(49, 13, 'C''est une immense métropole futuriste', 0),
(50, 13, 'C''est une petite ville rurale appelée Inaba', 1),
(51, 13, 'C''est une ville sous-marine', 0),
(52, 13, 'C''est une colonie spatiale', 0),
(53, 14, 'SEES', 1),
(54, 14, 'SWORD', 0),
(55, 14, 'AEGIS', 0),
(56, 14, 'SPECTRE', 0),
(57, 15, 'Morgana', 0),
(58, 15, 'Igor', 1),
(59, 15, 'Ryuji', 0),
(60, 15, 'Yosuke', 0);

-- --------------------------------------------------------

--
-- Table structure for table `attempt_answers`
--
CREATE TABLE `attempt_answers` (
  `answer_id` int NOT NULL,
  `attempt_id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_id` int NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `answered_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--
CREATE TABLE `questions` (
  `question_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `question_text` text NOT NULL,
  `points` int NOT NULL,
  `question_order` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `quiz_id`, `question_text`, `points`, `question_order`) VALUES
(1, 1, 'Dans la partie 3 de JoJo\'s Bizarre Adventure, les noms des Stands sont liés à quoi ?', 1, 1),
(2, 1, 'Qui est l\'ennemi intemporel de la famille Joestar ?', 1, 2),
(3, 1, 'Quel est le nom de la capacité utilisée dans les premières parties ?', 1, 3),
(4, 1, 'Qui est le célèbre mangaka vivant à Morioh ?', 1, 4),
(5, 1, 'Comment s\'appelle le Stand de Jean-Pierre Polnareff ?', 1, 5),
(6, 2, 'Quel est la nation associée au contrat et à la roche ?', 1, 1),
(7, 2, 'Quelle nation a été créée en étant inspirée de la France ?', 1, 2),
(8, 2, 'Combien d\'éléments peuvent être maniés au total ?', 1, 3),
(9, 2, 'Quel personnage est lié à la musique et à la liberté (et à l\'alcoolisme) ?', 1, 4),
(10, 2, 'Quelle arme Charlotte utilise-t-elle ?', 1, 5),
(11, 4, 'Quel est le surnom du protagoniste de Persona 5 dans le Métavers ?', 1, 1),
(12, 4, 'Comment s''appelle le groupe de héros de Persona 5 ?', 1, 2),
(13, 4, 'Quelle est la particularité de la ville où se déroule principalement Persona 4 ?', 1, 3),
(14, 4, 'Quel est le nom de l''organisation à laquelle appartient le protagoniste dans Persona 3 ?', 1, 4),
(15, 4, 'Quel personnage emblématique est associé à la Chambre de Velours dans plusieurs jeux Persona ?', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--
CREATE TABLE `quiz` (
  `quiz_id` int NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--
CREATE TABLE `quizzes` (
  `quiz_id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`quiz_id`, `title`, `description`) VALUES
(1, 'Titre du quiz', 'Description du quiz'),
(2, 'Quiz Genshin Impact', 'Questions sur les nations, les éléments et les personnages.'),
(4, 'Quiz Personna', 'Test de connaissance sur Personna');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--
CREATE TABLE `quiz_attempts` (
  `attempt_id` int NOT NULL,
  `user_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `score` decimal(6,2) NOT NULL,
  `started_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `duration_seconds` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_scores`
--
CREATE TABLE `quiz_scores` (
  `score_id` int NOT NULL,
  `user_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `score` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`) VALUES
(1, 'Dummy'),
(2, 'Doll'),
(3, 'Test');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `answer_options`
--
ALTER TABLE `answer_options`
  ADD PRIMARY KEY (`option_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`quiz_id`);

--
-- Indexes for table `quiz_scores`
--
ALTER TABLE `quiz_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `fk_quiz_scores_user` (`user_id`),
  ADD KEY `fk_quiz_scores_quiz` (`quiz_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `fk_quiz_attempts_user` (`user_id`),
  ADD KEY `fk_quiz_attempts_quiz` (`quiz_id`);

--
-- Indexes for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD UNIQUE KEY `uq_attempt_answers_attempt_question` (`attempt_id`, `question_id`),
  ADD KEY `fk_attempt_answers_question` (`question_id`),
  ADD KEY `fk_attempt_answers_option` (`option_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answer_options`
--
ALTER TABLE `answer_options`
  MODIFY `option_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `quiz_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quiz_scores`
--
ALTER TABLE `quiz_scores`
  MODIFY `score_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `attempt_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  MODIFY `answer_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quiz_scores`
--
ALTER TABLE `quiz_scores`
  ADD CONSTRAINT `fk_quiz_scores_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`),
  ADD CONSTRAINT `fk_quiz_scores_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `fk_quiz_attempts_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`),
  ADD CONSTRAINT `fk_quiz_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD CONSTRAINT `fk_attempt_answers_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`attempt_id`),
  ADD CONSTRAINT `fk_attempt_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`),
  ADD CONSTRAINT `fk_attempt_answers_option` FOREIGN KEY (`option_id`) REFERENCES `answer_options` (`option_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
