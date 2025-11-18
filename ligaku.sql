-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2025 at 10:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ligaku`
--

-- --------------------------------------------------------

--
-- Table structure for table `best_players`
--

CREATE TABLE `best_players` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `rating` decimal(4,2) DEFAULT 0.00,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fouls`
--

CREATE TABLE `fouls` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `minute` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `card` enum('yellow','red') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fouls`
--

INSERT INTO `fouls` (`id`, `match_id`, `player_id`, `team_id`, `minute`, `description`, `card`) VALUES
(8, 27, 104, 42, 65, 'Menendang Kepala', 'yellow'),
(9, 28, 105, 42, 56, 'Menendang Kepala', 'red'),
(10, 29, 142, 44, 56, 'Menendang Kepala', 'red');

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `minute` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `match_id`, `player_id`, `minute`) VALUES
(27, 27, 103, 22),
(28, 27, 103, 33),
(29, 27, 103, 44),
(30, 27, 123, 55),
(31, 27, 118, 66),
(32, 28, 103, 34),
(33, 28, 103, 55),
(34, 28, 120, 77),
(35, 29, 108, 34),
(36, 29, 106, 55);

-- --------------------------------------------------------

--
-- Table structure for table `lineups`
--

CREATE TABLE `lineups` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `is_starting` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lineups`
--

