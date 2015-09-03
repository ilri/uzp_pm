-- phpMyAdmin SQL Dump
-- version 4.4.13.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 03, 2015 at 07:29 AM
-- Server version: 5.6.24
-- PHP Version: 5.6.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `azizi_uzp_99h_pm`
--

-- --------------------------------------------------------

--
-- Table structure for table `postmortem`
--

CREATE TABLE IF NOT EXISTS `postmortem` (
  `id` int(11) NOT NULL,
  `vet` varchar(50) NOT NULL,
  `assistant` varchar(50) NOT NULL,
  `animal_id` varchar(50) NOT NULL,
  `animal_class` varchar(50) NOT NULL,
  `weight` int(11) DEFAULT NULL,
  `edta` varchar(50) DEFAULT NULL,
  `serum` varchar(50) DEFAULT NULL,
  `bsmear_1` varchar(50) DEFAULT NULL,
  `bsmear_2` varchar(50) DEFAULT NULL,
  `osmear_1` varchar(50) DEFAULT NULL,
  `osmear_2` varchar(50) DEFAULT NULL,
  `wing` varchar(50) DEFAULT NULL,
  `eparasite` varchar(50) DEFAULT NULL,
  `species` varchar(200) DEFAULT NULL,
  `taxonomy` varchar(200) DEFAULT NULL,
  `id_certainty` varchar(50) DEFAULT NULL,
  `age` varchar(50) DEFAULT NULL,
  `sex` varchar(50) DEFAULT NULL,
  `pregnant` varchar(50) DEFAULT NULL,
  `lactating` varchar(50) DEFAULT NULL,
  `cond_samp` varchar(50) DEFAULT NULL,
  `clcl_sgns` varchar(50) DEFAULT NULL,
  `is_dis_suspected` varchar(50) DEFAULT NULL,
  `suspect_dis` varchar(50) DEFAULT NULL,
  `bcs` varchar(50) DEFAULT NULL,
  `body_length` decimal(10,0) DEFAULT NULL,
  `ear_length` decimal(10,0) DEFAULT NULL,
  `tragus_length` decimal(10,0) DEFAULT NULL,
  `forearm_length` decimal(10,0) DEFAULT NULL,
  `tibia_length` decimal(10,0) DEFAULT NULL,
  `hfoot_length` decimal(10,0) DEFAULT NULL,
  `tail_length` decimal(10,0) DEFAULT NULL,
  `full_body_length` varchar(100) DEFAULT NULL,
  `anterior_facial` varchar(100) DEFAULT NULL,
  `lateral_facial` varchar(100) DEFAULT NULL,
  `pp_dorsum` varchar(100) DEFAULT NULL,
  `pp_vetrum` varchar(100) DEFAULT NULL,
  `integument_les` varchar(255) DEFAULT NULL,
  `integument_bc` varchar(50) DEFAULT NULL,
  `pectoral_les` varchar(255) DEFAULT NULL,
  `pectoral_mc` varchar(50) DEFAULT NULL,
  `ptagium_les` varchar(255) DEFAULT NULL,
  `ptagium_bc` varchar(50) DEFAULT NULL,
  `saliva_les` varchar(255) DEFAULT NULL,
  `saliva_1_bc` varchar(50) DEFAULT NULL,
  `saliva_2_bc` varchar(50) DEFAULT NULL,
  `saliva_3_bc` varchar(50) DEFAULT NULL,
  `cavity_les` varchar(255) DEFAULT NULL,
  `diaphgram_les` varchar(255) DEFAULT NULL,
  `diaphgram_bc` varchar(50) DEFAULT NULL,
  `liver_les` varchar(255) DEFAULT NULL,
  `liver_weight` decimal(10,0) DEFAULT NULL,
  `liver_1_bc` varchar(50) DEFAULT NULL,
  `liver_2_bc` varchar(50) DEFAULT NULL,
  `liver_3_bc` varchar(50) DEFAULT NULL,
  `spleen_les` varchar(255) DEFAULT NULL,
  `spleen_weight` decimal(10,0) DEFAULT NULL,
  `spleen_1_bc` varchar(50) DEFAULT NULL,
  `spleen_2_bc` varchar(50) DEFAULT NULL,
  `spleen_3_bc` varchar(50) DEFAULT NULL,
  `kidney_les` varchar(255) DEFAULT NULL,
  `kidney_weight` decimal(10,0) DEFAULT NULL,
  `kidney_1_bc` varchar(50) DEFAULT NULL,
  `kidney_2_bc` varchar(50) DEFAULT NULL,
  `kidney_3_bc` varchar(50) DEFAULT NULL,
  `adrenal_les` varchar(255) DEFAULT NULL,
  `adrenal_weight` decimal(10,0) DEFAULT NULL,
  `adrenal_bc` varchar(50) DEFAULT NULL,
  `heart_les` varchar(255) DEFAULT NULL,
  `heart_weight` decimal(10,0) DEFAULT NULL,
  `heart_bc` varchar(50) DEFAULT NULL,
  `lung_les` varchar(255) DEFAULT NULL,
  `lung_weight` decimal(10,0) DEFAULT NULL,
  `lung_1_bc` varchar(50) DEFAULT NULL,
  `lung_2_bc` varchar(50) DEFAULT NULL,
  `lung_3_bc` varchar(50) DEFAULT NULL,
  `pluck_les` varchar(255) DEFAULT NULL,
  `pluck_bc` varchar(50) DEFAULT NULL,
  `urine_1_bc` varchar(50) DEFAULT NULL,
  `urine_2_bc` varchar(50) DEFAULT NULL,
  `femur_1_bc` varchar(50) DEFAULT NULL,
  `femur_2_bc` varchar(50) DEFAULT NULL,
  `brain_les` varchar(255) DEFAULT NULL,
  `brain_weight` decimal(10,0) DEFAULT NULL,
  `brain_bc` varchar(50) DEFAULT NULL,
  `faeces_1_bc` varchar(50) DEFAULT NULL,
  `faeces_2_bc` varchar(50) DEFAULT NULL,
  `faeces_3_bc` varchar(50) DEFAULT NULL,
  `urogen_les` varchar(255) DEFAULT NULL,
  `urogen_1_bc` varchar(50) DEFAULT NULL,
  `urogen_2_bc` varchar(50) DEFAULT NULL,
  `stomach_les` varchar(255) DEFAULT NULL,
  `stomach_bc` varchar(50) DEFAULT NULL,
  `ileum_les` varchar(255) DEFAULT NULL,
  `ileum_1_bc` varchar(50) DEFAULT NULL,
  `ileum_2_bc` varchar(50) DEFAULT NULL,
  `carcas_bc` varchar(50) DEFAULT NULL,
  `general_comment` varchar(100) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `_complete` smallint(1) NOT NULL DEFAULT '0',
  `smallint_les` varchar(255) DEFAULT NULL,
  `smallint_bc` varchar(50) DEFAULT NULL,
  `largeint_les` varchar(255) DEFAULT NULL,
  `largeint_bc` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) COLLATE latin1_bin DEFAULT NULL,
  `data` text COLLATE latin1_bin,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

