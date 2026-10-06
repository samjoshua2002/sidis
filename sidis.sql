-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 23, 2026 at 05:57 AM
-- Server version: 8.0.46-0ubuntu0.24.04.4
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sidis`
--

-- --------------------------------------------------------

--
-- Table structure for table `advisory_committee`
--

CREATE TABLE `advisory_committee` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `advisory_committee`
--

INSERT INTO `advisory_committee` (`id`, `name`, `designation`) VALUES
(1, 'Prof. Koshy Varghese', 'Chairperson'),
(2, 'Prof. M.S. Ramachandra Rao', 'Member'),
(3, 'Prof. Nilesh J. Vasa', 'Member'),
(4, 'Prof. C. Balaji', 'Member'),
(5, 'Prof. Navakanta Bhat', 'Dean, Division of Interdisciplinary Science, IISc - External Member'),
(6, 'Dr. Ravi Bhatkal', 'MD, MacDermidAlpha Electronics Solutions - External Member'),
(7, 'Mr. Raja Manickam', 'Founder, iVP Semi - External Member'),
(8, 'Dean, Faculty', 'Ex-officio'),
(9, 'Dean, AR', 'Ex-officio'),
(10, 'Dean, AC', 'Ex-officio'),
(11, 'Dean, Planning', 'Ex-officio'),
(12, 'Dean, Administration', 'Ex-officio'),
(13, 'Head, SIDiS', 'Member Secretary');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `date_text` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `date_text`, `link`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'First batch of Bachelor of Cybersecurity program starting in July 2026', '07 Feb to 07 Nov', 'bcyber_iitm.html', 'announcement.png', 1, '2026-08-20 07:18:14', '2026-08-20 07:18:14'),
(2, 'First batch of Bachelor of Cybersecurity program starting in July 2026', '07 Feb to 09 Nov', 'https://sidis.iitm.ac.in/bcyber_iitm.html', 'announcement.png', 0, '2026-08-20 07:18:14', '2026-09-01 10:08:29');

-- --------------------------------------------------------

--
-- Table structure for table `clusters`
--

CREATE TABLE `clusters` (
  `id` int UNSIGNED NOT NULL,
  `cluster_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clusters`
--

INSERT INTO `clusters` (`id`, `cluster_name`, `display_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Advanced Materials and Quantum Initiative', 1, 1, '2026-08-24 09:51:35', '2026-08-24 09:51:35'),
(2, 'Blue Economy', 2, 1, '2026-08-24 09:51:35', '2026-08-24 09:59:04'),
(3, 'Computational Engineering', 3, 1, '2026-08-24 09:51:35', '2026-08-24 09:59:15'),
(4, 'Sports Science & Analytics', 4, 1, '2026-08-24 09:51:35', '2026-08-24 10:15:16'),
(5, 'Management and Public Policy', 5, 1, '2026-08-24 09:51:35', '2026-08-24 09:59:28'),
(6, 'Power Conversion Systems', 6, 1, '2026-08-24 09:51:35', '2026-08-24 10:00:22'),
(7, 'Robotics and Cyber-physical Systems', 7, 1, '2026-08-24 09:51:35', '2026-08-24 10:00:26'),
(8, 'School of Innovation and Entrepreneurship', 8, 1, '2026-08-24 09:51:35', '2026-08-24 10:00:30'),
(9, 'School of Sustainability', 9, 1, '2026-08-24 09:51:35', '2026-08-24 10:00:32');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int UNSIGNED NOT NULL,
  `school_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `institution_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `map_embed_url` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `school_name`, `institution_name`, `address`, `email`, `phone`, `map_embed_url`) VALUES
(1, 'School of Interdisciplinary Studies', 'Indian Institute of Technology Madras', 'Subramonian Shankar Block (SSB)\nRoom Number 120-121, Ground Floor\nIndian Institute of Technology Madras\nChennai 600036', 'idoffice@iitm.ac.in', '+91-2257 2300', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7390.665910075512!2d80.23111577484175!3d12.991492887325704!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a5267f29aa9a61f%3A0x24ef264085e6a094!2sIndian%20Institute%20Of%20Technology%E2%80%93Madras%20(IIT%E2%80%93Madras)!5e1!3m2!1sen!2sin!4v1788596401956!5m2!1sen!2sin');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int UNSIGNED NOT NULL,
  `cluster_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_page` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `top_section` tinyint(1) NOT NULL DEFAULT '0',
  `top_order` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `cluster_id`, `name`, `image`, `department`, `designation`, `email`, `personal_page`, `display_order`, `status`, `created_at`, `updated_at`, `top_section`, `top_order`) VALUES