INSERT INTO `lineups` (`id`, `match_id`, `player_id`, `is_starting`, `created_at`) VALUES
(113, 157, 103, 0, '2025-05-18 04:19:01'),
(114, 157, 104, 0, '2025-05-18 04:19:01'),
(115, 157, 105, 0, '2025-05-18 04:19:01'),
(116, 157, 106, 1, '2025-05-18 04:19:01'),
(117, 157, 107, 1, '2025-05-18 04:19:01'),
(118, 157, 108, 1, '2025-05-18 04:19:01'),
(119, 157, 109, 1, '2025-05-18 04:19:01'),
(120, 157, 110, 1, '2025-05-18 04:19:01'),
(121, 157, 111, 1, '2025-05-18 04:19:01'),
(122, 157, 112, 1, '2025-05-18 04:19:01'),
(123, 157, 113, 1, '2025-05-18 04:19:01'),
(124, 157, 114, 1, '2025-05-18 04:19:01'),
(125, 157, 115, 1, '2025-05-18 04:19:01'),
(126, 157, 116, 1, '2025-05-18 04:19:01'),
(127, 157, 117, 1, '2025-05-18 04:19:01'),
(128, 157, 118, 1, '2025-05-18 04:19:01'),
(129, 157, 119, 1, '2025-05-18 04:19:01'),
(130, 157, 120, 0, '2025-05-18 04:19:01'),
(131, 157, 121, 0, '2025-05-18 04:19:01'),
(132, 157, 122, 0, '2025-05-18 04:19:01'),
(133, 157, 123, 1, '2025-05-18 04:19:01'),
(134, 157, 124, 1, '2025-05-18 04:19:01'),
(135, 157, 125, 1, '2025-05-18 04:19:01'),
(136, 157, 126, 1, '2025-05-18 04:19:01'),
(137, 157, 127, 1, '2025-05-18 04:19:01'),
(138, 157, 128, 1, '2025-05-18 04:19:01'),
(139, 157, 129, 1, '2025-05-18 04:19:01'),
(140, 157, 130, 1, '2025-05-18 04:19:01'),
(141, 157, 131, 1, '2025-05-18 04:19:01'),
(142, 157, 132, 1, '2025-05-18 04:19:01'),
(143, 158, 118, 1, '2025-05-18 07:12:25'),
(144, 158, 119, 1, '2025-05-18 07:12:25'),
(145, 158, 120, 1, '2025-05-18 07:12:25'),
(146, 158, 121, 1, '2025-05-18 07:12:25'),
(147, 158, 122, 1, '2025-05-18 07:12:25'),
(148, 158, 123, 1, '2025-05-18 07:12:25'),
(149, 158, 124, 1, '2025-05-18 07:12:25'),
(150, 158, 125, 1, '2025-05-18 07:12:25'),
(151, 158, 126, 1, '2025-05-18 07:12:25'),
(152, 158, 127, 1, '2025-05-18 07:12:25'),
(153, 158, 128, 1, '2025-05-18 07:12:25'),
(154, 158, 129, 0, '2025-05-18 07:12:25'),
(155, 158, 130, 1, '2025-05-18 07:12:25'),
(156, 158, 131, 0, '2025-05-18 07:12:25'),
(157, 158, 132, 0, '2025-05-18 07:12:25'),
(158, 158, 133, 0, '2025-05-18 07:12:25'),
(159, 158, 134, 1, '2025-05-18 07:12:25'),
(160, 158, 135, 0, '2025-05-18 07:12:25'),
(161, 158, 136, 1, '2025-05-18 07:12:25'),
(162, 158, 137, 0, '2025-05-18 07:12:25'),
(163, 158, 138, 1, '2025-05-18 07:12:25'),
(164, 158, 139, 1, '2025-05-18 07:12:25'),
(165, 158, 140, 1, '2025-05-18 07:12:25'),
(166, 158, 141, 1, '2025-05-18 07:12:25'),
(167, 158, 142, 1, '2025-05-18 07:12:25'),
(168, 158, 143, 1, '2025-05-18 07:12:25'),
(169, 158, 144, 1, '2025-05-18 07:12:25'),
(170, 158, 145, 1, '2025-05-18 07:12:25'),
(171, 158, 146, 1, '2025-05-18 07:12:25'),
(172, 158, 147, 1, '2025-05-18 07:12:25'),
(173, 160, 118, 1, '2025-05-18 07:46:23'),
(174, 160, 119, 1, '2025-05-18 07:46:23'),
(175, 160, 120, 1, '2025-05-18 07:46:23'),
(176, 160, 121, 1, '2025-05-18 07:46:23'),
(177, 160, 122, 1, '2025-05-18 07:46:23'),
(178, 160, 123, 1, '2025-05-18 07:46:23'),
(179, 160, 124, 1, '2025-05-18 07:46:23'),
(180, 160, 125, 1, '2025-05-18 07:46:23'),
(181, 160, 126, 1, '2025-05-18 07:46:23'),
(182, 160, 127, 1, '2025-05-18 07:46:23'),
(183, 160, 128, 1, '2025-05-18 07:46:23'),
(184, 160, 129, 1, '2025-05-18 07:46:23'),
(185, 160, 130, 0, '2025-05-18 07:46:23'),
(186, 160, 131, 0, '2025-05-18 07:46:23'),
(187, 160, 132, 0, '2025-05-18 07:46:23'),
(188, 160, 148, 0, '2025-05-18 07:46:23'),
(189, 160, 149, 0, '2025-05-18 07:46:23'),
(190, 160, 150, 0, '2025-05-18 07:46:23'),
(191, 160, 151, 1, '2025-05-18 07:46:23'),
(192, 160, 152, 1, '2025-05-18 07:46:23'),
(193, 160, 153, 1, '2025-05-18 07:46:23'),
(194, 160, 154, 1, '2025-05-18 07:46:23'),
(195, 160, 155, 1, '2025-05-18 07:46:23'),
(196, 160, 156, 1, '2025-05-18 07:46:23'),
(197, 160, 157, 1, '2025-05-18 07:46:23'),
(198, 160, 158, 1, '2025-05-18 07:46:23'),
(199, 160, 159, 1, '2025-05-18 07:46:23'),
(200, 160, 160, 1, '2025-05-18 07:46:23'),
(201, 160, 161, 1, '2025-05-18 07:46:23'),
(202, 160, 162, 1, '2025-05-18 07:46:23'),
(203, 162, 103, 1, '2025-05-18 08:01:59'),
(204, 162, 104, 1, '2025-05-18 08:01:59'),
(205, 162, 105, 1, '2025-05-18 08:01:59'),
(206, 162, 106, 1, '2025-05-18 08:01:59'),
(207, 162, 107, 1, '2025-05-18 08:01:59'),
(208, 162, 108, 1, '2025-05-18 08:01:59'),
(209, 162, 109, 1, '2025-05-18 08:01:59'),
(210, 162, 110, 1, '2025-05-18 08:01:59'),
(211, 162, 111, 1, '2025-05-18 08:01:59'),
(212, 162, 112, 1, '2025-05-18 08:01:59'),
(213, 162, 113, 1, '2025-05-18 08:01:59'),
(214, 162, 114, 1, '2025-05-18 08:01:59'),
(215, 162, 115, 0, '2025-05-18 08:01:59'),
(216, 162, 116, 0, '2025-05-18 08:01:59'),
(217, 162, 117, 0, '2025-05-18 08:01:59'),
(218, 162, 118, 0, '2025-05-18 08:01:59'),
(219, 162, 119, 0, '2025-05-18 08:01:59'),
(220, 162, 120, 0, '2025-05-18 08:01:59'),
(221, 162, 121, 1, '2025-05-18 08:01:59'),
(222, 162, 122, 1, '2025-05-18 08:01:59'),
(223, 162, 123, 1, '2025-05-18 08:01:59'),
(224, 162, 124, 1, '2025-05-18 08:01:59'),
(225, 162, 125, 1, '2025-05-18 08:01:59'),
(226, 162, 126, 1, '2025-05-18 08:01:59'),
(227, 162, 127, 1, '2025-05-18 08:01:59'),
(228, 162, 128, 1, '2025-05-18 08:01:59'),
(229, 162, 129, 1, '2025-05-18 08:01:59'),
(230, 162, 130, 1, '2025-05-18 08:01:59'),
(231, 162, 131, 1, '2025-05-18 08:01:59'),
(232, 162, 132, 1, '2025-05-18 08:01:59'),
(233, 163, 103, 1, '2025-05-18 08:26:20'),
(234, 163, 104, 1, '2025-05-18 08:26:20'),
(235, 163, 105, 1, '2025-05-18 08:26:20'),
(236, 163, 106, 1, '2025-05-18 08:26:20'),
(237, 163, 107, 1, '2025-05-18 08:26:20'),
(238, 163, 108, 1, '2025-05-18 08:26:20'),
(239, 163, 109, 1, '2025-05-18 08:26:20'),
(240, 163, 110, 1, '2025-05-18 08:26:20'),
(241, 163, 111, 1, '2025-05-18 08:26:20'),
(242, 163, 112, 1, '2025-05-18 08:26:20'),
(243, 163, 113, 1, '2025-05-18 08:26:20'),
(244, 163, 114, 1, '2025-05-18 08:26:20'),
(245, 163, 115, 0, '2025-05-18 08:26:20'),
(246, 163, 116, 0, '2025-05-18 08:26:20'),
(247, 163, 117, 0, '2025-05-18 08:26:20'),
(248, 163, 133, 0, '2025-05-18 08:26:20'),
(249, 163, 134, 0, '2025-05-18 08:26:20'),
(250, 163, 135, 0, '2025-05-18 08:26:20'),
(251, 163, 136, 1, '2025-05-18 08:26:20'),
(252, 163, 137, 1, '2025-05-18 08:26:20'),
(253, 163, 138, 1, '2025-05-18 08:26:20'),
(254, 163, 139, 1, '2025-05-18 08:26:20'),
(255, 163, 140, 1, '2025-05-18 08:26:20'),
(256, 163, 141, 1, '2025-05-18 08:26:20'),
(257, 163, 142, 1, '2025-05-18 08:26:20'),
(258, 163, 143, 1, '2025-05-18 08:26:20'),
(259, 163, 144, 1, '2025-05-18 08:26:20'),
(260, 163, 145, 1, '2025-05-18 08:26:20'),
(261, 163, 146, 1, '2025-05-18 08:26:20'),
(262, 163, 147, 1, '2025-05-18 08:26:20'),
(263, 171, 103, 1, '2025-05-18 14:05:48'),
(264, 171, 104, 0, '2025-05-18 14:05:48'),
(265, 171, 105, 1, '2025-05-18 14:05:48'),
(266, 171, 106, 0, '2025-05-18 14:05:48'),
(267, 171, 107, 1, '2025-05-18 14:05:48'),
(268, 171, 108, 0, '2025-05-18 14:05:48'),
(269, 171, 109, 1, '2025-05-18 14:05:48'),
(270, 171, 110, 1, '2025-05-18 14:05:48'),
(271, 171, 111, 1, '2025-05-18 14:05:48'),
(272, 171, 112, 1, '2025-05-18 14:05:48'),
(273, 171, 113, 1, '2025-05-18 14:05:48'),
(274, 171, 114, 1, '2025-05-18 14:05:48'),
(275, 171, 115, 1, '2025-05-18 14:05:48'),
(276, 171, 116, 1, '2025-05-18 14:05:48'),
(277, 171, 117, 1, '2025-05-18 14:05:48'),
(278, 171, 118, 1, '2025-05-18 14:05:48'),
(279, 171, 119, 1, '2025-05-18 14:05:48'),
(280, 171, 120, 1, '2025-05-18 14:05:48'),
(281, 171, 121, 1, '2025-05-18 14:05:48'),
(282, 171, 122, 1, '2025-05-18 14:05:48'),
(283, 171, 123, 1, '2025-05-18 14:05:48'),
(284, 171, 124, 1, '2025-05-18 14:05:48'),
(285, 171, 125, 1, '2025-05-18 14:05:48'),
(286, 171, 126, 1, '2025-05-18 14:05:48'),
(287, 171, 127, 1, '2025-05-18 14:05:48'),
(288, 171, 128, 0, '2025-05-18 14:05:48'),
(289, 171, 129, 1, '2025-05-18 14:05:48'),
(290, 171, 130, 0, '2025-05-18 14:05:48'),
(291, 171, 131, 1, '2025-05-18 14:05:48'),
(292, 171, 132, 0, '2025-05-18 14:05:48'),
(293, 172, 103, 1, '2025-05-21 12:59:47'),
(294, 172, 104, 1, '2025-05-21 12:59:47'),
(295, 172, 105, 1, '2025-05-21 12:59:47'),
(296, 172, 106, 1, '2025-05-21 12:59:47'),
(297, 172, 107, 1, '2025-05-21 12:59:47'),
(298, 172, 108, 1, '2025-05-21 12:59:47'),
(299, 172, 109, 1, '2025-05-21 12:59:47'),
(300, 172, 110, 1, '2025-05-21 12:59:47'),
(301, 172, 111, 1, '2025-05-21 12:59:47'),
(302, 172, 112, 1, '2025-05-21 12:59:47'),
(303, 172, 113, 1, '2025-05-21 12:59:47'),
(304, 172, 114, 0, '2025-05-21 12:59:47'),
(305, 172, 115, 0, '2025-05-21 12:59:47'),
(306, 172, 116, 0, '2025-05-21 12:59:47'),
(307, 172, 117, 0, '2025-05-21 12:59:47'),
(308, 172, 133, 0, '2025-05-21 12:59:47'),
(309, 172, 134, 0, '2025-05-21 12:59:47'),
(310, 172, 135, 0, '2025-05-21 12:59:47'),
(311, 172, 136, 0, '2025-05-21 12:59:47'),
(312, 172, 137, 1, '2025-05-21 12:59:47'),
(313, 172, 138, 1, '2025-05-21 12:59:47'),
(314, 172, 139, 1, '2025-05-21 12:59:47'),
(315, 172, 140, 1, '2025-05-21 12:59:47'),
(316, 172, 141, 1, '2025-05-21 12:59:47'),
(317, 172, 142, 1, '2025-05-21 12:59:47'),
(318, 172, 143, 1, '2025-05-21 12:59:47'),
(319, 172, 144, 1, '2025-05-21 12:59:47'),
(320, 172, 145, 1, '2025-05-21 12:59:47'),
(321, 172, 146, 1, '2025-05-21 12:59:47'),
(322, 172, 147, 1, '2025-05-21 12:59:47');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `score_a` int(11) DEFAULT NULL,
  `score_b` int(11) DEFAULT NULL,
  `result` enum('Home Team Win','Away Team Win','Draw') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `schedule_id`, `score_a`, `score_b`, `result`) VALUES