-- --------------------------------------------------------

--
-- Table structure for table `storage_box`
--

CREATE TABLE IF NOT EXISTS `storage_box` (
  `id` int(11) NOT NULL,
  `box_label` varchar(50) NOT NULL,
  `added_by` varchar(50) NOT NULL,
  `time_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stored_sample`
--

CREATE TABLE IF NOT EXISTS `stored_sample` (
  `id` int(11) NOT NULL,
  `storage_box_id` int(11) NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `position` int(11) NOT NULL,
  `added_by` varchar(50) NOT NULL,
  `time_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `postmortem`
--
ALTER TABLE `postmortem`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`);

--
-- Indexes for table `storage_box`
--
ALTER TABLE `storage_box`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `box_label` (`box_label`);

--
-- Indexes for table `stored_sample`
--
ALTER TABLE `stored_sample`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD UNIQUE KEY `storage_box_id_2` (`storage_box_id`,`position`),
  ADD KEY `storage_box_id` (`storage_box_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `postmortem`
--
ALTER TABLE `postmortem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `storage_box`
--
ALTER TABLE `storage_box`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `stored_sample`
--
ALTER TABLE `stored_sample`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `stored_sample`
--
ALTER TABLE `stored_sample`
  ADD CONSTRAINT `fk_stored_sample_storage_box` FOREIGN KEY (`storage_box_id`) REFERENCES `storage_box` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