(4, '1', 'Prashant Rawat', 'Prashant Rawat.webp', 'Aerospace Engineering', 'Faculty', 'prashant[.]rawat[at]smail[.]iitm[.]ac[.]in', 'https://www.prashantiitm.com/', 1, 1, '2026-08-24 10:02:18', '2026-09-05 07:47:54', 0, 0),
(5, '1', 'M S Sivakumar', 'm-s-sivakumar.ILElJ9wI_Z22UwSV.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'mssiva[at]smail[.]iitm[.]ac[.]in', 'https://home.iitm.ac.in/mssiva/', 2, 1, '2026-08-24 10:02:18', '2026-08-27 07:33:43', 0, 0),
(6, '1', 'V V Raghavendra Sai', 'v-v-raghavendra-sai.7li7eJw4_9lRU7.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'vvrsai[at]smail[.]iitm[.]ac[.]in', 'https://sites.google.com/smail.iitm.ac.in/biosensors/home', 3, 1, '2026-08-24 10:02:18', '2026-08-27 07:36:32', 0, 0),
(7, '1', 'Amit Nain', 'dummy.png', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'amit[at]smail[.]iitm[.]ac[.]in', '#', 4, 1, '2026-08-24 10:02:18', '2026-08-24 10:47:25', 0, 0),
(8, '1', 'S. VIMALRAJ', 'dummy.png', 'Applied Mechanics and Biomedical Enigineering', 'Faculty', 'vimalraj[at]smail[.]iitm[.]ac[.]in', '#', 5, 1, '2026-08-24 10:02:18', '2026-08-24 10:47:25', 0, 0),
(9, '1', 'Greeshma Thrivikraman', 'Greeshma Thrivikraman.png', 'Biotechnology', 'Faculty', 'greeshma[at]smail[.]iitm[.]ac[.]in', 'https://biotech.iitm.ac.in/innerfaculty.php?fname=Greeshma+Thrivikraman', 6, 1, '2026-08-24 10:02:18', '2026-08-27 07:55:14', 0, 0),
(10, '1', 'Sanjib Senapati', 'Sanjib Senapati.png', 'Biotechnology', 'Faculty', 'sanjibs[at]smail[.]iitm[.]ac[.]in', 'https://biotech.iitm.ac.in/innerfaculty.php?fname=Sanjib+Senapati', 7, 1, '2026-08-24 10:02:18', '2026-08-27 07:55:55', 0, 0),
(11, '1', 'Madivala G. Basavaraj', 'basavaraj.png', 'Chemical Engineering', 'Faculty', 'basa[at]smail[.]iitm[.]ac[.]in', 'https://che.iitm.ac.in/faculty.php?fname=Dr.+Basavaraj+M.+Gurappa', 8, 1, '2026-08-24 10:02:18', '2026-08-27 08:02:16', 0, 0),
(12, '1', 'S. R. K. Chaitanya Sharma Yamijala', '1772718815_715520dede.jpg', 'Chemistry', 'Faculty', 'yamijala[at]smail[.]iitm[.]ac[.]in', 'https://chem.iitm.ac.in/faculty-inner.php?id=9', 9, 1, '2026-08-24 10:02:18', '2026-08-27 10:59:30', 0, 0),
(13, '1', 'Krishna Reddy Nandipati', '1772729088_340420547a.jpg', 'Chemistry', 'Faculty', 'knandipati[at]smail[.]iitm[.]ac[.]in', 'https://chem.iitm.ac.in/faculty-inner.php?id=20', 10, 1, '2026-08-24 10:02:18', '2026-08-27 10:59:33', 0, 0),
(14, '1', 'Soumen Ghosh', '1772783552_db664e0fe2.jpg', 'Chemistry', 'Faculty', 'chemsghosh[at]smail[.]iitm[.]ac[.]in', 'https://chem.iitm.ac.in/faculty-inner.php?id=33', 11, 1, '2026-08-24 10:02:18', '2026-08-27 11:00:03', 0, 0),
(15, '1', 'Aslam Kunhi Mohamed', 'ASLAM.jpg', 'Civil Engineering', 'Faculty', 'akm[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/aslam/', 12, 1, '2026-08-24 10:02:18', '2026-08-27 11:03:45', 0, 0),
(16, '1', 'SOUMYA DUTTA', 'citations.jpg', 'Electrical Engineering', 'Faculty', 's[.]dutta[at]ee[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/soumya-dutta/', 13, 1, '2026-08-24 10:02:18', '2026-08-28 05:37:09', 0, 0),
(17, '1', 'Shivananju B N', 'shivananju-bn.png', 'Electrical Engineering', 'Faculty', 'shivananju[at]smail[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/shivananju-bn/', 14, 1, '2026-08-24 10:02:18', '2026-08-28 05:37:51', 0, 0),
(18, '1', 'Sudharsanan S', 'sudharsanan-srinivasan.png', 'Electrical Engineering', 'Faculty', 'sudharsanan[at]ee[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/sudharsanan-srinivasan/', 15, 1, '2026-08-24 10:02:18', '2026-08-28 06:43:30', 0, 0),
(19, '1', 'Bhaswar Chakrabarti', 'bhaswar-chakrabarti.png', 'Electrical Engineering', 'Faculty', 'bchakrabarti[at]smail[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/bhaswar-chakrabarti/', 16, 1, '2026-08-24 10:02:18', '2026-08-28 07:22:34', 0, 0),
(20, '1', 'Anil Prabhakar', 'anil-prabhakar.png', 'Electrical Engineering', 'Faculty', 'anilpr[at]smail[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/anil-prabhakar/', 17, 1, '2026-08-24 10:02:18', '2026-08-28 07:23:02', 0, 0),
(21, '1', 'Tuhin Subhra Santra', 'TSubhraSantra.jpg', 'Engineering Design', 'Faculty', 'tuhin[at]smail[.]iitm[.]ac[.]in', 'https://ed.iitm.ac.in/faculty.html?id=Tuhin_Subhra_Santra', 18, 1, '2026-08-24 10:02:18', '2026-08-28 07:38:17', 0, 0),
(22, '1', 'Anirudh Udupa', 'audupa.png', 'Mechanical Engineering', 'Faculty', 'audupa[at]smail[.]iitm[.]ac[.]in', 'https://mech.iitm.ac.in/profile.php?fname=audupa', 19, 1, '2026-08-24 10:02:18', '2026-08-28 08:18:39', 0, 0),
(23, '1', 'Ranjit Bauri', 'ranjit.png', 'Metallurgical and Materials Engineering', 'Faculty', 'rbauri[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Ranjit%20Bauri', 20, 1, '2026-08-24 10:02:18', '2026-08-28 08:25:58', 0, 0),
(24, '1', 'Ravi Kumar N V', 'WhatsApp Image 2025-08-07 at 12.18.50 PM.jpeg', 'Metallurgical and Materials Engineering', 'Faculty', 'nvrk[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Ravi%20Kumar%20NV', 21, 1, '2026-08-24 10:02:18', '2026-08-28 08:32:19', 0, 0),
(25, '1', 'Surendra B. Anantharaman', 'surendra.png', 'Metallurgical and Materials Engineering', 'Faculty', 'sba[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Surendra%20B%20Anantharaman', 22, 1, '2026-08-24 10:02:18', '2026-08-28 08:32:54', 0, 0),
(26, '1', 'Rohit Batra', 'rohit.png', 'Metallurgical and Materials Engineering', 'Faculty', 'rbatra[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Rohit%20Batra', 23, 1, '2026-08-24 10:02:18', '2026-08-28 08:33:16', 0, 0),
(27, '1', 'Satyesh Kumar Yadav', 'satyesh.png', 'Metallurgical and Materials Engineering', 'Faculty', 'satyesh[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Satyesh%20Kumar%20Yadav', 24, 1, '2026-08-24 10:02:18', '2026-08-28 08:33:39', 0, 0),
(28, '1', 'Prem Bisht', 'bisht.jpg', 'Physics', 'Faculty', 'bisht[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/bisht.html', 25, 1, '2026-08-24 10:02:18', '2026-08-28 08:46:59', 0, 0),
(29, '1', 'Basudev Roy', 'basudev.jpg', 'Physics', 'Faculty', 'basudev[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/basudev.html', 26, 1, '2026-08-24 10:02:18', '2026-08-28 08:47:35', 0, 0),
(30, '1', 'Rahul Sawant', 'rahul_sawant.jpg', 'Physics', 'Faculty', 'rahul[.]sawant[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/rahul_sawant.html', 27, 1, '2026-08-24 10:02:18', '2026-08-28 08:48:56', 0, 0),
(31, '1', 'Panchanana Khuntia', 'pkhuntia.jpg', 'Physics', 'Faculty', 'pkhuntia[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/pkhuntia.html', 28, 1, '2026-08-24 10:02:18', '2026-08-28 08:49:29', 0, 0),
(32, '1', 'P Murugavel', 'muruga.jpg', 'Physics', 'Faculty', 'muruga[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/muruga.html', 29, 1, '2026-08-24 10:02:18', '2026-08-28 08:50:50', 0, 0),
(33, '1', 'Abhishek Misra', 'abhishek_misra.jpg', 'Physics', 'Faculty', 'abhishek[.]misra[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/abhishek_misra.html', 30, 1, '2026-08-24 10:02:18', '2026-08-28 08:52:42', 0, 0),
(34, '1', 'Sudakar Chandran', 'csudakar.jpg', 'Physics', 'Faculty', 'csudakar[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/csudakar.html', 31, 1, '2026-08-24 10:02:18', '2026-08-28 08:53:05', 0, 0),
(35, '1', 'V Praveen Bhallamudi', 'praveen_bhallamudi.jpg', 'Physics', 'Faculty', 'praveen[.]bhallamudi[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/praveen_bhallamudi.html', 32, 1, '2026-08-24 10:02:18', '2026-08-28 08:54:35', 0, 0),
(36, '1', 'Sivarama Krishnan', 'srkrishnan.jpg', 'Physics', 'Faculty', 'srkrishnan[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/srkrishnan.html', 33, 1, '2026-08-24 10:02:18', '2026-08-28 08:55:01', 0, 0),
(37, '1', 'Vaibhav Madhok', 'madhok.jpg', 'Physics', 'Faculty', 'madhok[at]physics[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/madhok.html', 34, 1, '2026-08-24 10:02:18', '2026-08-28 08:55:25', 0, 0),
(38, '1', 'Dillip Kumar Satapathy', 'dks.jpg', 'Physics', 'Faculty', 'dks[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/dks.html', 35, 1, '2026-08-24 10:02:18', '2026-08-28 08:55:53', 0, 0),
(39, '1', 'Ayana Ghosh', 'ghosha.jpg', 'Physics', 'Faculty', 'research[.]aghosh[at]gmail[.]com', 'https://physics.iitm.ac.in/people/facultyinfo/ghosha.html', 36, 1, '2026-08-24 10:02:18', '2026-08-28 08:56:19', 0, 0),
(40, '2', 'K. Murali', 'faculty_1751358968_68639df8a71f1.png', 'Ocean Engineering', 'Faculty', 'murali[at]smail[.]iitm[.]ac[.]in', 'https://doe.iitm.ac.in/muralik', 1, 1, '2026-08-24 10:12:59', '2026-08-28 08:39:01', 0, 0),
(41, '2', 'V. Sriram', 'faculty_1754900673_6899a8c19a60f.png', 'Ocean Engineering', 'Faculty', 'vsriram[at]smail[.]iitm[.]ac[.]in', 'https://doe.iitm.ac.in/sriramvenkatachalam', 2, 1, '2026-08-24 10:12:59', '2026-08-28 08:39:37', 0, 0),
(42, '2', 'Debashis Chakraborty', '1772719541_7586731c39.jpg', 'Chemistry', 'Faculty', 'dchakraborty[at]smail[.]iitm[.]ac[.]in', 'https://chem.iitm.ac.in/faculty-inner.php?id=10', 3, 1, '2026-08-24 10:12:59', '2026-08-27 11:00:28', 0, 0),
(43, '2', 'Soumendra Nath Kuiry', 'snkuiry-SoumendraKui.jpg', 'Civil Engineering', 'Faculty', 'snkuiry[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/snkuiry/', 4, 1, '2026-08-24 10:12:59', '2026-08-27 11:08:40', 0, 0),
(44, '2,5,9', 'Krishna Malakar', 'krishna-qog0yqe83mryaiy1797o3solt84dkuui61vk8ou5dk.png', 'Humanities and Social Sciences', 'Faculty', 'krishnamalakar[at]smail[.]iitm[.]ac[.]in', 'https://hss.iitm.ac.in/krishna-malakar/', 5, 1, '2026-08-24 10:12:59', '2026-09-10 06:47:57', 0, 0),
(45, '2,9', 'Santosh Kumar Sahu', 'santosh_sahu-qweyxu9upf8mslsjejnurwdg45c0y64l4qnzsxz6wo.jpg', 'Humanities and Social Sciences', 'Faculty', 'santosh[at]smail[.]iitm[.]ac[.]in', 'https://hss.iitm.ac.in/santosh-kumar-sahu', 6, 1, '2026-08-24 10:12:59', '2026-09-10 06:48:20', 0, 0),
(46, '2', 'Abdus Samad', 'img_6899b5b4afa733.69416491.jpg', 'Ocean Engineering', 'Faculty', 'samad[at]smail[.]iitm[.]ac[.]in', 'https://doe.iitm.ac.in/samad', 7, 1, '2026-08-24 10:12:59', '2026-08-28 08:40:56', 0, 0),
(47, '2', 'Thejesh Kumar Garala', 'faculty_1754900713_6899a8e990b32.png', 'Ocean Engineering', 'Faculty', 'tkgarala[at]smail[.]iitm[.]ac[.]in', 'https://doe.iitm.ac.in/tkgarala', 8, 1, '2026-08-24 10:12:59', '2026-08-28 08:41:27', 0, 0),
(48, '2', 'M. A. Atmanand', 'atmanand.png', 'Ocean Engineering', 'Faculty', 'atma[at]smail[.]iitm[.]ac[.]in', 'https://doe.iitm.ac.in/atmanand', 9, 1, '2026-08-24 10:12:59', '2026-08-28 08:42:15', 0, 0),
(49, '3', 'A. Sameen', 'Sameen.webp', 'Aerospace Engineering', 'Faculty', 'sameen[at]smail[.]iitm[.]ac[.]in', 'https://home.iitm.ac.in/sameen/index.html', 1, 1, '2026-08-24 10:13:59', '2026-08-27 07:27:48', 0, 0),
(50, '3', 'Sunetra Sarkar', 'Sunetra Sarkar.webp', 'Aerospace Engineering', 'Faculty', 'sunetra[at]smail[.]iitm[.]ac[.]in', 'https://home.iitm.ac.in/sunetra/', 2, 1, '2026-08-24 10:13:59', '2026-08-27 07:28:18', 0, 0),
(51, '3', 'Aniketh Kalur', 'Aniketh_Kalur.jpg', 'Aerospace Engineering', 'Faculty', 'aniketh[.]kalur[at]smail[.]iitm[.]ac[.]in', 'https://ae.iitm.ac.in/~aniketh.kalur/', 3, 1, '2026-08-24 10:13:59', '2026-08-27 07:29:01', 0, 0),
(52, '3', 'Vagesh D. Narasimhamurthy', 'vagesh-d-narasimhamurthy.B0IDFXH2_zsFBC.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'vagesh[at]smail[.]iitm[.]ac[.]in', 'https://home.iitm.ac.in/vagesh/', 4, 1, '2026-08-24 10:13:59', '2026-08-27 07:46:51', 0, 0),
(53, '3', 'Sayan Gupta', '20231130-1527512-690x1227.jpeg', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'sayangupta[at]smail[.]iitm[.]ac[.]in', 'https://home.iitm.ac.in/sayan/', 5, 1, '2026-08-24 10:13:59', '2026-08-27 07:47:36', 0, 0),
(54, '3', 'Prasad Patnaik B S V', 'b-s-v-prasad-patnaik.WEaEneeQ_Ze4SuK.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'bsvp[at]smail[.]iitm[.]ac[.]in', 'https://simbiotsiitm.github.io/Simbiots-Lab/', 6, 1, '2026-08-24 10:13:59', '2026-08-27 07:48:13', 0, 0),
(55, '3', 'Sarith P Sathian', 'sarith-p-sathian.Cp2bKUVo_rly1X.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'sarith[at]smail[.]iitm[.]ac[.]in', 'https://sites.google.com/site/sarithshomepage', 7, 1, '2026-08-24 10:13:59', '2026-08-27 07:48:57', 0, 0),
(56, '3', 'Aditi Kathpalia', 'aditi-kathpalia.DRa-ryD6_1cDCA8.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'aditi[at]smail[.]iitm[.]ac[.]in', 'https://aditikathpalia.wordpress.com/', 8, 1, '2026-08-24 10:13:59', '2026-08-27 07:49:24', 0, 0),
(57, '3', 'Danny Raj M', 'danny-apm.D3hQ6GP-_2bA5ET.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'danny[at]smail[.]iitm[.]ac[.]in', 'https://www.dannyraj.com/', 9, 1, '2026-08-24 10:13:59', '2026-08-27 07:49:47', 0, 0),
(58, '3', 'M. Hamsa Priya', 'faculty_6995e2470aab36.89472653.jpeg', 'Biotechnology', 'Faculty', 'hamsa[at]smail[.]iitm[.]ac[.]in', 'https://biotech.iitm.ac.in/innerfaculty.php?fname=M+Hamsa+Priya', 10, 1, '2026-08-24 10:13:59', '2026-08-27 07:56:50', 0, 0),
(59, '3', 'Abhinav S. Raman', 'ASR_photo.jpg', 'Chemical Engineering', 'Faculty', 'asraman[at]smail[.]iitm[.]ac[.]in', 'https://che.iitm.ac.in/faculty.php?fname=Dr.+Abhinav+S.+Raman', 11, 1, '2026-08-24 10:13:59', '2026-08-27 08:12:48', 0, 0),
(60, '3', 'Parul Verma', 'Parul.png', 'Chemical Engineering', 'Faculty', 'parulv[at]smail[.]iitm[.]ac[.]in', 'https://che.iitm.ac.in/faculty.php?fname=Dr.+Parul+Verma', 12, 1, '2026-08-24 10:13:59', '2026-08-27 08:15:07', 0, 0),
(61, '3,9', 'Sreeparvathy Vijay', 'Sreeparvathy Photo1.jpg', 'Civil Engineering', 'Faculty', 'sreeparvathyvijay[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/sreeparvathyvijay/', 13, 1, '2026-08-24 10:13:59', '2026-09-10 06:46:33', 0, 0),
(62, '3', 'Saravanan U', 'saran-SaravananUIITM.jpg', 'Civil Engineering', 'Faculty', 'saran[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/saran/', 14, 1, '2026-08-24 10:13:59', '2026-08-27 11:10:05', 0, 0),
(63, '3', 'Sivaram Ambikasaran', 'dummy.png', 'Mathematics', 'Faculty', 'sivaambi[at]smail[.]iitm[.]ac[.]in', 'https://math.iitm.ac.in/innerfaculty.php?fname=Sivaram', 15, 1, '2026-08-24 10:13:59', '2026-08-28 08:13:58', 0, 0),
(64, '3', 'Barun Sarkar', 'barunsarkar.png', 'Mathematics', 'Faculty', 'barun[at]smail[.]iitm[.]ac[.]in', 'https://math.iitm.ac.in/innerfaculty.php?fname=Barun%20Sarkar', 16, 1, '2026-08-24 10:13:59', '2026-08-28 08:14:52', 0, 0),
(65, '3', 'Rakhi Singh', 'WhatsApp Image 2025-01-13 at 9.12.00 AM.jpeg', 'Mathematics', 'Faculty', 'rakhi[at]smail[.]iitm[.]ac[.]in', 'https://math.iitm.ac.in/innerfaculty.php?fname=Rakhi%20Singh', 17, 1, '2026-08-24 10:13:59', '2026-08-28 08:16:30', 0, 0),
(66, '3', 'Balaji Srinivasan', 'sbalaji.png', 'Mechanical Engineering', 'Faculty', 'sbalaji[at]smail[.]iitm[.]ac[.]in', 'https://mech.iitm.ac.in/profile.php?fname=sbalaji', 18, 1, '2026-08-24 10:13:59', '2026-08-28 08:20:29', 0, 0),
(67, '3', 'Gandham Phanikumar', 'gandham.png', 'Metallurgical and Materials Engineering', 'Faculty', 'gphani[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Gandham%20Phanikumar', 19, 1, '2026-08-24 10:13:59', '2026-08-28 08:34:18', 0, 0),
(68, '3', 'Neelima M Gupte', 'gupte.jpg', 'Physics', 'Faculty', 'gupte[at]physics[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/gupte.html', 20, 1, '2026-08-24 10:13:59', '2026-08-28 08:56:49', 0, 0),
(69, '4', 'Mahesh V Panchagnula', 'mahesh-panchagnula.CDvs0crw_2vvUx9.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'mvp[at]smail[.]iitm[.]ac[.]in', 'https://home.iitm.ac.in/mvp/', 1, 1, '2026-08-24 10:15:50', '2026-08-27 07:50:24', 0, 0),
(70, '4', 'A N Rajagopalan', 'rajagopalan-an.png', 'Electrical Engineering', 'Faculty', 'raju[at]ee[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/rajagopalan-an/', 2, 1, '2026-08-24 10:15:50', '2026-08-28 07:29:58', 0, 0),
(71, '4', 'Babji Srinivasan', 'profile.jpeg', 'Applied Mechanics & Biomedical Engineering', 'Faculty', 'babji[.]srinivasan[at]iitm[.]ac[.]in', 'https://home.iitm.ac.in/babji.srinivasan/', 3, 1, '2026-08-24 10:15:50', '2026-08-27 07:44:34', 0, 0),
(72, '4', 'Raghunathan Rengaswamy', 'prof-raghu.png', 'Chemical Engineering', 'Faculty', 'raghur[at]iitm[.]ac[.]in', 'https://che.iitm.ac.in/faculty.php?fname=Dr.+Ragunathan+Rengasamy', 4, 1, '2026-08-24 10:15:50', '2026-08-27 08:16:59', 0, 0),
(73, '4', 'Nandan Sudarsanam', 'nandan.jpg.webp', 'Data Science & AI', 'Faculty', 'nandan[at]dsai[.]iitm[.]ac[.]in', 'https://doms.iitm.ac.in/index.php/people/new-profile/nandan-sudarsanam-profile/', 5, 1, '2026-08-24 10:15:50', '2026-08-27 11:33:09', 0, 0),
(74, '4', 'Arunkumar Thittai', 'arun-kumar-thittai.D_sDbPte_ZVnr7O.webp', 'Applied Mechanics & Biomedical Engineering', 'Faculty', 'akthittai[at]iitm[.]ac[.]in', 'https://sites.google.com/view/arunthittai', 6, 1, '2026-08-24 10:15:50', '2026-08-27 07:45:57', 0, 0),
(75, '4', 'Sivaram Ambikasaran', 'dummy.png', 'Data Science & AI', 'Faculty', 'sivaambi[at]dsai[.]iitm[.]ac[.]in', 'https://math.iitm.ac.in/innerfaculty.php?fname=Sivaram', 7, 1, '2026-08-24 10:15:50', '2026-08-27 11:33:59', 0, 0),
(76, '4', 'Manish Anand', 'manand.png', 'Mechanical Engineering', 'Faculty', 'manand[at]iitm[.]ac[.]in', 'https://mech.iitm.ac.in/profile.php?fname=manand', 8, 1, '2026-08-24 10:15:50', '2026-08-28 08:21:18', 0, 0),
(77, '5', 'M. Thenmozhi', '2U2A0852-scaled.jpg-1-e1771587104861.webp', 'Department of Management Studies (DoMS)', 'Faculty', 'mtm[at]iitm[.]ac[.]in', 'https://doms.iitm.ac.in/index.php/people/faculty/thenmozhim/', 1, 1, '2026-08-24 10:16:42', '2026-08-27 11:39:55', 0, 0),
(78, '5', 'Hitika Tiwari', 'image.webp', 'Data Science and AI', 'Faculty', 'hitika[at]iitmz[.]ac[.]in', 'https://www.iitmz.ac.in/schools/engineering-and-science/faculty/prof-hitika-tiwari', 2, 1, '2026-08-24 10:16:42', '2026-08-27 11:34:59', 0, 0),
(79, '5', 'Lata Dyaram', 'lata_dyaram1-2.jpg.webp', 'Department of Management Studies', 'Faculty', 'lata[.]dyaram[at]smail[.]iitm[.]ac[.]in', 'https://doms.iitm.ac.in/index.php/people/faculty/latadayaram/', 3, 1, '2026-08-24 10:16:42', '2026-08-27 11:38:36', 0, 0),
(80, '5', 'Sudhir Chella Rajan', 'prof_chella_rajan-qxk7hm1au5qeodqeberr71shttsdw56zyujfgyd5fc.jpg', 'Humanities and Social Sciences', 'Faculty', 'scrajan[at]smail[.]iitm[.]ac[.]in', 'https://hss.iitm.ac.in/sudhir-chella-rajan', 4, 1, '2026-08-24 10:16:42', '2026-08-28 07:53:45', 0, 0),
(81, '5', 'Subash S', 'Subash_300-r1s0s1f5h88hvkrd0d50i9j439z43jr3n1qm2cjmwo.jpg', 'Humanities and Social Sciences', 'Faculty', 'subash[at]smail[.]iitm[.]ac[.]in', 'https://hss.iitm.ac.in/subash-s', 5, 1, '2026-08-24 10:16:42', '2026-08-28 07:54:26', 0, 0),
(83, '5', 'Pramod Kumar Naik', 'pramod-qog10tlbadmw3bwt05ptn9phc1tqon514e2emtqnjs.png', 'Humanities and Social Sciences', 'Faculty', 'pramod[at]smail[.]iitm[.]ac[.]in', 'https://hss.iitm.ac.in/pramod-kumar-naik', 7, 1, '2026-08-24 10:16:42', '2026-08-28 07:57:18', 0, 0),
(84, '5', 'V.R. Muraleedharan', 'murali.png', 'Humanities and Social Sciences', 'Faculty', 'vrm[at]smail[.]iitm[.]ac[.]in', 'https://hss.iitm.ac.in/muraleedharan-vr/', 8, 1, '2026-08-24 10:16:42', '2026-08-28 08:01:20', 0, 0),
(85, '5', 'Sandeep Kumar Kujur', 'sandeep-qog13bttih2f1w9qd8q0amxqd1gz7l39gsp0pe0uyg.png', 'Humanities and Social Sciences', 'Faculty', 'sandeep[at]smail[.]iitm[.]ac[.]in', 'https://hss.iitm.ac.in/sandeep-kumar-kujur', 9, 1, '2026-08-24 10:16:42', '2026-08-28 08:03:15', 0, 0),
(86, '5', 'Neelesh S Upadhye', 'neelesh.png', 'Mathematics', 'Faculty', 'neelesh[at]smail[.]iitm[.]ac[.]in', 'https://math.iitm.ac.in/innerfaculty.php?fname=Neelesh%20Shankar', 10, 1, '2026-08-24 10:16:42', '2026-08-28 08:16:20', 0, 0),
(87, '6', 'Kothandaraman Ramanujam', '1772728376_3ca6b85bde.jpg', 'Chemistry', 'Faculty', 'rkraman[at]smail[.]iitm[.]ac[.]in', 'https://chem.iitm.ac.in/faculty-inner.php?id=19', 1, 1, '2026-08-24 10:17:19', '2026-08-27 11:00:54', 0, 0),
(88, '6', 'Arun Karuppaswamy B', 'arun-karuppaswamy-b.png', 'Electrical Engineering', 'Faculty', 'akp[at]smail[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/arun-karuppaswamy-b/', 2, 1, '2026-08-24 10:17:19', '2026-08-28 07:30:29', 0, 0),
(89, '6', 'Kamalesh Hatua', 'kamalesh-hatua.png', 'Electrical Engineering', 'Faculty', 'kamalesh[at]ee[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/kamalesh-hatua/', 3, 1, '2026-08-24 10:17:19', '2026-08-28 07:30:57', 0, 0),
(90, '6', 'K Shanti Swarup', 'k-swarup.png', 'Electrical Engineering', 'Faculty', 'swarup[at]ee[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/shanti-k/', 4, 1, '2026-08-24 10:17:19', '2026-08-28 07:32:18', 0, 0),
(91, '6', 'R Sarathi', 'sarathi-r.png', 'Electrical Engineering', 'Faculty', 'rsarathi[at]smail[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/sarathi-r/', 5, 1, '2026-08-24 10:17:19', '2026-08-28 07:32:52', 0, 0),
(92, '6', 'Mahesh Kumar', 'mahesh-kumar.png', 'Electrical Engineering', 'Faculty', 'maheshk[at]smail[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/mahesh-kumar/', 6, 1, '2026-08-24 10:17:19', '2026-08-28 07:33:34', 0, 0),
(93, '6', 'Lakshminarasamma N', 'lakshminarasamma-n.png', 'Electrical Engineering', 'Faculty', 'lakshmin[at]smail[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/lakshminarasamma-n/', 7, 1, '2026-08-24 10:17:19', '2026-08-28 07:34:03', 0, 0),
(94, '6', 'Srikanthan Sridharan', 'SSrikanthan.jpg', 'Engineering Design', 'Faculty', 'srikanthan[at]smail[.]iitm[.]ac[.]in', 'https://ed.iitm.ac.in/faculty.html?id=Srikanthan_S', 8, 1, '2026-08-24 10:17:19', '2026-08-28 07:39:14', 0, 0),
(95, '6', 'Deepak Ronanki', 'deepak.jpg', 'Engineering Design', 'Faculty', 'dronanki[at]smail[.]iitm[.]ac[.]in', 'https://ed.iitm.ac.in/faculty.html?id=Deepak_Ronanki', 9, 1, '2026-08-24 10:17:19', '2026-08-28 07:40:04', 0, 0),
(96, '7', 'Satadal Ghosh', 'Satadal Ghosh.webp', 'Aerospace Engineering', 'Faculty', 'satadal[at]smail[.]iitm[.]ac[.]in', 'https://sites.google.com/smail.iitm.ac.in/satadalghosh', 1, 1, '2026-08-24 10:17:43', '2026-08-27 07:30:37', 0, 0),
(97, '7', 'Devaprakash Muniraj', 'Devaprakash Muniraj.webp', 'Aerospace Engineering', 'Faculty', 'deva[at]smail[.]iitm[.]ac[.]in', 'https://sites.google.com/view/rasas-lab/home', 2, 1, '2026-08-24 10:17:43', '2026-08-27 07:30:01', 0, 0),
(98, '7', 'M Manivannan', 'm-manivannan.BI-MCDrb_RKTXF.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'mani[at]smail[.]iitm[.]ac[.]in', 'https://home.iitm.ac.in/mani/', 3, 1, '2026-08-24 10:17:43', '2026-08-27 07:51:53', 0, 0),
(99, '7', 'Aritra Pal', '1729461288969.jpg', 'Civil Engineering', 'Faculty', 'aritrapal[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/aritrap/', 4, 1, '2026-08-24 10:17:43', '2026-08-27 11:10:38', 0, 0),
(100, '7', 'Ayon Chakraborty', 'ayon.jpg', 'Computer Science and Engineering', 'Faculty', 'ayon[at]cse[.]iitm[.]ac[.]in', 'https://cse.iitm.ac.in/innerfaculty.php?fname=Ayon%20Chakraborty', 5, 1, '2026-08-24 10:17:43', '2026-08-27 11:30:48', 0, 0),
(101, '7', 'Gopalakrishnan Srinivasan', 'gopal.png', 'Computer Science and Engineering', 'Faculty', 'sgopal[at]cse[.]iitm[.]ac[.]in', 'https://cse.iitm.ac.in/innerfaculty.php?fname=Gopalakrishnan%20Srinivasan', 6, 1, '2026-08-24 10:17:43', '2026-08-27 11:31:22', 0, 0),
(102, '7', 'Chester Rebeiro', 'chester.png', 'Computer Science and Engineering', 'Faculty', 'chester[at]cse[.]iitm[.]ac[.]in', 'https://cse.iitm.ac.in/innerfaculty.php?fname=Chester%20Rebeiro', 7, 1, '2026-08-24 10:17:43', '2026-08-27 11:31:56', 0, 0),
(103, '7', 'Patanjali', 'dummy.png', 'Data Science and AI', 'Faculty', 'patanjali[at]dsai[.]iitm[.]ac[.]in', '#', 8, 1, '2026-08-24 10:17:43', '2026-08-24 10:47:25', 0, 0),
(104, '7', 'Arunkumar D Mahindrakar', 'arun-d-mahindrakar.png', 'Electrical Engineering', 'Faculty', 'arun_dm[at]ee[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/arun-d-mahindrakar/', 9, 1, '2026-08-24 10:17:43', '2026-08-28 07:35:46', 0, 0),
(105, '7', 'Puduru Viswanadha Reddy', 'puduru-reddy.png', 'Electrical Engineering', 'Faculty', 'vishwa[at]smail[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/puduru-reddy/', 10, 1, '2026-08-24 10:17:43', '2026-08-28 07:36:12', 0, 0),
(106, '7', 'Bijo Sebastian', 'bijo.jpg', 'Engineering Design', 'Faculty', 'bijo[.]sebastian[at]smail[.]iitm[.]ac[.]in', 'https://ed.iitm.ac.in/faculty.html?id=Bijo_Sebastian', 11, 1, '2026-08-24 10:17:43', '2026-08-28 07:40:33', 0, 0),
(107, '7', 'Niravkumar Patel', 'Nirav_Patel.jpg', 'Engineering Design', 'Faculty', 'niravpatel[at]smail[.]iitm[.]ac[.]in', 'https://ed.iitm.ac.in/faculty.html?id=Nirav_Patel', 12, 1, '2026-08-24 10:17:43', '2026-08-28 07:40:56', 0, 0),
(108, '7', 'Sandipan Bandyopadhyay', 'SBandyopadhyay.jpg', 'Engineering Design', 'Faculty', 'sandipan[at]smail[.]iitm[.]ac[.]in', 'https://ed.iitm.ac.in/faculty.html?id=Sandipan_Bandyopadhyay', 13, 1, '2026-08-24 10:17:43', '2026-08-28 07:41:19', 0, 0),
(109, '7', 'Santanu Sarkar', '1749709804_2e344f9d6954da9d.jpg', 'Mathematics', 'Faculty', 'santanu[at]smail[.]iitm[.]ac[.]in', 'https://math.iitm.ac.in/innerfaculty.php?fname=Santanu%20Sarkar', 14, 1, '2026-08-24 10:17:43', '2026-08-28 08:17:21', 0, 0),
(110, '7', 'Krishnan Balasubramanian', 'balas.png', 'Mechanical Engineering', 'Faculty', 'balas[at]smail[.]iitm[.]ac[.]in', 'https://mech.iitm.ac.in/profile.php?fname=balas', 15, 1, '2026-08-24 10:17:43', '2026-08-28 08:22:16', 0, 0),
(111, '7', 'Anuj Kumar Tiwari', 'anujt.png', 'Mechanical Engineering', 'Faculty', 'anujt[at]smail[.]iitm[.]ac[.]in', 'https://mech.iitm.ac.in/profile.php?fname=anujt', 16, 1, '2026-08-24 10:17:43', '2026-08-28 08:22:44', 0, 0),
(112, '7', 'Abhilash Somayajula', 'img_6938ae6358bb98.66689705.jpg', 'Ocean Engineering', 'Faculty', 'abhilash[at]smail[.]iitm[.]ac[.]in', 'https://doe.iitm.ac.in/abhilash', 17, 1, '2026-08-24 10:17:43', '2026-08-28 08:44:21', 0, 0),
(113, '8', 'Satyanarayanan Seshadri', 'satyanarayanan-seshadri.C3EpzFCj_1TxrGS.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'satya[at]iitm[.]ac[.]in', 'https://energlab.com/', 1, 1, '2026-08-24 10:18:26', '2026-08-27 07:52:37', 0, 0),
(114, '9', 'Ashwin Mahalingam', 'ashwin.jpg', 'Civil Engineering', 'Faculty', 'mash[at]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/mash/', 1, 1, '2026-08-24 10:19:09', '2026-08-27 11:11:19', 0, 0),
(115, '9', 'Manikandan Mathur Sankaranarayanan', 'Manikandan Mathur.webp', 'Aerospace Engineering', 'Faculty', 'manims[at]smail[.]iitm[.]ac[.]in', 'https://sites.google.com/site/mathur2m/home', 2, 1, '2026-08-24 10:19:09', '2026-08-27 07:31:20', 0, 0),
(116, '9', 'Saumendra K. Bajpai', 'saumendra-kumar-bajpai.B1vrE_8x_1PgEy3.webp', 'Applied Mechanics and Biomedical Engineering', 'Faculty', 'sbajpai[at]smail[.]iitm[.]ac[.]in', 'https://cellmechanics.blogspot.com/', 3, 1, '2026-08-24 10:19:09', '2026-08-27 07:53:08', 0, 0),
(117, '9', 'Guhan Jayaraman', 'Guhan Jayaraman.png', 'Biotechnology', 'Faculty', 'guhanj[at]smail[.]iitm[.]ac[.]in', 'https://biotech.iitm.ac.in/innerfaculty.php?fname=Guhan+Jayaraman', 4, 1, '2026-08-24 10:19:09', '2026-08-27 07:58:37', 0, 0),
(118, '9', 'Rayala Suresh Kumar', 'Suresh Rayala.png', 'Biotechnology', 'Faculty', 'rayala[at]smail[.]iitm[.]ac[.]in', 'https://biotech.iitm.ac.in/innerfaculty.php?fname=Suresh+Kumar+', 5, 1, '2026-08-24 10:19:09', '2026-08-27 07:59:10', 0, 0),
(119, '9', 'Sathyanarayana N. Gummadi', 'sathya.png', 'Biotechnology', 'Faculty', 'gummadi[at]smail[.]iitm[.]ac[.]in', 'https://biotech.iitm.ac.in/innerfaculty.php?fname=Sathyanarayana+N+Gummadi', 6, 1, '2026-08-24 10:19:09', '2026-08-27 07:59:36', 0, 0),
(120, '9', 'Rajnish Kumar', 'rajnish.png', 'Chemical Engineering', 'Faculty', 'rajnish[at]smail[.]iitm[.]ac[.]in', 'https://che.iitm.ac.in/faculty.php?fname=Dr.+Rajnish+Kumar', 7, 1, '2026-08-24 10:19:09', '2026-08-27 08:19:23', 0, 0),
(121, '9', 'Sankha Karmakar', 'sanka.png', 'Chemical Engineering', 'Faculty', 'skarmakar[at]smail[.]iitm[.]ac[.]in', 'https://che.iitm.ac.in/faculty.php?fname=Dr.+Sankha+Karmakar', 8, 1, '2026-08-24 10:19:09', '2026-08-27 08:19:54', 0, 0),
(122, '9', 'Jithin John Varghese', 'jithin.png', 'Chemical Engineering', 'Faculty', 'jithinjv[at]smail[.]iitm[.]ac[.]in', 'https://che.iitm.ac.in/faculty.php?fname=Dr.+Jithin+John+Varghese', 9, 1, '2026-08-24 10:19:09', '2026-08-27 08:20:22', 0, 0),
(123, '9', 'NITIN MURALIDHARAN', 'WhatsApp Image 2024-11-05 at 2.43.21 PM.jpg', 'Chemical Engineering', 'Faculty', 'muralidharan[at]smail[.]iitm[.]ac[.]in', 'https://che.iitm.ac.in/faculty.php?fname=Dr.+Nitin+Muralidharan', 10, 1, '2026-08-24 10:19:09', '2026-08-27 08:20:56', 0, 0),
(124, '9', 'Ramesh L. Gardas', '1772778304_fda6f5d870.jpg', 'Chemistry', 'Faculty', 'gardas[at]smail[.]iitm[.]ac[.]in', 'https://chem.iitm.ac.in/faculty-inner.php?id=28', 11, 1, '2026-08-24 10:19:09', '2026-08-27 11:01:20', 0, 0),
(125, '9', 'Sachin S. Gunthe', 'SachinSGunthe(2) - Sachin S Gunthe II.jpg', 'Civil Engineering', 'Faculty', 's[.]gunthe[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/sgunthe/', 12, 1, '2026-08-24 10:19:09', '2026-08-27 11:11:55', 0, 0),
(126, '9', 'Chandan Sarangi', 'Chandan_Sarangi.jpg', 'Civil Engineering', 'Faculty', 'chandansarangi[at]civil[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/chandansarangi/', 13, 1, '2026-08-24 10:19:09', '2026-08-27 11:12:41', 0, 0),
(127, '9', 'Indumathi Nambi', 'IMN-IndumathiNambi.jpg', 'Civil Engineering', 'Faculty', 'indunambi[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/indunambi/', 14, 1, '2026-08-24 10:19:09', '2026-08-27 11:13:08', 0, 0),
(128, '9', 'Mathava Kumar S', 'Mathava Kumar S - EWRE-CE - Mathava Kumar S IITM.jpg', 'Civil Engineering', 'Faculty', 'mathav[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/mathava/', 15, 1, '2026-08-24 10:19:09', '2026-08-27 11:13:39', 0, 0),
(129, '9', 'Prakash Singh Badal', 'Psbimage.jpg', 'Civil Engineering', 'Faculty', 'psb[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/prakash/', 16, 1, '2026-08-24 10:19:09', '2026-08-27 11:14:15', 0, 0),
(130, '9', 'Anmol Pahwa', 'Profile Photo.jpg', 'Civil Engineering', 'Faculty', 'anmpahwa[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/anmpahwa/', 17, 1, '2026-08-24 10:19:09', '2026-08-27 11:14:48', 0, 0),
(132, '9', 'Ligy Philip', 'DSC_1084.jpg', 'Civil Engineering', 'Faculty', 'ligy[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/ligy/', 19, 1, '2026-08-24 10:19:09', '2026-08-27 11:16:02', 0, 0),
(133, '9', 'Venkatraman Srinivasan', 'venkatram.jpg', 'Civil Engineering', 'Faculty', 'venkatraman[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/venkatraman/', 20, 1, '2026-08-24 10:19:09', '2026-08-27 11:16:47', 0, 0),
(134, '9', 'Nikhil Bugalia', '20.jpg', 'Civil Engineering', 'Faculty', 'nbugalia[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/nbugalia/', 21, 1, '2026-08-24 10:19:09', '2026-08-27 11:17:12', 0, 0),
(135, '9', 'Shiva Nagendra SM', 'Shiva Nagendra SM - Shiva Nagendra.jpg', 'Civil Engineering', 'Faculty', 'snagendra[at]smail[.]iitm[.]ac[.]in', 'https://civil.iitm.ac.in/faculty/snagendra/', 22, 1, '2026-08-24 10:19:09', '2026-08-27 11:19:26', 0, 0),
(136, '9', 'Balaji Srinivasan', 'balaji-srinivasan.png', 'Electrical Engineering', 'Faculty', 'balajis[at]ee[.]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/balaji-srinivasan/', 23, 1, '2026-08-24 10:19:09', '2026-08-28 07:36:47', 0, 0),
(137, '9', 'Harikrishna', 'dummy.png', 'Engineering Design', 'Faculty', 'ed17d009[at]smail[.]iitm[.]ac[.]in', '#', 24, 1, '2026-08-24 10:19:09', '2026-08-24 10:47:25', 0, 0),
(138, '9', 'Atriya Biswas', 'ABiswas.jpg', 'Engineering Design', 'Faculty', 'abiswas[at]smail[.]iitm[.]ac[.]in', 'https://ed.iitm.ac.in/faculty.html?id=Atriya_Biswas', 25, 1, '2026-08-24 10:19:09', '2026-08-28 07:43:46', 0, 0),
(139, '9', 'Kavitha Arunachalam', 'Kavitha_Arunachalam.jpg', 'Engineering Design', 'Faculty', 'akavitha[at]smail[.]iitm[.]ac[.]in', 'https://ed.iitm.ac.in/faculty.html?id=Kavitha_Arunachalam', 26, 1, '2026-08-24 10:19:09', '2026-08-28 07:46:42', 0, 0),
(140, '9', 'Sabuj Kumar Mandal', 'SabujKumar1-qog11140t1x6o7lvs8yu77t634soe7yvtfaah1fi60.png', 'Humanities and Social Sciences', 'Faculty', 'sabuj[at]smail[.]iitm[.]ac[.]in', 'https://hss.iitm.ac.in/sabuj-kumar-mandal', 27, 1, '2026-08-24 10:19:09', '2026-08-28 08:04:23', 0, 0),
(143, '9', 'GL Samuel', 'samuelgl.png', 'Mechanical Engineering', 'Faculty', 'samuelgl[at]smail[.]iitm[.]ac[.]in', 'https://mech.iitm.ac.in/profile.php?fname=samuelgl', 30, 1, '2026-08-24 10:19:09', '2026-08-28 08:23:07', 0, 0),
(144, '9', 'Bhuvanesh Srinivasan', 'bhuvan.png', 'Metallurgical and Materials Engineering', 'Faculty', 'bhuvanesh[.]srini[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Bhuvanesh%20Srinivasan', 31, 1, '2026-08-24 10:19:09', '2026-08-28 08:35:12', 0, 0),
(145, '9', 'Lakshman Neelakantan', 'lakshman.png', 'Metallurgical and Materials Engineering', 'Faculty', 'nlakshman[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Lakshman%20Neelakantan', 32, 1, '2026-08-24 10:19:09', '2026-08-28 08:36:50', 0, 0),
(146, '9', 'Ajay Kumar Shukla', 'shukla.png', 'Metallurgical and Materials Engineering', 'Faculty', 'shukla[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Shukla%20Ajay%20Kumar', 33, 1, '2026-08-24 10:19:09', '2026-08-28 08:37:13', 0, 0),
(147, '9', 'Tiju Thomas', 'tiju.png', 'Metallurgical and Materials Engineering', 'Faculty', 'tijuthomas[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Tiju%20Thomas', 34, 1, '2026-08-24 10:19:09', '2026-08-28 08:37:36', 0, 0),
(148, '9', 'S. Sankaran', 'sankaran.png', 'Metallurgical and Materials Engineering', 'Faculty', 'ssankaran[at]smail[.]iitm[.]ac[.]in', 'https://mme.iitm.ac.in/innerfaculty.php?fname=Sankaran%20S', 35, 1, '2026-08-24 10:19:09', '2026-08-28 08:37:57', 0, 0),
(149, '9', 'Somnath C Roy', 'somnath.jpg', 'Physics', 'Faculty', 'somnath[at]smail[.]iitm[.]ac[.]in', 'https://physics.iitm.ac.in/people/facultyinfo/somnath.html', 36, 1, '2026-08-24 10:19:09', '2026-08-28 08:57:37', 0, 0),
(152, '', 'Anbarasu Manivannan', 'anbu.jpg', 'Department of Electrical Engineering', 'Head, School of Interdisciplinary Studies', 'anbarasu[at]iitm[.]ac[.]in', 'https://ee.iitm.ac.in/people/anbarasu-manivannan/', 0, 1, '2026-08-24 10:24:00', '2026-09-05 08:04:26', 0, 1),
(153, '1', 'Ravindra Naik Bukke', 'dummy.png', 'School of Interdisciplinary Studies', 'Assistant Professor', 'ravindra[at]iitm[.]ac[.]in', '#', 0, 1, '2026-08-24 10:24:00', '2026-08-24 11:18:38', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `featured_research`
--

CREATE TABLE `featured_research` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `featured_research`
--

INSERT INTO `featured_research` (`id`, `title`, `link`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Toward Stable and High‐Performance Metal Halide Perovskite Field‐Effect Transistors. (2026)', 'https://advanced.onlinelibrary.wiley.com/doi/full/10.1002/aelm.202500749', 'feat1.png', 1, '2026-08-20 09:36:12', '2026-08-20 09:37:23'),
(2, 'Wide Angle Polarization-Independent 6-Bit Optical Modulator Using Phase Change Material. (2026)', 'https://pubs.acs.org/doi/full/10.1021/acs.nanolett.5c05799', 'feat2.png', 1, '2026-08-20 09:36:12', '2026-08-20 09:36:12'),
(3, 'An Ultra-Thin Amplitude-Tunable Microwave Absorber Using Nanometer-Thick GeTe Phase Change Material. (2026)', 'https://pubs.acs.org/doi/full/10.1021/acsaelm.6c00724', 'feat3.png', 1, '2026-08-20 09:36:12', '2026-08-20 09:36:12'),
(4, 'Next-gen magnetic field sensing using quantum defects', 'https://qmettech.com/technical-group-3/', 'feat4.png', 1, '2026-08-20 09:36:12', '2026-08-20 09:36:12');

-- --------------------------------------------------------

--
-- Table structure for table `guidelines`
--

CREATE TABLE `guidelines` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guideline` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `guidelines`
--

INSERT INTO `guidelines` (`id`, `title`, `guideline`) VALUES
(1, 'Norms to propose a new IDDD program', 'Justification for the interdisciplinary nature of the program'),
(2, 'Norms to propose a new IDDD program', 'The need for the proposed program and the expected outcome of the program'),
(3, 'Norms to propose a new IDDD program', 'Buy-in from existing cluster or creation of new cluster.'),
(4, 'Norms to propose a new IDDD program', 'Interested group of faculty members (from multiple departments) and their commitment in overall execution.'),
(5, 'Norms to propose a new IDDD program', 'Commitment from potential course instructors (minimum 3-4 instructors) for at least three years for each course in the proposed curriculum.'),
(6, 'Norms to invite existing faculty members to SIDiS', 'Justification to be provided on the choice of suitable clusters based on their expertise with relevant research accomplishments.'),
(7, 'Norms to invite existing faculty members to SIDiS', 'Contributions made to exiting IDDD, I2MP courses in the cluster. If not, willingness to teach courses offered in IDDD, I2MP programs for at least three years.'),
(8, 'Norms to invite existing faculty members to SIDiS', 'Willingness to contribute to research programs of the cluster.'),
(9, 'Norms to invite existing faculty members to SIDiS', 'Willingness to execute sponsored/consultancy projects through SIDiS'),
(10, 'Norms to invite existing faculty members to SIDiS', 'Commitment to participate and contribute to administrative responsibilities of SIDiS and SCC.'),
(11, 'Norms to invite existing faculty members to SIDiS', 'Faculty contribution in Teaching, Research guidance, publications, SR, IC, and Internationalization in the areas of the cluster.'),
(12, 'Norms to start a new cluster', 'Rationale of formation of a new cluster – opportunities, demand, and justification on why the proposed cluster does not fit within existing clusters'),
(13, 'Norms to start a new cluster', 'Number of participating faculty members (from more than two departments), and identified faculty members and their commitment in overall execution.'),
(14, 'Norms to start a new cluster', 'The group of faculty members proposing the cluster, shall understand that full-time faculty members directly under SIDiS may not be recruited.'),
(15, 'Norms to start a new cluster', 'National and International Scenario of proposed domain and its significance on why such cluster to be formed at IITM.'),
(16, 'Norms to start a new cluster', 'Commitment from potential faculty members to be part of the new cluster and their willingness to be course instructors (at least three) for at least three years for each course in the proposed curriculum.'),
(17, 'Performance evaluation of existing clusters', 'IDDD, I2MP Students enrollment for the entire period'),
(18, 'Performance evaluation of existing clusters', 'Research scholars’ performance and progress'),
(19, 'Performance evaluation of existing clusters', 'Sponsored, consultancy projects'),
(20, 'Performance evaluation of existing clusters', 'Placement activities'),
(21, 'Performance evaluation of existing clusters', 'International collaborations, joint research students, and projects');

-- --------------------------------------------------------

--
-- Table structure for table `head_message`
--

CREATE TABLE `head_message` (
  `id` int UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `head_message`
--

INSERT INTO `head_message` (`id`, `description`, `name`, `designation`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Welcome to the School of Interdisciplinary Studies (SIDiS) at IIT Madras. SIDiS is dedicated to advancing research and education at the intersections of science, engineering, technology, humanities, and policy. As complex global challenges increasingly demand integrated solutions, SIDiS provides a collaborative platform to drive innovation in emerging areas such as advanced materials, energy systems, sustainability, and climate science.\n\nOur mission is to cultivate a new generation of engineers and researchers with strong interdisciplinary capabilities, enabling them to work seamlessly across traditional boundaries and contribute to impactful technological and societal solutions.\n\nTo support this vision, SIDiS is organized into nine thematic clusters: Advanced Materials and Quantum Initiative, Blue Economy, Computational Engineering, Management & Public Policy, Power Conversion Systems, Robotics and Cyber-Physical Systems, School of Sustainability, School of Innovation & Entrepreneurship, and Sports Science and Analytics. Through these focused clusters, SIDiS fosters cutting-edge research, promotes academic collaboration, and strengthens engagement with industry and policy ecosystems.', 'Prof. Anbarasu Manivannan', 'Head, School of Interdisciplinary Studies', 'head_1789463306_4cfbb12f8e837076.jpg', '2026-08-20 07:08:36', '2026-09-15 09:08:26');

-- --------------------------------------------------------

--
-- Table structure for table `hero_section`
--

CREATE TABLE `hero_section` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_section`
--

INSERT INTO `hero_section` (`id`, `title`, `subtitle`, `image`, `created_at`, `updated_at`) VALUES
(1, 'School of Interdisciplinary Studies', 'IIT Madras', 'hero_1789957414_9bf0182a2d47b5b9.jpg', '2026-08-20 07:01:54', '2026-09-21 02:23:34');

-- --------------------------------------------------------

--
-- Table structure for table `message_from_director`
--

CREATE TABLE `message_from_director` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `iframe_url` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_from_director`
--

INSERT INTO `message_from_director` (`id`, `title`, `iframe_url`, `created_at`, `updated_at`) VALUES
(1, 'Message from the Director', 'https://drive.google.com/file/d/1BISZLMTUsMHNxyOLKIoP6sqNsA5vNsvT/preview', '2026-08-20 06:46:22', '2026-09-15 07:55:33');

-- --------------------------------------------------------

--
-- Table structure for table `ms`
--

CREATE TABLE `ms` (
  `id` int UNSIGNED NOT NULL,
  `batch` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roll_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mentor_1` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mentor_2` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ms`
--

INSERT INTO `ms` (`id`, `batch`, `roll_number`, `student_name`, `mail_id`, `mentor_1`, `mentor_2`) VALUES
(1, 'Jan 2025', 'ID24S002', 'Balaji V S', 'id24ds002@smail.iitm.ac.in', '', ''),
(2, 'Jan 2025', 'ID24S800', 'Haambayi Mudenda', 'id24s800@smail.iitm.ac.in', NULL, NULL),
(3, 'Jul 2025', 'ID25S001', 'Saptajit Banerjee', 'ID25S001@SMAIL.IITM.AC.IN', NULL, NULL),
(4, 'Jul 2025', 'ID25S004', 'Tanushree Hajare', 'ID25S004@SMAIL.IITM.AC.IN', NULL, NULL),
(5, 'Jul 2025', 'ID25S006', 'CHITTOOR SAI GANESH', 'ID25S006@SMAIL.IITM.AC.IN', NULL, NULL),
(6, 'Jul 2025', 'ID25S007', 'Komal', 'ID25S007@SMAIL.IITM.AC.IN', NULL, NULL),
(7, 'Jul 2025', 'ID25S009', 'Nasrin Ansari', 'ID25S009@SMAIL.IITM.AC.IN', NULL, NULL),
(8, 'Jul 2025', 'ID25S012', 'Barbie Sharma', 'ID25S012@SMAIL.IITM.AC.IN', NULL, NULL),
(9, 'Jul 2025', 'ID25S014', 'Ashutosh Kumar Singh Rathore', 'ID25S014@SMAIL.IITM.AC.IN', NULL, NULL),
(10, 'Jul 2025', 'ID25S017', 'Ayush Singh', 'ID25S017@SMAIL.IITM.AC.IN', NULL, NULL),
(11, 'Jul 2025', 'ID25S405', 'Gatram Sravan Kumar', 'ID25S405@SMAIL.IITM.AC.IN', NULL, NULL),
(12, 'Jul 2025', 'ID25S411', 'Sankara Narayanan', 'ID25S411@SMAIL.IITM.AC.IN', NULL, NULL),
(13, 'Jul 2025', 'ID25S409', 'Mahalakshmi S', 'ID25S409@SMAIL.IITM.AC.IN', NULL, NULL),
(14, 'Jul 2025', 'ID25S407', 'Gokula Vishnu Kirti Damodaran', 'ID25S407@SMAIL.IITM.AC.IN', NULL, NULL),
(15, 'Jul 2025', 'ID25S406', 'Shri Jayanthi', 'ID25S406@SMAIL.IITM.AC.IN', NULL, NULL),
(16, 'Jul 2025', 'ID25S403', 'KARTHEEK KORLEPARA', 'ID25S403@SMAIL.IITM.AC.IN', NULL, NULL),
(17, 'Jan 2026', 'ID25S027', 'Puneet', 'id25s027@smail.iitm.ac.in', NULL, NULL),
(18, 'Jan 2026', 'ID25S024', 'Ashutosh Ghosh', 'id25s024@smail.iitm.ac.in', NULL, NULL),
(19, 'Jan 2026', 'ID25S029', 'Neethya L', 'id25s029@smail.iitm.ac.in', NULL, NULL),
(20, 'Jan 2026', 'ID25S030', 'K Srirag', 'id25s030@smail.iitm.ac.in', NULL, NULL),
(21, 'Jan 2026', 'ID25S020', 'Ribhu Vajpeyi', 'id25s020@smail.iitm.ac.in', NULL, NULL),
(22, 'Jan 2026', 'ID25S800', 'Alemayehu Tesfaye Yihunie', 'id25s800@smail.iitm.ac.in', NULL, NULL),
(23, 'Jul 2026', 'ID26S001', 'Dweep Vartak', 'id26s001@smail.iitm.ac.in', NULL, NULL),
(24, 'Jul 2026', 'ID26S004', 'Abhay K', 'id26s004@smail.iitm.ac.in', NULL, NULL),
(25, 'Jul 2026', 'ID26S008', 'VRUTHIKA L S', 'id26s008@smail.iitm.ac.in', NULL, NULL),
(26, 'Jul 2026', 'ID26S010', 'N Naga Deepak Krishna', 'id26s010@smail.iitm.ac.in', NULL, NULL),
(27, 'Jul 2026', 'ID26S011', 'MUTHU NITHIN V', 'id26s011@smail.iitm.ac.in', NULL, NULL),
(28, 'Jul 2026', 'ID26S012', 'VASANTHARAN K', 'id26s012@smail.iitm.ac.in', NULL, NULL),
(29, 'Jul 2026', 'ID26S013', 'Fazal', 'id26s013@smail.iitm.ac.in', NULL, NULL),
(30, 'Jul 2026', 'ID26S016', 'Suvarnika O', 'id26s016@smail.iitm.ac.in', NULL, NULL),
(31, 'Jul 2026', 'ID26S017', 'Sakthi Meenakshi A', 'id26s017@smail.iitm.ac.in', NULL, NULL),
(32, 'Jul 2026', 'ID26S031', 'DHARSHAN S', 'id26s031@smail.iitm.ac.in', NULL, NULL),
(33, 'Jul 2026', 'ID26S400', 'Gati Ambaliya', 'id26s400@smail.iitm.ac.in', NULL, NULL),
(34, 'Jul 2026', 'ID26S424', 'Prapthi Sanghi', 'id26s424@smail.iitm.ac.in', NULL, NULL),
(35, 'Jul 2026', 'ID26S422', 'N P J Vedhaanth', 'id26s422@smail.iitm.ac.in', NULL, NULL),
(36, 'Jul 2026', 'ID26S423', 'Samuel Mugin J', 'id26s423@smail.iitm.ac.in', NULL, NULL),
(37, 'Jul 2026', 'ID26S408', 'Gautam Swaminathan', 'id26s408@smail.iitm.ac.in', NULL, NULL),
(38, 'Jul 2026', 'ID26S409', 'Vishal A', 'id26s409@smail.iitm.ac.in', NULL, NULL),
(39, 'Jul 2026', 'ID26S410', 'Suriya Dileepan', 'id26s410@smail.iitm.ac.in', NULL, NULL),
(40, 'Jul 2026', 'ID26S412', 'Elango', 'id26s412@smail.iitm.ac.in', NULL, NULL),
(41, 'Jul 2026', 'ID26S413', 'Abhimanyu Kumar', 'id26s413@smail.iitm.ac.in', NULL, NULL),
(42, 'Jul 2026', 'ID26S414', 'Sathvik P Narayan', 'id26s414@smail.iitm.ac.in', NULL, NULL),
(43, 'Jul 2026', 'ID26S415', 'Sugan D', 'id26s415@smail.iitm.ac.in', NULL, NULL),
(44, 'Jul 2026', 'ID26S418', 'Badrinarayanan Rangarajan', 'id26s418@smail.iitm.ac.in', NULL, NULL),
(45, 'Jul 2026', 'ID26S419', 'Sasikumar V G', 'id26s419@smail.iitm.ac.in', NULL, NULL),
(46, 'Jul 2026', 'ID26S420', 'Roopesh Cuppala', 'id26s420@smail.iitm.ac.in', NULL, NULL),
(47, 'Jul 2026', 'ID26S421', 'Manu Areraa A K', 'id26s421@smail.iitm.ac.in', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mtech_students`
--

CREATE TABLE `mtech_students` (
  `id` int NOT NULL,
  `programme` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `roll_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `student_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mentor_1` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mentor_2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mtech_students`
--

INSERT INTO `mtech_students` (`id`, `programme`, `roll_number`, `student_name`, `mentor_1`, `mentor_2`) VALUES
(1, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE23B038', 'PUNYA MALAIYA JAIN', NULL, NULL),
(2, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE23B044', 'CHITHARTH JEEVACHITHAN', NULL, NULL),
(3, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE23B052', 'NIRANJAN BOLLINENI', NULL, NULL),
(4, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE23B053', 'NITHYASHREE PRABHU', NULL, NULL),
(5, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE23B058', 'SHRAVANI KELAPURE', NULL, NULL),
(6, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE23B102', 'KUTRALEESWARAN NATTAMAI HARIKRISHNAN', NULL, NULL),
(7, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE23B001', 'ABHINAYA A', NULL, NULL),
(8, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE23B003', 'AVHAD AYUSH PRAVIN', NULL, NULL),
(9, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE23B006', 'JENITH CYRIL SINGH', NULL, NULL),
(10, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE23B011', 'RAM REDDY GARI NANDITHA', NULL, NULL),
(11, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE23B018', 'SANCHANA V L', NULL, NULL),
(12, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE23B020', 'AYUSH REPSWAL', NULL, NULL),
(13, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE23B022', 'SHASHWAT KEDIA', NULL, NULL),
(14, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE23B037', 'PRANEESH B', NULL, NULL),
(15, 'International Interdisciplinary Masters Program (I2MP)', 'ID25M808', 'Zewdu Assefa Yemane', NULL, NULL),
(16, 'International Interdisciplinary Masters Program (I2MP)', 'ID25M801', 'Dipta Talukder', NULL, NULL),
(17, 'International Interdisciplinary Masters Program (I2MP)', 'ID25M802', 'Kanak Agarwal', NULL, NULL),
(18, 'International Interdisciplinary Masters Program (I2MP)', 'ID25M803', 'Kowshik Arko Dey', NULL, NULL),
(19, 'International Interdisciplinary Masters Program (I2MP)', 'ID25M806', 'Nitesh Kumar', NULL, NULL),
(20, 'International Interdisciplinary Masters Program (I2MP)', 'ID25M804', 'Md Aminul Islam Rony', NULL, NULL),
(21, 'International Interdisciplinary Masters Program (I2MP)', 'ID25M805', 'Nikesh Kumar Mandal', NULL, NULL),
(22, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M801', 'Arnold Naison Mutasa', NULL, NULL),
(23, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M802', 'Bakwatana George William', NULL, NULL),
(24, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M803', 'Benjamin Ahumuza', NULL, NULL),
(25, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M804', 'Bibusa Shipilo', NULL, NULL),
(26, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M805', 'David Ayemlo Esuga Mopah', NULL, NULL),
(27, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M807', 'Habtamua', NULL, NULL),
(28, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M808', 'Mohinee Prithula', NULL, NULL),
(29, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M811', 'Rishav Dev Paudel', NULL, NULL),
(30, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M812', 'Tajer Kemer Adam', NULL, NULL),
(31, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M813', 'Vicknarajah Nilesh', NULL, NULL),
(32, 'International Interdisciplinary Masters Program (I2MP)', 'ID26M814', 'Wycliffe Musyoki Kyalo', NULL, NULL),
(33, 'Joint Masters Program (JMP)', 'EE25C003', 'Dedipya Singh Yadav', NULL, NULL),
(34, 'Joint Masters Program (JMP)', 'EE25C004', 'Karishma K', NULL, NULL),
(35, 'Joint Masters Program (JMP)', 'EE25C005', 'Konduru Sravanth Kumar Raju', NULL, NULL),
(36, 'Joint Masters Program (JMP)', 'EE25C006', 'Konkipudi Pavan Kumar', NULL, NULL),
(37, 'Joint Masters Program (JMP)', 'EE25C007', 'Raksita Rajagopal', NULL, NULL),
(38, 'Joint Masters Program (JMP)', 'EE25C008', 'Sai S Kalyan', NULL, NULL),
(39, 'Joint Masters Program (JMP)', 'EE25C009', 'Shahul Hameed N', NULL, NULL),
(40, 'Joint Masters Program (JMP)', NULL, 'Komal Jaiswal', NULL, NULL),
(41, 'Joint Masters Program (JMP)', NULL, 'Deeksha Ravishankar', NULL, NULL),
(42, 'Joint Masters Program (JMP)', NULL, 'Aniket Sakharwade', NULL, NULL),
(43, 'Joint Masters Program (JMP)', NULL, 'Yeolekar Yadnya Sandeep', NULL, NULL),
(44, 'Joint Masters Program (JMP)', NULL, 'Brinda Jane J D', NULL, NULL),
(45, 'Joint Masters Program (JMP)', NULL, 'Sasidharan U', NULL, NULL),
(46, 'Joint Masters Program (JMP)', NULL, 'Shreeju Manandhar', NULL, NULL),
(47, 'Joint Masters Program (JMP)', NULL, 'Nikee Thakur', NULL, NULL),
(48, 'Joint Masters Program (JMP)', NULL, 'Paribhasha Gyawali', NULL, NULL),
(49, 'Joint Masters Program (JMP)', NULL, 'Upendra Bahadur Chand', NULL, NULL),
(50, 'Joint Masters Program (JMP)', NULL, 'Aashish Acharya', NULL, NULL),
(52, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B010', 'JOSHUA PHILIP JOHN', NULL, NULL),
(53, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B015', 'AAYUSH PANDA', NULL, NULL),
(54, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B021', 'ELAKKIYA SENTHIL KUMAR', NULL, NULL),
(55, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B022', 'FAISAL MUHIUDDIN SIDDIQI', NULL, NULL),
(56, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B025', 'JADI SRIKANTH', NULL, NULL),
(57, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B030', 'LAUREL BENEDICT X', NULL, NULL),
(58, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B031', 'MIHIR MUTHAIAH K', NULL, NULL),
(59, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B032', 'MOHAMMED MIZAL KODAKKADAN', NULL, NULL),
(60, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B038', 'PRAKHAR SINGH', NULL, NULL),
(61, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B040', 'SARAF BHARAT SURAJ', NULL, NULL),
(62, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS23B041', 'SURYA PRATAP SINGH', NULL, NULL),
(63, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B046', 'LATHA SREE C', NULL, NULL),
(64, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B050', 'PONNOJU SRIVARSHITH', NULL, NULL),
(65, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B059', 'CHAMALA PREM NIHAR REDDY', NULL, NULL),
(66, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B072', 'JAMBHEKAR TANISH SAMIR', NULL, NULL),
(67, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B075', 'JOANN ROSE JIMMY', NULL, NULL),
(68, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B081', 'SANNITH REDDY KASARLA', NULL, NULL),
(69, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B095', 'MURAHARISETTY BANGARUVALLI', NULL, NULL),
(70, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B105', 'PURVA YOGESH PANPALIA', NULL, NULL),
(71, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B110', 'SANJITH M', NULL, NULL),
(72, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B117', 'SUGGU PREM KUMAR', NULL, NULL),
(73, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE23B124', 'VISHWANATH VENUGOPAL', NULL, NULL),
(74, 'Interdisciplinary Dual Degree Program (IDDD)', 'CH23B009', 'ATHARVA DINESH KOKATE', NULL, NULL),
(75, 'Interdisciplinary Dual Degree Program (IDDD)', 'CH23B039', 'DUVVURI VENKATA SATYA PRANAV', NULL, NULL),
(76, 'Interdisciplinary Dual Degree Program (IDDD)', 'CH23B044', 'SIDDHANT RAJESH DANGE', NULL, NULL),
(77, 'Interdisciplinary Dual Degree Program (IDDD)', 'CH23B074', 'ARYAN DOBHAL', NULL, NULL),
(78, 'Interdisciplinary Dual Degree Program (IDDD)', 'CH23B104', 'RAM NARAYAN', NULL, NULL),
(79, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B005', 'ANISH SHRIKANT DESHMUKH', NULL, NULL),
(80, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B007', 'ANUP ATUL KHARUL', NULL, NULL),
(81, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B009', 'ARVIND BHARAT IYER', NULL, NULL),
(82, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B010', 'ARYA SADASHIV DESHMUKH', NULL, NULL),
(83, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B012', 'BHOSALE ARJUN PRAKASH', NULL, NULL),
(84, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B013', 'BOOBALAN MOHAN', NULL, NULL),
(85, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B016', 'DEREK OLIVER F', NULL, NULL),
(86, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B025', 'INIYA DHEIVEEKAN', NULL, NULL),
(87, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B026', 'ISHAN', NULL, NULL),
(88, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B027', 'KANAK KULDEEP VARMA', NULL, NULL),
(89, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B028', 'KARTHIGA M', NULL, NULL),
(90, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B035', 'PERVAJE SURYA PRASAD BHAT', NULL, NULL),
(91, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B036', 'PRANAV ADHITYAA D', NULL, NULL),
(92, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B039', 'RAJAVI NILESH GUJARATHI', NULL, NULL),
(93, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B045', 'VIBHAA SELLADURAI', NULL, NULL),
(94, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B046', 'VISHU KUMAR', NULL, NULL),
(95, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B049', 'HAREGANESH SENTHILKUMAR', NULL, NULL),
(96, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B050', 'HARINI SHAKTHI J', NULL, NULL),
(97, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B051', 'KALYAN S', NULL, NULL),
(98, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B052', 'MANAN AGRAWAL', NULL, NULL),
(99, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B053', 'MANDEEP N H', NULL, NULL),
(100, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B063', 'BHUVANESH S', NULL, NULL),
(101, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B064', 'CHIRAG SOMANI', NULL, NULL),
(102, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B065', 'GURURAJ DINESH THORAT', NULL, NULL),
(103, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B067', 'MALE SOUMIKA', NULL, NULL),
(104, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B070', 'RITVIK T L', NULL, NULL),
(105, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B072', 'SAI SIDDHARTH UPADRASHTA', NULL, NULL),
(106, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B079', 'SHREYAS NAVSALKAR', NULL, NULL),
(107, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B080', 'PAGARIYA PARTH PRAFULL', NULL, NULL),
(108, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED23B081', 'NITHILAN S', NULL, NULL),
(109, 'Interdisciplinary Dual Degree Program (IDDD)', 'EE23B031', 'KAUSHAL R', NULL, NULL),
(110, 'Interdisciplinary Dual Degree Program (IDDD)', 'EP23B001', 'ADRIJA CHATTOPADHYAY', NULL, NULL),
(111, 'Interdisciplinary Dual Degree Program (IDDD)', 'EP23B010', 'DHRUV KAUSHIK', NULL, NULL),
(112, 'Interdisciplinary Dual Degree Program (IDDD)', 'EP23B015', 'MOHAMMED THANOOF VADAKKAN SHIJU', NULL, NULL),
(113, 'Interdisciplinary Dual Degree Program (IDDD)', 'MD23B002', 'AKADE TEJAS DEEPAK', NULL, NULL),
(114, 'Interdisciplinary Dual Degree Program (IDDD)', 'MD23B017', 'NARESH KUMAR MISHRA', NULL, NULL),
(115, 'Interdisciplinary Dual Degree Program (IDDD)', 'MD23B020', 'N T SANJANA', NULL, NULL),
(116, 'Interdisciplinary Dual Degree Program (IDDD)', 'MD23B025', 'SHREYAS BALAKARTHIKEYAN', NULL, NULL),
(117, 'Interdisciplinary Dual Degree Program (IDDD)', 'MD23B028', 'VENKATESH SHARMA', NULL, NULL),
(118, 'Interdisciplinary Dual Degree Program (IDDD)', 'MD23B035', 'RISHI RAMACHANDRAN', NULL, NULL),
(119, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B020', 'ARYAN PRABU S', NULL, NULL),
(120, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B032', 'HIRITHIK YAADHAV K', NULL, NULL),
(121, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B059', 'RAGHAV Y', NULL, NULL),
(122, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B060', 'RAHUL ADISH R', NULL, NULL),
(123, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B070', 'SATHYANAND S', NULL, NULL),
(124, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B082', 'TAMBELA MAHENDRA REDDY', NULL, NULL),
(125, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B104', 'DANIEL CALWIN RIJHU A J', NULL, NULL),
(126, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B115', 'SRINIDHI V', NULL, NULL),
(127, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B116', 'THARASSWIN B', NULL, NULL),
(128, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B119', 'VEMASANI TUSSHAR KRISHNA NAIDU', NULL, NULL),
(129, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B145', 'RAHUL M', NULL, NULL),
(130, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B146', 'RUPAK KANTI GHOSH', NULL, NULL),
(131, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME23B249', 'SRIHARI PRASAD', NULL, NULL),
(132, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B002', 'AYUSH ADARSH', NULL, NULL),
(133, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B005', 'MUKUNTHAN S M', NULL, NULL),
(134, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B009', 'VAKATI VENKATA AKSHIT', NULL, NULL),
(135, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B010', 'VIHAN DARSHAN SHAH', NULL, NULL),
(136, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B018', 'THAKARE JANVI RAJENDRARAO', NULL, NULL),
(137, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B040', 'DEEPTI GIRISH NAGANUR', NULL, NULL),
(138, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B041', 'DEVA SHANKAR JIJI PILLAI', NULL, NULL),
(139, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B044', 'JEREMY SCARIA', NULL, NULL),
(140, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B049', 'MANYAM SRIVALLABH', NULL, NULL),
(141, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B067', 'UGINE MERCY J', NULL, NULL),
(142, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B078', 'CHALLAPALLI NAGA KRISHNA ADITHI', NULL, NULL),
(143, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM23B079', 'PANSHUL HUMAD', NULL, NULL),
(144, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA23B010', 'JEFF JACOB GEORGE', NULL, NULL),
(145, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA23B025', 'JANANI S R', NULL, NULL),
(146, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA23B026', 'KIRUTHIK SANKAR M', NULL, NULL),
(147, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA23B028', 'RITESH S', NULL, NULL),
(148, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA23B041', 'CHARULEKHA B', NULL, NULL),
(149, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA23B062', 'PATNAM ASRITHA', NULL, NULL),
(150, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA23B068', 'SAMEER K S', NULL, NULL),
(151, 'Interdisciplinary Dual Degree Program (IDDD)', 'PH23B013', 'ABHISHEK CHOUDHARY', NULL, NULL),
(152, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B005', 'ABHINAV SINGH', NULL, NULL),
(153, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B006', 'KARTHIK PURANAM', NULL, NULL),
(154, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B013', 'SAI SUDDHIR A B', NULL, NULL),
(155, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B026', 'AVUTHU NEERAJA REDDY', NULL, NULL),
(156, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B030', 'DHYAN G', NULL, NULL),
(157, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B059', 'SREERAM MADHAVAN V', NULL, NULL),
(158, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B067', 'KAVIN ARVINTH RAVICHANDRAN', NULL, NULL),
(159, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B070', 'CHIRAG V GAONKAR', NULL, NULL),
(160, 'Interdisciplinary Dual Degree Program (IDDD)', 'AE22B105', 'JAHAAN RITESH SHAH', NULL, NULL),
(161, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B008', 'LOKESH PARIHAR', NULL, NULL),
(162, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B009', 'PRITHISH KUMAR M', NULL, NULL),
(163, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B017', 'ADITYA RAJ', NULL, NULL),
(164, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B018', 'AKSHAR V A', NULL, NULL),
(165, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B020', 'ARJUN', NULL, NULL),
(166, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B021', 'ATHARV SANTOSH SHETE', NULL, NULL),
(167, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B028', 'MANOJ S S', NULL, NULL),
(168, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B031', 'MUSKAN CHANDANI', NULL, NULL),
(169, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B033', 'SAAHIL GUPTA', NULL, NULL),
(170, 'Interdisciplinary Dual Degree Program (IDDD)', 'BE22B039', 'SURIYA PRIYAN D', NULL, NULL),
(171, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS22B006', 'AATMAN VASHI', NULL, NULL),
(172, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS22B013', 'CHANDRASEKARAN J', NULL, NULL),
(173, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS22B021', 'KANDARPA SAI SANJEEV HITESH', NULL, NULL),
(174, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS22B022', 'KARTHIK R', NULL, NULL),
(175, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS22B033', 'SANA HASSAN', NULL, NULL),
(176, 'Interdisciplinary Dual Degree Program (IDDD)', 'BS22B040', 'VASUDHA KANNAN', NULL, NULL),
(177, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE22B009', 'KUNDURU HARISH', NULL, NULL),
(178, 'Interdisciplinary Dual Degree Program (IDDD)', 'CE22B085', 'PATEL KRIMA RAVINDRABHAI', NULL, NULL),
(179, 'Interdisciplinary Dual Degree Program (IDDD)', 'CH22B002', 'GANESH B', NULL, NULL),
(180, 'Interdisciplinary Dual Degree Program (IDDD)', 'CH22B008', 'AAYUSH BHAKNA', NULL, NULL),
(181, 'Interdisciplinary Dual Degree Program (IDDD)', 'CH22B049', 'ADITHYAN D S', NULL, NULL),
(182, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B001', 'PRADYUN GOEL', NULL, NULL),
(183, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B005', 'ALLADU YASHOVARDHAN REDDY', NULL, NULL),
(184, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B007', 'ANIRVIN SRIVATSAN', NULL, NULL),
(185, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B008', 'SARDA GOPAL MAHESH', NULL, NULL),
(186, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B011', 'UDAYAN GUPTA', NULL, NULL),
(187, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B016', 'SHAH PRAKSHI MIHIR', NULL, NULL),
(188, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B017', 'JANANI S K', NULL, NULL),
(189, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B021', 'HEMANT PRAVIN RAJHANS', NULL, NULL),
(190, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B023', 'RAFAD ABDUL RASHEED', NULL, NULL),
(191, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B025', 'NIKHILESH S', NULL, NULL),
(192, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B026', 'AVANTHIK V P', NULL, NULL),
(193, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B028', 'SABARINATHAN S S', NULL, NULL),
(194, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B029', 'SRI SAKTHI PRATHOSH R', NULL, NULL),
(195, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B032', 'MADHUPRANAVI S', NULL, NULL),
(196, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B034', 'SAI SONAM SINGH', NULL, NULL),
(197, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B044', 'ABHINAND SHAJI', NULL, NULL),
(198, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B046', 'DHIRAN VENULA', NULL, NULL),
(199, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B047', 'DIVYA GUPTA', NULL, NULL),
(200, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B048', 'ERANDE SIDDHANT SURESH', NULL, NULL),
(201, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B066', 'SATHYAPRIYA D', NULL, NULL),
(202, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B069', 'SWAMINATH PRANAV V', NULL, NULL),
(203, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B070', 'UDDHAV HEMANTH RAO', NULL, NULL),
(204, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B071', 'VAIBHAV MANEGAR', NULL, NULL),
(205, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B072', 'VIGNESH S', NULL, NULL),
(206, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B073', 'VINNAY GUPTA', NULL, NULL),
(207, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B075', 'VISHVESH SITARAM KATTI', NULL, NULL),
(208, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B081', 'ADITYA NARAYAN PANDA', NULL, NULL),
(209, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B083', 'DEBANKIT BHATTACHARYA', NULL, NULL),
(210, 'Interdisciplinary Dual Degree Program (IDDD)', 'ED22B084', 'GURUJEE CHINMAY BIPIN', NULL, NULL),
(211, 'Interdisciplinary Dual Degree Program (IDDD)', 'EE22B012', 'ARYAN S NAMBOODIRI', NULL, NULL),
(212, 'Interdisciplinary Dual Degree Program (IDDD)', 'EP22B024', 'C SHANKARANARAYANAN', NULL, NULL),
(213, 'Interdisciplinary Dual Degree Program (IDDD)', 'EP22B028', 'JAI VISHWAS KARANDIKAR', NULL, NULL),
(214, 'Interdisciplinary Dual Degree Program (IDDD)', 'EP22B030', 'JOEL GEORGE KALLARACKAL', NULL, NULL),
(215, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B046', 'JAI SAKHTHIVELAN K', NULL, NULL),
(216, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B048', 'VAIBHAV K E', NULL, NULL),
(217, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B058', 'SHANKAR K', NULL, NULL),
(218, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B070', 'ALDIS DANIEL G', NULL, NULL),
(219, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B092', 'ABHINAV KUMAR', NULL, NULL),
(220, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B106', 'ANUJ SREENIVASAN', NULL, NULL),
(221, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B122', 'DUDDU PRAGNA SAI TEJA', NULL, NULL),
(222, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B152', 'MANDALA HARSHITHA MANI', NULL, NULL),
(223, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B162', 'NALGE VEDANT DILIP', NULL, NULL),
(224, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B163', 'NANDHINI S', NULL, NULL),
(225, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B191', 'SHAH SOHAM HARINBHAI', NULL, NULL),
(226, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B196', 'SHUBHAM TIWARI', NULL, NULL),
(227, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B203', 'SUDARSUN.D.S', NULL, NULL),
(228, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B215', 'YOGESH G R', NULL, NULL),
(229, 'Interdisciplinary Dual Degree Program (IDDD)', 'ME22B223', 'SHYAM KUMAR P', NULL, NULL),
(230, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM22B002', 'BARATH.S.D', NULL, NULL),
(231, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM22B011', 'DEEPAK S', NULL, NULL),
(232, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM22B012', 'YASWANTH KUMAR A', NULL, NULL),
(233, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM22B054', 'RISHI SUSHRUT PRABHUDESAI', NULL, NULL),
(234, 'Interdisciplinary Dual Degree Program (IDDD)', 'MM22B058', 'SREERAG S SREEJITH', NULL, NULL),
(235, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B002', 'PAURUSH KUMAR', NULL, NULL),
(236, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B010', 'YASWAND R', NULL, NULL),
(237, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B020', 'ADITI SOUMENDRA', NULL, NULL),
(238, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B025', 'AKSHAT PORWAL', NULL, NULL),
(239, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B026', 'AKSHITHAA ARUNRAJ', NULL, NULL),
(240, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B035', 'DILLEN JOE P', NULL, NULL),
(241, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B036', 'FATEMA CROCKERY WALA', NULL, NULL),
(242, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B037', 'GAURI MISHRA', NULL, NULL),
(243, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B042', 'KABIR NAWAL BAJAJ', NULL, NULL),
(244, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B047', 'KURUMALLA SATYA SANTHOSH', NULL, NULL),
(245, 'Interdisciplinary Dual Degree Program (IDDD)', 'NA22B077', 'THADIKONDA NAGA SAI BALAJI', NULL, NULL),
(246, 'Interdisciplinary Dual Degree Program (IDDD)', 'PH22B002', 'SRIJAN S BHAT', NULL, NULL),
(247, 'Interdisciplinary Dual Degree Program (IDDD)', 'PH22B016', 'MOHD HAMZA', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `news_events`
--

CREATE TABLE `news_events` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_events`
--

INSERT INTO `news_events` (`id`, `title`, `link`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'IIT Madras - School of Interdisciplinary Studies (SIDiS) - 63rd Convocation 2026', 'https://www.youtube.com/live/LzOaQqhYrgg?si=4_oqrkEn5F4VCZ9h', 'news-live.png', 1, '2026-08-20 09:33:23', '2026-08-20 09:34:50'),
(2, 'Joint Masters Program with University of Swansea', 'https://ge.iitm.ac.in/swansea', 'news1.png', 1, '2026-08-20 09:33:23', '2026-08-20 09:33:23'),
(3, 'Joint Masters Program with University of Birmingham', 'https://ge.iitm.ac.in/uob/sustainable-energy-systems', 'news2.png', 1, '2026-08-20 09:33:23', '2026-08-20 09:33:23'),
(4, 'Bachelor of Cybersecurity', 'bcyber_iitm.html', 'news3.png', 1, '2026-08-20 09:33:23', '2026-08-20 09:33:23'),
(5, 'SIDiS D3P 2026', 'SIDiS-D3P_INVITATION.pdf', 'news4.png', 1, '2026-08-20 09:33:23', '2026-09-01 08:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `phd_scholars`
--

CREATE TABLE `phd_scholars` (
  `id` int NOT NULL,
  `batch` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `roll_number` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `student_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mail_id` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mentor_1` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mentor_2` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phd_scholars`
--

INSERT INTO `phd_scholars` (`id`, `batch`, `roll_number`, `student_name`, `mail_id`, `mentor_1`, `mentor_2`) VALUES
(1, 'Jan 2025', 'ID24D003', 'Amrita P', 'id24d003@smail.iitm.ac.in', NULL, NULL),
(2, 'Jan 2025', 'ID24D007', 'Shvetha Sivaprasad', 'id24d007@smail.iitm.ac.in', NULL, NULL),
(3, 'Jan 2025', 'ID24D012', 'Mani S', 'id24d012@smail.iitm.ac.in', NULL, NULL),
(4, 'Jan 2025', 'ID24D014', 'Poul Cherian', 'id24d014@smail.iitm.ac.in', NULL, NULL),
(5, 'Jan 2025', 'ID24D008', 'Goriparthi Sai Sankar', 'id24d008@smail.iitm.ac.in', NULL, NULL),
(6, 'Jan 2025', 'ID24D004', 'Stephen Babu', 'id24d004@smail.iitm.ac.in', NULL, NULL),
(7, 'Jan 2025', 'ID24D001', 'Anju Kumari', 'id24d001@smail.iitm.ac.in', NULL, NULL),
(8, 'Jan 2025', 'ID24D016', 'Ruthramoorthy S', 'id24d016@smail.iitm.ac.in', NULL, NULL),
(9, 'Jan 2025', 'ID24D011', 'Archana Soni', 'id24d011@smail.iitm.ac.in', NULL, NULL),
(10, 'Jan 2025', 'ID23D001', 'Jaikishor Mavai', 'id23d001@smail.iitm.ac.in', NULL, NULL),
(11, 'Jul 2025', 'ID25D002', 'Varad Ashishrao Talnikar', 'id25d002@smail.iitm.ac.in', NULL, NULL),
(12, 'Jul 2025', 'ID25D004', 'Shivani Mehta', 'id25d004@smail.iitm.ac.in', NULL, NULL),
(13, 'Jul 2025', 'ID25D013', 'DIVYA M K', 'id25d013@smail.iitm.ac.in', NULL, NULL),
(14, 'Jul 2025', 'ID25D016', 'Pramit Majumder', 'id25d016@smail.iitm.ac.in', NULL, NULL),
(15, 'Jul 2025', 'ID25D012', 'Anu M', 'id25d012@smail.iitm.ac.in', NULL, NULL),
(16, 'Jan 2026', 'ID25D017', 'Ambuj Pandey', 'id25d017@smail.iitm.ac.in', NULL, NULL),
(17, 'Jan 2026', 'ID25D027', 'K Sathyanarayana Raju', 'id25d027@smail.iitm.ac.in', NULL, NULL),
(18, 'Jan 2026', 'ID25D022', 'Tushmi Dutta', 'id25d022@smail.iitm.ac.in', NULL, NULL),
(19, 'Jan 2026', 'ID25D028', 'Parthiban V', 'id25d028@smail.iitm.ac.in', NULL, NULL),
(20, 'Jan 2026', 'ID25D023', 'Pulivathi Jeeshitha', 'id25d023@smail.iitm.ac.in', NULL, NULL),
(21, 'Jan 2026', 'ID25D018', 'Sobia Shafi', 'id25d018@smail.iitm.ac.in', NULL, NULL),
(22, 'Jan 2026', 'ID25D021', 'R Raajalakshmi', 'id25d021@smail.iitm.ac.in', NULL, NULL),
(23, 'Jan 2026', 'ID25D029', 'Mahesh Taparia', 'id25d029@smail.iit.ac.in', NULL, NULL),
(24, 'Jan 2026', 'ID25D030', 'S. Viswanathan', 'id25d030@smail.iit.ac.in', NULL, NULL),
(25, 'Jul 2026', 'ID26D002', 'GOURI LAKSHMI R NAIR', 'id26d002@smail.iitm.ac.in', NULL, NULL),
(26, 'Jul 2026', 'ID26D003', 'Sukanya Ray', 'id26d003@smail.iitm.ac.in', NULL, NULL),
(27, 'Jul 2026', 'ID26D004', 'SAISIVAKUMARAN', 'id26d004@smail.iitm.ac.in', NULL, NULL),
(28, 'Jul 2026', 'ID26D005', 'Mithunraj S', 'id26d005@smail.iitm.ac.in', NULL, NULL),
(29, 'Jul 2026', 'ID26D007', 'NIVEDITHA K V', 'id26d007@smail.iitm.ac.in', NULL, NULL),
(30, 'Jul 2026', 'ID26D009', 'Arya Mallick', 'id26d009@smail.iitm.ac.in', NULL, NULL),
(31, 'Jul 2026', 'ID26D014', 'Uzma Nawaz', 'id26d014@smail.iitm.ac.in', NULL, NULL),
(32, 'Jul 2026', 'ID26D015', 'MD SHADAN ZAFAR', 'id26d015@smail.iitm.ac.in', NULL, NULL),
(33, 'Jul 2026', 'ID26D018', 'Mythreyi S S', 'id26d018@smail.iitm.ac.in', NULL, NULL),
(34, 'Jul 2026', 'ID26D019', 'Sajan Daheriya', 'id26d019@smail.iitm.ac.in', NULL, NULL),
(35, 'Jul 2026', 'ID26D021', 'Gautham Krishna', 'id26d021@smail.iitm.ac.in', NULL, NULL),
(36, 'Jul 2026', 'ID26D023', 'Indumathi S', 'id26d023@smail.iitm.ac.in', NULL, NULL),
(37, 'Jul 2026', 'ID26D024', 'Tushar Vijay Dani', 'id26d024@smail.iitm.ac.in', NULL, NULL),
(38, 'Jul 2026', 'ID26D026', 'Sandeep Singh Sandhu', 'id26d026@smail.iitm.ac.in', NULL, NULL),
(39, 'Jul 2026', 'ID26D027', 'J p IRENE CYNTHIA', 'id26d027@smail.iitm.ac.in', NULL, NULL),
(40, 'Jul 2026', 'ID26D028', 'RAKESH NAIN', 'id26d028@smail.iitm.ac.in', NULL, NULL),
(41, 'Jul 2026', 'ID26D030', 'Sangeeth Krishna S', 'id26d030@smail.iitm.ac.in', NULL, NULL),
(42, 'Jul 2026', 'ID26D033', 'Prateek Dwivedi', 'id26d033@smail.iitm.ac.in', NULL, NULL),
(43, 'Jul 2026', 'ID26D401', 'Lovlyn Sanjay', 'id26d401@smail.iitm.ac.in', NULL, NULL),
(44, 'Jul 2026', 'ID26D900', 'Mogaraju Jagadish Kumar', 'id26d900@smail.iitm.ac.in', NULL, NULL),
(45, 'Jul 2026', 'ID26D901', 'Pratyush Srivatsava', 'id26d901@smail.iitm.ac.in', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `school_consultative_committee`
--

CREATE TABLE `school_consultative_committee` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `committee_section` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_consultative_committee`
--

INSERT INTO `school_consultative_committee` (`id`, `name`, `designation`, `committee_section`) VALUES
(1, 'Prof. Anbarasu Manivannan', 'Head, SIDiS, Chairperson', 'Main Committee'),
(2, 'Prof. Rajnish Kumar', 'Head, School of Sustainability, Associate Chairperson', 'Main Committee'),
(3, 'All IDDD Coordinators and I2MP Coordinators', NULL, 'Members'),
(4, 'Prof. Santosh Kumar Sahu', 'MS, PhD Coordinator, SIDiS', 'Members'),
(5, 'Prof. Satyanarayanan Seshadri', 'Associate Head, School of Sustainability', 'Members'),
(6, 'Prof. Rupashree Baral', 'Member, School of Sustainability', 'Members'),
(7, 'Prof. Abhishek Misra', 'Advanced Materials and Nanotechnology', 'IDDD Coordinators'),
(8, 'Prof. Prasad Patnaik', 'B S V, Computational Engineering', 'IDDD Coordinators'),
(9, 'Prof. Krishna Vasudevan', 'Energy Systems', 'IDDD Coordinators'),
(10, 'Prof. Asokan T', 'Robotics', 'IDDD Coordinators'),
(11, 'Prof. Prabha Mandayam', 'Quantum Science and Technology', 'IDDD Coordinators'),
(12, 'Prof. Sayan Gupta', 'Complex Systems and Dynamics', 'IDDD Coordinators'),
(13, 'Prof. Sridharakumar Narasimhan', 'Cyber-Physical Systems', 'IDDD Coordinators'),
(14, 'Prof. Srikanthan Sridharan', 'Electric Vehicles', 'IDDD Coordinators'),
(15, 'Prof. P. Krishna Prasanna', 'Quantitative Finance', 'IDDD Coordinators'),
(16, 'Prof. Usha Mohan', 'Tech MBA', 'IDDD Coordinators'),
(17, 'Prof. Muraleedharan VR', 'Public Policy', 'IDDD Coordinators'),
(18, 'Prof. Sachin S. Gunthe', 'Atmospheric and Climate Sciences', 'IDDD Coordinators'),
(19, 'Prof. Sridhar Kumar Narasimhan', 'Cyber-Physical Systems', 'I2MP Coordinators'),
(20, 'Prof. Praveen Vidya Bhallamudi', 'Advanced Materials and Nanotechnology', 'I2MP Coordinators'),
(21, 'Prof. Prasad Patnaik B S V', 'Computational Engineering', 'I2MP Coordinators'),
(22, 'Prof. Asokan T', 'Robotics', 'I2MP Coordinators'),
(23, 'Prof. Sayan Gupta', 'Complex Systems and Dynamics', 'I2MP Coordinators'),
(24, 'Prof. Krishna Vasudevan', 'Energy Systems', 'I2MP Coordinators');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'dummy.png',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `designation`, `image`, `status`, `display_order`) VALUES
(1, 'Mrs. V. Chamundeeswari', 'Senior Assistant', 'chamu.png', 1, 1),
(2, 'Ms. G. Umamageswari', 'Secretarial Assistant', 'uma.jpeg', 1, 2),
(3, 'Mr. E.Mohan', 'Attendant', 'mohan.jpeg', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin-si-di-s', '$2y$12$nQS9NNYUFhLKw8.f5PZXiu.siDYh.R1fORVh/mAMP5hZaqpUKvdmO', 1, '2026-08-20 09:53:12', '2026-08-20 10:42:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `advisory_committee`
--
ALTER TABLE `advisory_committee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clusters`
--
ALTER TABLE `clusters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cluster_name` (`cluster_name`),
  ADD KEY `idx_cluster_status` (`status`),
  ADD KEY `idx_cluster_order` (`display_order`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_faculty_cluster` (`cluster_id`),
  ADD KEY `idx_faculty_status` (`status`),
  ADD KEY `idx_faculty_order` (`display_order`),
  ADD KEY `idx_faculty_designation` (`designation`);

--
-- Indexes for table `featured_research`
--
ALTER TABLE `featured_research`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guidelines`
--
ALTER TABLE `guidelines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `head_message`
--
ALTER TABLE `head_message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_section`
--
ALTER TABLE `hero_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message_from_director`
--
ALTER TABLE `message_from_director`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ms`
--
ALTER TABLE `ms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_roll_number` (`roll_number`),
  ADD KEY `batch_index` (`batch`);

--
-- Indexes for table `mtech_students`
--
ALTER TABLE `mtech_students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_events`
--
ALTER TABLE `news_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phd_scholars`
--
ALTER TABLE `phd_scholars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_consultative_committee`
--
ALTER TABLE `school_consultative_committee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advisory_committee`
--
ALTER TABLE `advisory_committee`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `featured_research`
--
ALTER TABLE `featured_research`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `guidelines`
--
ALTER TABLE `guidelines`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `head_message`
--
ALTER TABLE `head_message`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hero_section`
--
ALTER TABLE `hero_section`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `message_from_director`
--
ALTER TABLE `message_from_director`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ms`
--
ALTER TABLE `ms`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `mtech_students`
--
ALTER TABLE `mtech_students`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=249;

--
-- AUTO_INCREMENT for table `news_events`
--
ALTER TABLE `news_events`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `phd_scholars`
--
ALTER TABLE `phd_scholars`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `school_consultative_committee`
--
ALTER TABLE `school_consultative_committee`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Table structure for table `important_dates`
--

CREATE TABLE `important_dates` (
  `id` int UNSIGNED NOT NULL,
  `schedule_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_schedule` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `important_dates`
--

INSERT INTO `important_dates` (`id`, `schedule_name`, `description`, `date_schedule`) VALUES
(1, 'Admission Schedule 2026-2027', 'Portal open for online application', '10 April 2026'),
(2, 'Admission Schedule 2026-2027', 'Last date of receipt of online application', '30th October 2026 @ 5:00 PM'),
(3, 'Admission Schedule 2026-2027', 'Written test and interview', 'To be announced by the concerned department');

--
-- Indexes for table `important_dates`
--
ALTER TABLE `important_dates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `important_dates`
--
ALTER TABLE `important_dates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Table structure for table `how_to_apply`
--

CREATE TABLE `how_to_apply` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_type` enum('link','attachment') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'link',
  `link_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `how_to_apply`
--

INSERT INTO `how_to_apply` (`id`, `title`, `image`, `link_type`, `link_url`, `attachment`, `display_order`, `status`) VALUES
(1, 'Research', 'Frame 150.png', 'link', 'clusters.php', NULL, 1, 1),
(2, 'Guidelines', 'Frame 151.png', 'attachment', NULL, 'SIDiS-D3P_INVITATION.pdf', 2, 1);

--
-- Indexes for table `how_to_apply`
--
ALTER TABLE `how_to_apply`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `how_to_apply`
--
ALTER TABLE `how_to_apply`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
