/*
SQLyog Enterprise - MySQL GUI v7.02 
MySQL - 5.5.5-10.4.22-MariaDB : Database - db_neda_ppmp
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_neda_ppmp` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `db_neda_ppmp`;

/*Table structure for table `ppmp_bac_member` */

DROP TABLE IF EXISTS `ppmp_bac_member`;

CREATE TABLE `ppmp_bac_member` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(100) DEFAULT NULL,
  `office_id` varchar(10) DEFAULT NULL,
  `bac_group` enum('End User','Technical Expert') DEFAULT NULL,
  `expertise` varchar(100) DEFAULT NULL,
  `sub_expertise` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

/*Data for the table `ppmp_bac_member` */

insert  into `ppmp_bac_member`(`id`,`emp_id`,`office_id`,`bac_group`,`expertise`,`sub_expertise`) values (1,'001019',NULL,'Technical Expert','Goods','ICT'),(2,'122213',NULL,'Technical Expert','Goods','Others (catering)'),(3,'312453',NULL,'Technical Expert','Goods','Supplies and Materials'),(4,'714588',NULL,'Technical Expert','Goods','Automotive'),(5,'714588',NULL,'Technical Expert','Infrastructure',''),(6,'001019',NULL,'Technical Expert','Consultancy',''),(7,'010205','ORD','End User',NULL,NULL),(8,'122213','FAD','End User',NULL,NULL);

/*Table structure for table `ppmp_bid` */

DROP TABLE IF EXISTS `ppmp_bid`;

CREATE TABLE `ppmp_bid` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `pr_id` int(255) DEFAULT NULL,
  `rfq_id` int(255) DEFAULT NULL,
  `bid_no` varchar(100) DEFAULT NULL,
  `date_opened` date DEFAULT NULL,
  `time_opened` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

/*Data for the table `ppmp_bid` */

insert  into `ppmp_bid`(`id`,`pr_id`,`rfq_id`,`bid_no`,`date_opened`,`time_opened`) values (4,3,19,'22-02-002-00','2022-03-01','01:00 PM'),(5,2,15,'22-0128-001-00','2022-06-29','01:00 AM');

/*Table structure for table `ppmp_bid_member` */

DROP TABLE IF EXISTS `ppmp_bid_member`;

CREATE TABLE `ppmp_bid_member` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `bid_id` int(255) DEFAULT NULL,
  `emp_id` varchar(10) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ppmp_bid_member` (`bid_id`),
  CONSTRAINT `FK_ppmp_bid_member` FOREIGN KEY (`bid_id`) REFERENCES `ppmp_bid` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4;

/*Data for the table `ppmp_bid_member` */

insert  into `ppmp_bid_member`(`id`,`bid_id`,`emp_id`,`position`) values (16,4,'122213','BAC Chairperson'),(17,4,'082870','BAC Vice-Chairperson'),(18,4,'001019','BAC Member'),(19,4,'312453','Provisional Member'),(20,4,'122213','Provisional Member - End User'),(21,5,'122213','BAC Chairperson'),(22,5,'082870','BAC Vice-Chairperson'),(23,5,'001019','BAC Member'),(24,5,'122213','Provisional Member'),(25,5,'122213','Provisional Member - End User');

/*Table structure for table `ppmp_bid_winner` */

DROP TABLE IF EXISTS `ppmp_bid_winner`;

CREATE TABLE `ppmp_bid_winner` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `bid_id` int(255) DEFAULT NULL,
  `supplier_id` int(255) DEFAULT NULL,
  `pr_item_id` int(255) DEFAULT NULL,
  `justification` text DEFAULT NULL,
  `status` enum('Awarded','Failed') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ppmp_bid_winner` (`bid_id`),
  CONSTRAINT `FK_ppmp_bid_winner` FOREIGN KEY (`bid_id`) REFERENCES `ppmp_bid` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4;

/*Data for the table `ppmp_bid_winner` */

insert  into `ppmp_bid_winner`(`id`,`bid_id`,`supplier_id`,`pr_item_id`,`justification`,`status`) values (7,4,3,1424,'Test','Awarded'),(8,4,3,1400,'','Awarded'),(9,4,NULL,1403,'','Failed'),(10,4,3,1407,'','Awarded'),(11,4,2,1408,'','Awarded'),(12,4,3,1410,'','Awarded'),(13,4,2,1425,'','Awarded'),(14,5,2,1359,'','Awarded'),(15,5,2,1362,'','Awarded'),(16,5,2,1363,'','Awarded'),(17,5,2,1367,'','Awarded'),(18,5,2,1371,'','Awarded'),(19,5,2,1242,'','Awarded'),(20,5,NULL,1246,'','Failed'),(21,5,NULL,1249,'','Failed'),(22,5,NULL,1252,'','Failed'),(23,5,NULL,1256,'','Failed'),(24,5,NULL,1260,'','Failed'),(25,5,NULL,1262,'','Failed'),(26,5,NULL,1263,'','Failed'),(27,5,NULL,1265,'','Failed'),(28,5,NULL,1269,'','Failed'),(29,5,NULL,1273,'','Failed'),(30,5,NULL,1277,'','Failed'),(31,5,NULL,1281,'','Failed'),(32,5,NULL,1283,'','Failed'),(33,5,NULL,1284,'','Failed'),(34,5,NULL,1287,'','Failed'),(35,5,NULL,1290,'','Failed'),(36,5,NULL,1291,'','Failed'),(37,5,NULL,1292,'','Failed'),(38,5,NULL,1293,'','Failed'),(39,5,NULL,1294,'','Failed'),(40,5,NULL,1296,'','Failed');

/*Table structure for table `ppmp_payment_term` */

DROP TABLE IF EXISTS `ppmp_payment_term`;

CREATE TABLE `ppmp_payment_term` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

/*Data for the table `ppmp_payment_term` */

insert  into `ppmp_payment_term`(`id`,`title`,`description`) values (1,'Payment in advance',NULL),(2,'7 days after invoice date',NULL),(3,'10 days after invoice date',NULL),(4,'30 days after invoice date',NULL),(5,'60 days after invoice date',NULL),(6,'90 days after invoice date',NULL),(7,'End of month',NULL),(8,'21st of the month following invoice date',NULL),(9,'Cash on delivery',NULL),(10,'Account conducted on a cash basis, no credit',NULL),(11,'A documentary credit confirmed by a bank, often used for export ',NULL),(12,'A promise to pay at a later date, usually supported by a bank',NULL),(13,'Cash next delivery',NULL),(14,'Cash before shipment',NULL),(15,'Cash in advance',NULL),(16,'Cash with order',NULL),(17,'Monthly credit payment of a full month\'s supply',NULL),(18,'As above plus an extra calendar month',NULL),(19,'Payment from the customer offset against the value of supplies purchased ',NULL),(20,'Debit Card',NULL),(21,'Credit Card',NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