(27, 162, 3, 2, 'Home Team Win'),
(28, 171, 2, 1, 'Home Team Win'),
(29, 172, 2, 0, 'Home Team Win');

-- --------------------------------------------------------

--
-- Table structure for table `motm`
--

CREATE TABLE `motm` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motm`
--

INSERT INTO `motm` (`id`, `match_id`, `player_id`) VALUES
(3, 27, 103),
(4, 27, 103),
(5, 28, 103),
(6, 29, 103);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `registration_date` date NOT NULL,
  `payment_status` varchar(50) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `team_id`, `registration_date`, `payment_status`, `payment_proof`) VALUES
(4, 42, '2025-05-18', 'Sudah Bayar', 'proof_4_1747541398.jpg'),
(5, 43, '2025-05-18', 'Sudah Bayar', 'proof_5_1747541807.jpg'),
(6, 44, '2025-05-18', 'Sudah Bayar', 'proof_6_1747552257.jpg'),
(7, 45, '2025-05-18', 'Sudah Bayar', 'proof_7_1747554070.jpg'),
(13, 51, '2025-05-18', 'Sudah Bayar', 'proof_13_1747573875.png'),
(14, 52, '2025-05-20', 'Belum Bayar', ''),
(15, 53, '2025-05-21', 'Sudah Bayar', 'proof_15_1747832205.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `back_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `name`, `position`, `team_id`, `back_number`) VALUES
(87, 'Andi Pratama', 'Goalkeeper', 39, 12),
(88, 'Bima Saputra', 'Defender', 39, 13),
(89, 'Deni Firmansyah', 'Midfielder', 39, 14),
(90, 'Rizky Hidayat', 'Forward', 39, 15),
(91, 'Yoga Maulana', 'Goalkeeper', 39, 16),
(92, 'Fajar Ramadhan', 'Forward', 39, 17),
(93, 'Reza Kurniawan', 'Defender', 39, 18),
(94, 'Arief Setiawan', 'Midfielder', 39, 19),
(95, 'Ilham Syahputra', 'Goalkeeper', 40, 12),
(96, 'Fikri Aditya', 'Defender', 40, 13),
(97, 'Rian Maulana', 'Midfielder', 40, 14),
(98, 'Arman Prasetyo', 'Forward', 40, 15),
(99, 'Hendra Wijaya', 'Goalkeeper', 40, 16),
(100, 'Fikran Alfarizi', 'Defender', 40, 17),
(101, 'Zaki Nurhuda', 'Midfielder', 40, 18),
(102, 'Bayu Saputro', 'Forward', 40, 19),
(103, 'Michael Evans', 'Goalkeeper', 42, 1),
(104, 'Eric Foster', 'Goalkeeper', 42, 12),
(105, 'Ryan Collins', 'Defender', 42, 2),
(106, 'Samuel Brown', 'Defender', 42, 3),
(107, 'Daniel Green', 'Defender', 42, 4),
(108, 'Luke Anderson', 'Defender', 42, 5),
(109, 'Adam Scott', 'Midfielder', 42, 6),
(110, 'Jason Lee', 'Midfielder', 42, 7),
(111, 'Thomas White', 'Midfielder', 42, 8),
(112, 'Chris Young', 'Midfielder', 42, 10),
(113, 'Liam Mitchell', 'Midfielder', 42, 14),
(114, 'Kevin Miller', 'Forward', 42, 9),
(115, 'Nathan Walker', 'Forward', 42, 11),
(116, 'Owen Grant', 'Forward', 42, 15),
(117, 'Connor Blake', 'Defender', 42, 13),
(118, 'Oliver Knight', 'Goalkeeper', 43, 1),
(119, 'Declan Hayes', 'Goalkeeper', 43, 12),
(120, 'Ethan Lewis', 'Defender', 43, 2),
(121, 'Logan Martinez', 'Defender', 43, 3),
(122, 'Jacob Hall', 'Defender', 43, 4),
(123, 'Mason Carter', 'Defender', 43, 5),
(124, 'Julian Scott', 'Defender', 43, 13),
(125, 'Caleb Wright', 'Midfielder', 43, 6),
(126, 'Aiden Turner', 'Midfielder', 43, 7),
(127, 'Joshua King', 'Midfielder', 43, 8),
(128, 'Benjamin Clark', 'Midfielder', 43, 10),
(129, 'Xavier Morris', 'Midfielder', 43, 14),
(130, 'Anthony Rivera', 'Forward', 43, 9),
(131, 'Elijah Adams', 'Forward', 43, 11),
(132, 'Marcus Flynn', 'Forward', 43, 15),
(133, 'William Roberts', 'Goalkeeper', 44, 1),
(134, 'Henry Bennett', 'Goalkeeper', 44, 12),
(135, 'Jayden Phillips', 'Defender', 44, 2),
(136, 'Dylan Cooper', 'Defender', 44, 3),
(137, 'Nathaniel Baker', 'Defender', 44, 4),
(138, 'Gabriel Morris', 'Defender', 44, 5),
(139, 'Sebastian Ward', 'Defender', 44, 13),
(140, 'Tyler Reed', 'Midfielder', 44, 6),
(141, 'Christian Perez', 'Midfielder', 44, 7),
(142, 'Zachary Morgan', 'Midfielder', 44, 8),
(143, 'Austin Hughes', 'Midfielder', 44, 10),
(144, 'Leo Chambers', 'Midfielder', 44, 14),
(145, 'Brandon Rogers', 'Forward', 44, 9),
(146, 'Levi Stewart', 'Forward', 44, 11),
(147, 'Carson Webb', 'Forward', 44, 15),
(148, 'Logan Bennett', 'Goalkeeper', 45, 1),
(149, 'Carter Simmons', 'Goalkeeper', 45, 12),
(150, 'Blake Reynolds', 'Defender', 45, 2),
(151, 'Dominic Barnes', 'Defender', 45, 3),
(152, 'Hayden Brooks', 'Defender', 45, 4),
(153, 'Trevor Wallace', 'Defender', 45, 5),
(154, 'Miles Griffin', 'Defender', 45, 13),
(155, 'Julian Parker', 'Midfielder', 45, 6),
(156, 'Spencer Hayes', 'Midfielder', 45, 7),
(157, 'Maxwell Dean', 'Midfielder', 45, 8),
(158, 'Roman Ellis', 'Midfielder', 45, 10),
(159, 'Declan Matthews', 'Midfielder', 45, 14),
(160, 'Easton Vaughn', 'Forward', 45, 9),
(161, 'Camden Rhodes', 'Forward', 45, 11),
(162, 'Jaxon Monroe', 'Forward', 45, 15),
(167, 'Reky Rahayu', 'Goalkeeper', 51, 29),
(168, 'Nick Kuipers', 'Defender', 51, 90),
(169, 'Victor Igbonefo', 'Defender', 51, 33),
(170, 'Zalnando', 'Defender', 51, 4),
(171, 'Henhen Herdiana', 'Defender', 51, 5),
(172, 'Kakang Rudianto', 'Defender', 51, 13),
(173, 'Dedi Kusnandar', 'Midfielder', 51, 6),
(174, 'Marc Klok', 'Midfielder', 51, 7),
(175, 'Beckham Putra', 'Midfielder', 51, 8),
(176, 'Febri Hariyadi', 'Midfielder', 51, 10),
(177, 'Robi Darwis', 'Midfielder', 51, 14),
(178, 'Ciro Alves', 'Forward', 51, 98),
(179, 'David da Silva', 'Forward', 51, 11),
(180, 'Ezra Walian', 'Forward', 51, 15),
(181, 'Teja Paku Alam', 'Goalkeeper', 51, 1),
(182, 'Landon Sharp', 'Goalkeeper', 53, 1),
(183, 'Bryce Whitaker', 'Goalkeeper', 53, 2),
(184, 'Chase Franklin', 'Defender', 53, 3),
(185, 'Colton Wells', 'Defender', 53, 4),
(186, 'Hayden Marsh', 'Defender', 53, 5),
(187, 'Seth Rodgers', 'Defender', 53, 6),
(188, 'Trevor Douglas', 'Defender', 53, 7),
(189, 'Alex Warner', 'Midfielder', 53, 8),
(190, 'Miles Newton', 'Midfielder', 53, 9),
(191, 'Riley Sutton', 'Midfielder', 53, 10),
(192, 'Dalton Briggs', 'Midfielder', 53, 11),
(193, 'Camden Holt', 'Midfielder', 53, 12),
(194, 'Wesley McCarthy', 'Midfielder', 53, 13),
(195, 'Bryan Chandler', 'Forward', 53, 14),
(196, 'Cooper Ramsey', 'Forward', 53, 15);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `match_date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `team_home_id` int(11) DEFAULT NULL,
  `team_away_id` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','finished') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `match_date`, `time`, `team_home_id`, `team_away_id`, `location`, `status`) VALUES
(208, '2025-11-18', '15:00:00', 42, 43, '0', 'pending'),
(209, '2025-11-21', '15:00:00', 42, 44, '0', 'pending'),
(210, '2025-11-24', '15:00:00', 42, 45, '0', 'pending'),
(211, '2025-11-27', '15:00:00', 42, 51, '0', 'pending'),
(212, '2025-11-30', '15:00:00', 42, 52, '0', 'pending'),
(213, '2025-12-03', '15:00:00', 42, 53, '0', 'pending'),
(214, '2025-12-06', '15:00:00', 43, 44, '0', 'pending'),
(215, '2025-12-09', '15:00:00', 43, 45, '0', 'pending'),
(216, '2025-12-12', '15:00:00', 43, 51, '0', 'pending'),
(217, '2025-12-15', '15:00:00', 43, 52, '0', 'pending'),
(218, '2025-12-18', '15:00:00', 43, 53, '0', 'pending'),
(219, '2025-12-21', '15:00:00', 44, 45, '0', 'pending'),
(220, '2025-12-24', '15:00:00', 44, 51, '0', 'pending'),
(221, '2025-12-27', '15:00:00', 44, 52, '0', 'pending'),
(222, '2025-12-30', '15:00:00', 44, 53, '0', 'pending'),
(223, '2026-01-02', '15:00:00', 45, 51, '0', 'pending'),
(224, '2026-01-05', '15:00:00', 45, 52, '0', 'pending'),
(225, '2026-01-08', '15:00:00', 45, 53, '0', 'pending'),
(226, '2026-01-11', '15:00:00', 51, 52, '0', 'pending'),
(227, '2026-01-14', '15:00:00', 51, 53, '0', 'pending'),
(228, '2026-01-17', '15:00:00', 52, 53, '0', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `standings`
--

CREATE TABLE `standings` (
  `id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `matches_played` int(11) DEFAULT 0,
  `wins` int(11) DEFAULT 0,
  `draws` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `goals_for` int(11) DEFAULT 0,
  `goals_against` int(11) DEFAULT 0,
  `goal_diff` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `standings`
