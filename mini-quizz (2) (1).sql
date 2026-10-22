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
(20, 5, 'Golden Experience', 0);

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
(5, 1, 'Comment s\'appelle le Stand de Jean-Pierre Polnareff ?', 1, 5);

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
(2, 'Titre du second quiz', 'Description du second quiz');

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
  `completed_at` datetime NOT NULL,
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
  MODIFY `option_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `quiz_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quiz_scores`
--
ALTER TABLE `quiz_scores`
  MODIFY `score_id` int NOT NULL AUTO_INCREMENT;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
