# --------------------------------------------------------
# Host:                         127.0.0.1
# Server version:               5.1.36-community-log - MySQL Community Server (GPL)
# Server OS:                    Win32
# HeidiSQL version:             6.0.0.3956
# Date/time:                    2011-12-07 22:55:33
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

# Dumping database structure for mural
DROP DATABASE IF EXISTS `mural`;
CREATE DATABASE IF NOT EXISTS `mural` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `mural`;


# Dumping structure for table mural.recados
DROP TABLE IF EXISTS `recados`;
CREATE TABLE IF NOT EXISTS `recados` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `mensagem` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# Dumping data for table mural.recados: 0 rows
DELETE FROM `recados`;
/*!40000 ALTER TABLE `recados` DISABLE KEYS */;
/*!40000 ALTER TABLE `recados` ENABLE KEYS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;