--

INSERT INTO `standings` (`id`, `team_id`, `matches_played`, `wins`, `draws`, `losses`, `goals_for`, `goals_against`, `goal_diff`, `points`) VALUES
(48, 42, 3, 3, 0, 0, 7, 3, 4, 9),
(49, 43, 2, 0, 0, 2, 3, 5, -2, 0),
(50, 51, 0, 0, 0, 0, 0, 0, 0, 0),
(53, 53, 0, 0, 0, 0, 0, 0, 0, 0),
(55, 44, 1, 0, 0, 1, 0, 2, -2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `team_name`) VALUES
(42, 'Thunder FC'),
(43, 'Iron Wolves'),
(44, 'Sky Hawks'),
(45, 'Shadow Blades'),
(51, 'Persib'),
(52, 'PSIS'),
(53, 'Blue Titans');

-- --------------------------------------------------------

--
-- Table structure for table `topscorers`
--

CREATE TABLE `topscorers` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `goals` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'admin123', 'rossinurajizah@gmail.com', '$2y$10$uc0ZBBKGdy2AEZQWcWZy6uxFpN/uN3y0Uvt1Br4CyWh9iCT2pwple'),
(3, 'valentino', 'valentino@gmail.com', '$2y$10$RbrZPPC6tDKcwDskyKt1VuNi45nQLb4QUjkSKvJqZGh4WQ6igVYxS'),
(5, 'Duka', 'Duka@gmail.com', '$2y$10$83NHOGEzufJuuDOS0Gta7OV6mS4N1NvO/f6IQex86m/zHlGdpgqzy'),
(6, 'dinda', 'dinda@student.unsil.ac.id', '$2y$10$FKseIDzZI0VNN7cBG79dX.BcRNHC9R37nZJ8TDeUcwpi373AdWaZ6'),
(7, 'dita', 'dita@student.unsil.ac.id', '$2y$10$Y.VcvQ.YKe1NQxxaIMHgT.2viSm.AcOHYCCZoB3DF1zIq.Gv4ZDua'),
(8, 'oci', 'oci@gmail.com', '$2y$10$/B/OXRqHZqWtZBWcGccIrurBKGyfZIMEEa.khM5Kx153hQo.rsE7W'),
(9, 'Valen', 'Valen@gmail.com', '$2y$10$.qkOnG2A0YVWvIFt1KknreNKnPoMBdo7./m4x2MqQ4Ph/.7WicxjC');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `best_players`
--
ALTER TABLE `best_players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `fouls`
--
ALTER TABLE `fouls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `lineups`
--
ALTER TABLE `lineups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `lineups_ibfk_1` (`match_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `motm`
--
ALTER TABLE `motm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `player_id` (`player_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_home_id` (`team_home_id`),
  ADD KEY `team_away_id` (`team_away_id`);

--
-- Indexes for table `standings`
--
ALTER TABLE `standings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_id` (`team_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `topscorers`
--
ALTER TABLE `topscorers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `best_players`
--
ALTER TABLE `best_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fouls`
--
ALTER TABLE `fouls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `lineups`
--
ALTER TABLE `lineups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=323;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `motm`
--
ALTER TABLE `motm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `standings`
--
ALTER TABLE `standings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `topscorers`
--
ALTER TABLE `topscorers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `best_players`
--
ALTER TABLE `best_players`
  ADD CONSTRAINT `best_players_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `best_players_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `fouls`
--
ALTER TABLE `fouls`
  ADD CONSTRAINT `fouls_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`),
  ADD CONSTRAINT `fouls_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `fouls_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`),
  ADD CONSTRAINT `goals_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `lineups`
--
ALTER TABLE `lineups`
  ADD CONSTRAINT `lineups_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lineups_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`);

--
-- Constraints for table `motm`
--
ALTER TABLE `motm`
  ADD CONSTRAINT `motm_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`),
  ADD CONSTRAINT `motm_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`team_home_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`team_away_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `standings`
--
ALTER TABLE `standings`
  ADD CONSTRAINT `standings_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `topscorers`
--
ALTER TABLE `topscorers`
  ADD CONSTRAINT `topscorers_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `topscorers_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
