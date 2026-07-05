-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.0.41-community-nt


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema therp
--

CREATE DATABASE IF NOT EXISTS therp;
USE therp;

--
-- Definition of table `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `accountid` int(10) unsigned NOT NULL,
  `name` varchar(45) NOT NULL,
  `starting_balance` double default NULL,
  PRIMARY KEY  (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `account`
--

/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT INTO `account` (`accountid`,`name`,`starting_balance`) VALUES 
 (1,'Default Sales/Discounts',NULL),
 (1010,'Petty cach',NULL),
 (1030,'Cheque Account',NULL),
 (1040,'Savings account',NULL),
 (1050,'Payroll accounts',NULL),
 (1100,'Accounts Receivable',NULL),
 (1420,'Raw material inventory',NULL),
 (1440,'Work in progress inventory',NULL),
 (1460,'Finished Goods Inventory',NULL),
 (2100,'Accounts Payable',NULL),
 (2150,'Goods Received Suspense',NULL),
 (2300,'GST Payable',NULL),
 (2310,'GST Recoverable',NULL),
 (2340,'Payroll tax payable',NULL),
 (4100,'Product sales',NULL),
 (5000,'Cost of Sales',NULL),
 (5500,'Direct Labour Costs',NULL),
 (5700,'Inventory adjustment',NULL),
 (6200,'Communication',NULL),
 (6300,'Travelling expenses',NULL),
 (7020,'Support Salaries & Wages',NULL),
 (7030,'Support Salary & Wage Deductions',NULL),
 (7040,'Management Salaries',NULL),
 (7050,'Management Salary deductions',NULL),
 (7080,'Payroll tax',NULL),
 (7090,'Benefits',NULL),
 (7400,'Office rent',NULL),
 (7500,'Office supplies',NULL),
 (7600,'Automotive expenses',NULL),
 (7610,'Communication expenses',NULL),
 (7650,'Travel Expenses',NULL);
/*!40000 ALTER TABLE `account` ENABLE KEYS */;


--
-- Definition of table `account_group`
--

DROP TABLE IF EXISTS `account_group`;
CREATE TABLE `account_group` (
  `groupid` int(10) unsigned NOT NULL,
  `accountid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`groupid`,`accountid`),
  KEY `accountid` (`accountid`),
  CONSTRAINT `account_group_ibfk_2` FOREIGN KEY (`groupid`) REFERENCES `accountgroup` (`groupid`),
  CONSTRAINT `account_group_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `account` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `account_group`
--

/*!40000 ALTER TABLE `account_group` DISABLE KEYS */;
INSERT INTO `account_group` (`groupid`,`accountid`) VALUES 
 (4,1),
 (1,1010),
 (1,1030),
 (1,1040),
 (1,1050),
 (1,1100),
 (1,1420),
 (5,1420),
 (1,1460),
 (5,1460),
 (2,2100),
 (2,2150),
 (2,2300),
 (1,2310),
 (4,4100),
 (3,5000),
 (3,5500),
 (3,5700),
 (3,6200),
 (5,6200),
 (3,6300),
 (5,6300),
 (3,7020),
 (3,7030),
 (3,7040),
 (3,7050),
 (3,7400),
 (5,7400),
 (3,7500),
 (5,7500),
 (3,7600),
 (5,7600),
 (3,7610),
 (5,7610);
/*!40000 ALTER TABLE `account_group` ENABLE KEYS */;


--
-- Definition of table `accountconf`
--

DROP TABLE IF EXISTS `accountconf`;
CREATE TABLE `accountconf` (
  `account_receivable` int(10) unsigned NOT NULL,
  `account_payable` int(10) unsigned NOT NULL,
  `vat_payable` int(10) unsigned NOT NULL,
  `vat_recoverable` int(10) unsigned NOT NULL,
  `finished_goods` int(10) unsigned NOT NULL,
  `cost_of_sales` int(10) unsigned NOT NULL,
  `goods_received_suspense` int(10) unsigned NOT NULL,
  `inventory_adjustment` int(10) unsigned default NULL,
  `default_cash` int(10) unsigned default NULL,
  `default_sales` int(10) unsigned default NULL,
  `raw_material` int(10) unsigned default NULL,
  KEY `account_receivable` (`account_receivable`),
  KEY `account_payable` (`account_payable`),
  KEY `vat_payable` (`vat_payable`),
  KEY `vat_recoverable` (`vat_recoverable`),
  KEY `finished_goods` (`finished_goods`),
  KEY `cost_of_sales` (`cost_of_sales`),
  KEY `goods_received_suspense` (`goods_received_suspense`),
  KEY `inventory_adjustment` (`inventory_adjustment`),
  KEY `default_cash` (`default_cash`),
  KEY `default_sales` (`default_sales`),
  KEY `raw_material` (`raw_material`),
  CONSTRAINT `accountconf_ibfk_11` FOREIGN KEY (`raw_material`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_1` FOREIGN KEY (`account_receivable`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_10` FOREIGN KEY (`default_sales`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_2` FOREIGN KEY (`account_payable`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_3` FOREIGN KEY (`vat_payable`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_4` FOREIGN KEY (`vat_recoverable`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_5` FOREIGN KEY (`finished_goods`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_6` FOREIGN KEY (`cost_of_sales`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_7` FOREIGN KEY (`goods_received_suspense`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_8` FOREIGN KEY (`inventory_adjustment`) REFERENCES `account` (`accountid`),
  CONSTRAINT `accountconf_ibfk_9` FOREIGN KEY (`default_cash`) REFERENCES `account` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accountconf`
--

/*!40000 ALTER TABLE `accountconf` DISABLE KEYS */;
INSERT INTO `accountconf` (`account_receivable`,`account_payable`,`vat_payable`,`vat_recoverable`,`finished_goods`,`cost_of_sales`,`goods_received_suspense`,`inventory_adjustment`,`default_cash`,`default_sales`,`raw_material`) VALUES 
 (1100,2100,2300,2310,1460,5000,2150,5700,1010,1,1420);
/*!40000 ALTER TABLE `accountconf` ENABLE KEYS */;


--
-- Definition of table `accountgroup`
--

DROP TABLE IF EXISTS `accountgroup`;
CREATE TABLE `accountgroup` (
  `groupid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `accountgroup`
--

/*!40000 ALTER TABLE `accountgroup` DISABLE KEYS */;
INSERT INTO `accountgroup` (`groupid`,`description`) VALUES 
 (1,'Assets'),
 (2,'Liabilities'),
 (3,'Expenses'),
 (4,'Revenues'),
 (5,'Purchase debit');
/*!40000 ALTER TABLE `accountgroup` ENABLE KEYS */;


--
-- Definition of table `advanced_percent`
--

DROP TABLE IF EXISTS `advanced_percent`;
CREATE TABLE `advanced_percent` (
  `apid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `description` varchar(120) NOT NULL,
  PRIMARY KEY  USING BTREE (`apid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `advanced_percent`
--

/*!40000 ALTER TABLE `advanced_percent` DISABLE KEYS */;
INSERT INTO `advanced_percent` (`apid`,`name`,`description`) VALUES 
 (1,'th_tax_ap','');
/*!40000 ALTER TABLE `advanced_percent` ENABLE KEYS */;


--
-- Definition of table `ap_bracket`
--

DROP TABLE IF EXISTS `ap_bracket`;
CREATE TABLE `ap_bracket` (
  `apid` int(10) unsigned NOT NULL default '0',
  `bracketid` smallint(5) unsigned NOT NULL default '0',
  `ceiling` double NOT NULL,
  `percent` smallint(5) unsigned default NULL,
  PRIMARY KEY  USING BTREE (`apid`,`bracketid`),
  CONSTRAINT `ap_bracket_ibfk_1` FOREIGN KEY (`apid`) REFERENCES `advanced_percent` (`apid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ap_bracket`
--

/*!40000 ALTER TABLE `ap_bracket` DISABLE KEYS */;
INSERT INTO `ap_bracket` (`apid`,`bracketid`,`ceiling`,`percent`) VALUES 
 (1,1,100000,0),
 (1,2,500000,10),
 (1,3,1000000,20),
 (1,4,4000000,30),
 (1,5,999999999,37);
/*!40000 ALTER TABLE `ap_bracket` ENABLE KEYS */;


--
-- Definition of table `attribute`
--

DROP TABLE IF EXISTS `attribute`;
CREATE TABLE `attribute` (
  `attributeid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY  (`attributeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute`
--

/*!40000 ALTER TABLE `attribute` DISABLE KEYS */;
INSERT INTO `attribute` (`attributeid`,`name`) VALUES 
 (1,'salary'),
 (2,'sickdays_per_year'),
 (3,'hourrate'),
 (4,'hours_per_day'),
 (5,'late_arrival_penalty');
/*!40000 ALTER TABLE `attribute` ENABLE KEYS */;


--
-- Definition of table `attribute_description`
--

DROP TABLE IF EXISTS `attribute_description`;
CREATE TABLE `attribute_description` (
  `attributeid` int(10) unsigned NOT NULL default '0',
  `language` varchar(16) NOT NULL default '',
  `description` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`attributeid`,`language`),
  CONSTRAINT `attribute_description_ibfk_1` FOREIGN KEY (`attributeid`) REFERENCES `attribute` (`attributeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute_description`
--

/*!40000 ALTER TABLE `attribute_description` DISABLE KEYS */;
INSERT INTO `attribute_description` (`attributeid`,`language`,`description`) VALUES 
 (1,'en','Salary'),
 (1,'sv','MÃ¥nadslÃ¶n'),
 (1,'th','Salary'),
 (2,'en','Sick leave days per year'),
 (2,'sv','Sjukdagar per Ã¥r'),
 (2,'th','Sjukdagar per Ã¥r'),
 (3,'en','Hour rate'),
 (3,'sv','Hour rate'),
 (3,'th','Hour rate'),
 (4,'en','Hours per day'),
 (4,'sv','Hours per day'),
 (4,'th','Hours per day'),
 (5,'en','Late arrival penalty'),
 (5,'sv','Late arrival penalty'),
 (5,'th','Late arrival penalty');
/*!40000 ALTER TABLE `attribute_description` ENABLE KEYS */;


--
-- Definition of table `attribute_value`
--

DROP TABLE IF EXISTS `attribute_value`;
CREATE TABLE `attribute_value` (
  `attributeid` int(10) unsigned NOT NULL,
  `fromperiodid` int(10) unsigned NOT NULL,
  `value` double default NULL,
  KEY `attributeid` (`attributeid`),
  KEY `fromperiodid` (`fromperiodid`),
  CONSTRAINT `attribute_value_ibfk_2` FOREIGN KEY (`fromperiodid`) REFERENCES `payperiod` (`periodid`),
  CONSTRAINT `attribute_value_ibfk_1` FOREIGN KEY (`attributeid`) REFERENCES `attribute` (`attributeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute_value`
--

/*!40000 ALTER TABLE `attribute_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `attribute_value` ENABLE KEYS */;


--
-- Definition of table `bankaccount`
--

DROP TABLE IF EXISTS `bankaccount`;
CREATE TABLE `bankaccount` (
  `number` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `glaccountid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bankaccount`
--

/*!40000 ALTER TABLE `bankaccount` DISABLE KEYS */;
INSERT INTO `bankaccount` (`number`,`name`,`glaccountid`) VALUES 
 ('12345','Checkkonto',1030),
 ('23456','Sparkonto',1040),
 ('34567','Payroll account',1050);
/*!40000 ALTER TABLE `bankaccount` ENABLE KEYS */;


--
-- Definition of table `bom`
--

DROP TABLE IF EXISTS `bom`;
CREATE TABLE `bom` (
  `parentid` varchar(32) NOT NULL,
  `childid` varchar(32) NOT NULL,
  `quantity` int(11) default '1',
  PRIMARY KEY  (`parentid`,`childid`),
  KEY `fk_bom_child` (`childid`),
  CONSTRAINT `fk_bom_child` FOREIGN KEY (`childid`) REFERENCES `product` (`productid`),
  CONSTRAINT `fk_bom_parent` FOREIGN KEY (`parentid`) REFERENCES `product` (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bom`
--

/*!40000 ALTER TABLE `bom` DISABLE KEYS */;
/*!40000 ALTER TABLE `bom` ENABLE KEYS */;


--
-- Definition of table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `categoryid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  `vatcatid` int(10) unsigned NOT NULL,
  `expense_accountid` int(10) unsigned default NULL,
  `revenue_accountid` int(10) unsigned default NULL,
  `stock` smallint(6) NOT NULL default '1',
  `inventory_accountid` int(10) unsigned default NULL,
  PRIMARY KEY  (`categoryid`),
  KEY `inventory_accountid` (`inventory_accountid`),
  CONSTRAINT `category_ibfk_1` FOREIGN KEY (`inventory_accountid`) REFERENCES `account` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `category`
--

/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` (`categoryid`,`description`,`vatcatid`,`expense_accountid`,`revenue_accountid`,`stock`,`inventory_accountid`) VALUES 
 (1,'Default',1,5000,4100,1,1460);
/*!40000 ALTER TABLE `category` ENABLE KEYS */;


--
-- Definition of table `companyinfo`
--

DROP TABLE IF EXISTS `companyinfo`;
CREATE TABLE `companyinfo` (
  `companyname` varchar(80) default NULL,
  `vatnumber` varchar(32) default NULL,
  `streetaddress` varchar(128) default NULL,
  `city` varchar(32) default NULL,
  `zipcode` varchar(20) default NULL,
  `email` varchar(80) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `companyinfo`
--

/*!40000 ALTER TABLE `companyinfo` DISABLE KEYS */;
INSERT INTO `companyinfo` (`companyname`,`vatnumber`,`streetaddress`,`city`,`zipcode`,`email`) VALUES 
 ('Demo company','123456','Demo street 1','Chicago','12345','demo@therpsoft.com');
/*!40000 ALTER TABLE `companyinfo` ENABLE KEYS */;


--
-- Definition of table `customer`
--

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `customerid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `streetaddress` varchar(120) default NULL,
  `city` varchar(80) default NULL,
  `zipcode` varchar(16) default NULL,
  `email` varchar(80) default NULL,
  `vatnumber` varchar(32) default NULL,
  `credit_account` int(10) unsigned default NULL,
  `credit_length` int(10) unsigned default NULL,
  `use_vat` smallint(6) NOT NULL default '1',
  PRIMARY KEY  (`customerid`),
  KEY `fk_customer_credit_account` (`credit_account`),
  CONSTRAINT `fk_customer_credit_account` FOREIGN KEY (`credit_account`) REFERENCES `account` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `customer`
--

/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` (`customerid`,`name`,`streetaddress`,`city`,`zipcode`,`email`,`vatnumber`,`credit_account`,`credit_length`,`use_vat`) VALUES 
 (1,'Cash customer',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1),
 (2,'Kalle Anka','Ankeborgsv 1','Ankeborg','12345',NULL,NULL,NULL,NULL,1),
 (3,'Fantomen','Ddskallegrottan','Bengalien','23456',NULL,NULL,NULL,NULL,1),
 (4,'Olle','','','',NULL,NULL,NULL,NULL,1),
 (5,'Nisse','','','',NULL,NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;


--
-- Definition of table `daily_form`
--

DROP TABLE IF EXISTS `daily_form`;
CREATE TABLE `daily_form` (
  `formid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) default NULL,
  `teamid` int(10) unsigned default NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`formid`),
  KEY `teamid` (`teamid`),
  KEY `groupid` (`groupid`),
  CONSTRAINT `daily_form_ibfk_2` FOREIGN KEY (`groupid`) REFERENCES `payaccountgroup` (`groupid`),
  CONSTRAINT `daily_form_ibfk_1` FOREIGN KEY (`teamid`) REFERENCES `team` (`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `daily_form`
--

/*!40000 ALTER TABLE `daily_form` DISABLE KEYS */;
INSERT INTO `daily_form` (`formid`,`description`,`teamid`,`groupid`) VALUES 
 (1,'Daily report - Office',1,21),
 (2,'Daily report - Factory',2,22);
/*!40000 ALTER TABLE `daily_form` ENABLE KEYS */;


--
-- Definition of table `emp_attribute`
--

DROP TABLE IF EXISTS `emp_attribute`;
CREATE TABLE `emp_attribute` (
  `employeeid` int(10) unsigned NOT NULL default '0',
  `attributeid` int(10) unsigned NOT NULL default '0',
  `fromperiodid` int(10) unsigned NOT NULL default '0',
  `toperiodid` int(10) unsigned default NULL,
  `value` double default NULL,
  PRIMARY KEY  USING BTREE (`employeeid`,`attributeid`,`fromperiodid`),
  KEY `fk_attribute_emp` (`attributeid`),
  CONSTRAINT `fk_attribute_emp` FOREIGN KEY (`attributeid`) REFERENCES `attribute` (`attributeid`),
  CONSTRAINT `fk_emp_attribute` FOREIGN KEY (`employeeid`) REFERENCES `employee` (`employeeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emp_attribute`
--

/*!40000 ALTER TABLE `emp_attribute` DISABLE KEYS */;
INSERT INTO `emp_attribute` (`employeeid`,`attributeid`,`fromperiodid`,`toperiodid`,`value`) VALUES 
 (1,1,3,5,25000),
 (1,1,5,NULL,26000),
 (2,1,5,NULL,15000),
 (2,2,3,NULL,150),
 (2,3,5,NULL,50),
 (3,1,5,NULL,18000),
 (4,1,5,NULL,22000),
 (6,1,5,NULL,18000),
 (6,3,5,NULL,100),
 (7,3,5,NULL,110),
 (8,1,5,NULL,21000),
 (8,3,5,NULL,85),
 (9,1,5,NULL,15000),
 (9,3,5,NULL,120),
 (10,3,5,NULL,90),
 (11,1,5,NULL,150),
 (11,3,5,NULL,150),
 (12,3,5,NULL,80),
 (13,3,5,NULL,105),
 (14,3,5,NULL,95),
 (15,1,5,NULL,25000),
 (16,1,6,NULL,35000);
/*!40000 ALTER TABLE `emp_attribute` ENABLE KEYS */;


--
-- Definition of table `emp_payitem`
--

DROP TABLE IF EXISTS `emp_payitem`;
CREATE TABLE `emp_payitem` (
  `employeeid` int(10) unsigned NOT NULL,
  `no` int(10) unsigned NOT NULL,
  `fromperiodid` int(10) unsigned NOT NULL,
  `toperiodid` int(10) unsigned default NULL,
  `accountid` int(10) unsigned NOT NULL,
  `value` double default NULL,
  PRIMARY KEY  (`employeeid`,`no`),
  KEY `accountid` (`accountid`),
  CONSTRAINT `emp_payitem_ibfk_2` FOREIGN KEY (`accountid`) REFERENCES `payaccount` (`accountid`),
  CONSTRAINT `emp_payitem_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employee` (`employeeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emp_payitem`
--

/*!40000 ALTER TABLE `emp_payitem` DISABLE KEYS */;
INSERT INTO `emp_payitem` (`employeeid`,`no`,`fromperiodid`,`toperiodid`,`accountid`,`value`) VALUES 
 (1,1,3,NULL,4030,2),
 (7,1,5,NULL,4020,NULL);
/*!40000 ALTER TABLE `emp_payitem` ENABLE KEYS */;


--
-- Definition of table `emp_schedule`
--

DROP TABLE IF EXISTS `emp_schedule`;
CREATE TABLE `emp_schedule` (
  `employeeid` int(10) unsigned NOT NULL default '0',
  `valid_from` date NOT NULL,
  `valid_to` date default NULL,
  `scheduleid` int(11) NOT NULL,
  PRIMARY KEY  (`employeeid`,`valid_from`),
  CONSTRAINT `emp_schedule_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employee` (`employeeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emp_schedule`
--

/*!40000 ALTER TABLE `emp_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `emp_schedule` ENABLE KEYS */;


--
-- Definition of table `emp_team`
--

DROP TABLE IF EXISTS `emp_team`;
CREATE TABLE `emp_team` (
  `employeeid` int(10) unsigned NOT NULL,
  `teamid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`employeeid`,`teamid`),
  KEY `teamid` (`teamid`),
  CONSTRAINT `emp_team_ibfk_2` FOREIGN KEY (`teamid`) REFERENCES `team` (`teamid`),
  CONSTRAINT `emp_team_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employee` (`employeeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emp_team`
--

/*!40000 ALTER TABLE `emp_team` DISABLE KEYS */;
INSERT INTO `emp_team` (`employeeid`,`teamid`) VALUES 
 (1,1),
 (2,1),
 (3,1),
 (4,1),
 (6,2),
 (7,2),
 (8,2),
 (9,2),
 (10,2),
 (11,2),
 (12,2),
 (13,2),
 (14,2);
/*!40000 ALTER TABLE `emp_team` ENABLE KEYS */;


--
-- Definition of table `employee`
--

DROP TABLE IF EXISTS `employee`;
CREATE TABLE `employee` (
  `employeeid` int(10) unsigned NOT NULL auto_increment,
  `givenname` varchar(64) default NULL,
  `surname` varchar(64) default NULL,
  `active` smallint(5) unsigned default NULL,
  `bank_account` varchar(45) default NULL,
  `calctime` datetime default NULL,
  `street_address` varchar(80) NOT NULL,
  `zipcode` varchar(16) NOT NULL,
  `city` varchar(80) NOT NULL,
  `email` varchar(80) default NULL,
  `policyid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`employeeid`),
  KEY `policyid` (`policyid`),
  CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`policyid`) REFERENCES `policy` (`policyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `employee`
--

/*!40000 ALTER TABLE `employee` DISABLE KEYS */;
INSERT INTO `employee` (`employeeid`,`givenname`,`surname`,`active`,`bank_account`,`calctime`,`street_address`,`zipcode`,`city`,`email`,`policyid`) VALUES 
 (1,'Christian','Darren',1,'1234 5678 90124','2007-05-22 10:28:39','311 West Wisconsin Ave.','WI 54208-2289',' Milwaukee',NULL,1),
 (2,'Ronnie','Cruz',1,'553645645645','2007-05-22 18:01:39','41 Marietta Street NW','GA 30304-3388','Atlanta',NULL,1),
 (3,'Paola','Leilo',1,'123456','2007-05-22 20:36:02','2704 Dixie Rd.','FL 33801','Lakeland',NULL,1),
 (4,'Amanda','Lawrence',1,'','2007-05-13 11:43:05','','','',NULL,1),
 (5,'???????','??????',1,'??????','2007-05-13 11:43:06','','','',NULL,1),
 (6,'Judith','Scott',1,'345345345','2007-05-22 10:35:55','2670 Broadway Street','CO 90202-4802','Denver',NULL,2),
 (7,'Lance','Smith',1,'345345345','2007-05-22 18:10:19','2701 Prospect Ave,  P.O. Box 202601','MT 69620-2601','Helena',NULL,2),
 (8,'Michelle','Harrison',1,'23523534523','2007-05-22 10:35:04','524 E 7th St','NE 69230','Cozad',NULL,2),
 (9,'Peter','Weight',1,'','2007-05-22 10:35:39','265 1th Street NW','GA 30418','Atlanta',NULL,2),
 (10,'Sarah','Hunley',1,'643634','2007-05-22 10:35:56','5804 North Lamar Blvd.','Texas 78852','Austin',NULL,2),
 (11,'Brad','Evans',1,'','2007-05-22 10:35:56','31 Medical Drive','UT 84214-4610','Salt Lake City',NULL,2),
 (12,'Ana','Mercedes',1,'','2007-05-22 10:35:57','414-216 W. Chestnut Street','KY 40202','Louisville',NULL,2),
 (13,'Mia','Kelly',1,'','2007-05-22 18:34:58','625 Grand Ave.','WY 82070-3846','Laramie',NULL,2),
 (14,'John','Rhodes',1,'','2007-05-22 10:35:58','514 W. 5th St.','ND 58318','Bottineau',NULL,2),
 (15,'Fredrik','Bertilsson',1,'353453','2007-05-29 19:57:34','StiglÃ¶stgatan 77','586 46','LinkÃ¶ping',NULL,2),
 (16,'Bo','Jonsson',1,'345345345','2007-05-29 21:49:36','','','Karlskoga',NULL,2);
/*!40000 ALTER TABLE `employee` ENABLE KEYS */;


--
-- Definition of table `language`
--

DROP TABLE IF EXISTS `language`;
CREATE TABLE `language` (
  `language` varchar(16) NOT NULL,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `language`
--

/*!40000 ALTER TABLE `language` DISABLE KEYS */;
INSERT INTO `language` (`language`,`description`) VALUES 
 ('en','English'),
 ('sv','Svenska'),
 ('th','Thai');
/*!40000 ALTER TABLE `language` ENABLE KEYS */;


--
-- Definition of table `location`
--

DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `locationid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(45) NOT NULL,
  `streetaddress` varchar(120) NOT NULL,
  `city` varchar(45) NOT NULL,
  `zipcode` varchar(12) NOT NULL,
  `email` varchar(80) default NULL,
  PRIMARY KEY  (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `location`
--

/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` (`locationid`,`name`,`streetaddress`,`city`,`zipcode`,`email`) VALUES 
 (1,'thERP','Stigltsgatan 77','Linkping','584 46',NULL);
/*!40000 ALTER TABLE `location` ENABLE KEYS */;


--
-- Definition of table `logger`
--

DROP TABLE IF EXISTS `logger`;
CREATE TABLE `logger` (
  `loggid` int(10) unsigned NOT NULL auto_increment,
  `loggtext` varchar(2000) default NULL,
  `loggtime` datetime default NULL,
  `username` varchar(16) default NULL,
  PRIMARY KEY  (`loggid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `logger`
--

/*!40000 ALTER TABLE `logger` DISABLE KEYS */;
/*!40000 ALTER TABLE `logger` ENABLE KEYS */;


--
-- Definition of table `payable`
--

DROP TABLE IF EXISTS `payable`;
CREATE TABLE `payable` (
  `payableid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(120) default NULL,
  `amount` double NOT NULL,
  `vat` double NOT NULL,
  `supplierid` int(10) unsigned NOT NULL,
  `transactionid` int(10) unsigned NOT NULL,
  `createdby` varchar(16) default NULL,
  `duedate` datetime default NULL,
  PRIMARY KEY  (`payableid`),
  KEY `fk_payable_supplier` (`supplierid`),
  KEY `fk_payable_trans` (`transactionid`),
  KEY `createdby` (`createdby`),
  CONSTRAINT `payable_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`),
  CONSTRAINT `payable_ibfk_2` FOREIGN KEY (`transactionid`) REFERENCES `transaction` (`transactionid`),
  CONSTRAINT `payable_ibfk_3` FOREIGN KEY (`createdby`) REFERENCES `user` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payable`
--

/*!40000 ALTER TABLE `payable` DISABLE KEYS */;
/*!40000 ALTER TABLE `payable` ENABLE KEYS */;


--
-- Definition of table `payaccount`
--

DROP TABLE IF EXISTS `payaccount`;
CREATE TABLE `payaccount` (
  `accountid` int(10) unsigned NOT NULL auto_increment,
  `formula` varchar(256) default NULL,
  `calcseq` smallint(5) unsigned default NULL,
  `inputtype` smallint(5) unsigned default NULL,
  `glaccountid` int(10) unsigned default NULL,
  PRIMARY KEY  (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payaccount`
--

/*!40000 ALTER TABLE `payaccount` DISABLE KEYS */;
INSERT INTO `payaccount` (`accountid`,`formula`,`calcseq`,`inputtype`,`glaccountid`) VALUES 
 (1010,'attribute(salary)',1010,0,7040),
 (1020,'attribute(hourrate)',1020,1,NULL),
 (1030,'attribute(hourrate) * attribute(hours_per_day)',1030,2,NULL),
 (2010,'(-1) * attribute(salary) / 160',2010,1,7040),
 (2020,'(-1) * attribute(salary)/21',2020,2,7040),
 (2030,'sick_leave(2031, 2032)',2030,2,7040),
 (2031,'0',2031,2,NULL),
 (2032,'(-1) * attribute(salary) / 21',2032,2,7040),
 (2033,'yearAccountQuantitySum(2031)',2033,2,NULL),
 (2040,'(-1) * attribute(late_arrival_penalty)',2040,3,NULL),
 (3010,'',3010,0,NULL),
 (3020,'',3020,0,NULL),
 (4010,'',4010,0,NULL),
 (4020,'-30000',4020,0,NULL),
 (4030,'-1250',4030,3,NULL),
 (4040,'',4040,3,NULL),
 (4050,'',4050,0,NULL),
 (4060,'',4060,0,NULL),
 (4070,'',4070,0,NULL),
 (4080,'',4080,0,NULL),
 (4090,'',4090,0,NULL),
 (4100,'',4100,0,NULL),
 (5010,'(-1) * periodSum(earnings) * 0.05',5010,0,NULL),
 (5020,'(-1) * periodSum(earnings) * 0.05',5020,0,NULL),
 (9008,'periodSum(taxable)',9008,0,NULL),
 (9009,'advanced_percent(th_tax_ap, 12*periodSum(taxable))',9009,0,NULL),
 (9010,'(-1) * advanced_percent(th_tax_ap, 12*periodSum(taxable)) * periodSum(taxable)',9010,0,NULL);
/*!40000 ALTER TABLE `payaccount` ENABLE KEYS */;


--
-- Definition of table `payaccount_description`
--

DROP TABLE IF EXISTS `payaccount_description`;
CREATE TABLE `payaccount_description` (
  `accountid` int(10) unsigned NOT NULL,
  `language` varchar(16) NOT NULL,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`accountid`,`language`),
  CONSTRAINT `payaccount_description_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `payaccount` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payaccount_description`
--

/*!40000 ALTER TABLE `payaccount_description` DISABLE KEYS */;
INSERT INTO `payaccount_description` (`accountid`,`language`,`description`) VALUES 
 (1010,'en','Monthly salary'),
 (1010,'sv','MÃ¥nadslÃ¶n'),
 (1010,'th','Monthly salary'),
 (1020,'en','Hourly pay'),
 (1020,'sv','Arbetad tid'),
 (1020,'th','Hourlu pay'),
 (1030,'en','Daily pay'),
 (1030,'sv','Arbetad dag'),
 (1030,'th','Daily pay'),
 (2010,'en','Hour absence'),
 (2010,'sv','TimfrÃ¥nvaro'),
 (2010,'th','Hourly absence'),
 (2020,'en','Day absence'),
 (2020,'sv','DagfrÃ¥nvaro'),
 (2020,'th','Day absence'),
 (2030,'en','Sick leave'),
 (2030,'sv','SjukfrÃ¥nvaro'),
 (2030,'th','Sick leave'),
 (2031,'en','Paid sick leave'),
 (2031,'sv','Betald sjukfrÃ¥nvaro'),
 (2031,'th','Paid sick leave'),
 (2032,'en','Unpaid sick leave'),
 (2032,'sv','Obetald sjukfrÃ¥nvaro'),
 (2032,'th','Unpaid sick-leave'),
 (2033,'en','Numer of paid sick days'),
 (2033,'sv','Antal betalda sjukdagar'),
 (2033,'th','Antal betalda sjukdagar'),
 (2040,'en','Late arrival'),
 (2040,'sv','Sen ankomst'),
 (2040,'th','Late arrival'),
 (3010,'en','Travel expenses'),
 (3010,'sv','ReseutlÃ¤gg'),
 (3010,'th','Travel expenses'),
 (3020,'en','Health care'),
 (3020,'sv','SjukvÃ¥rdsutlÃ¤gg'),
 (3020,'th','Health care'),
 (4010,'en','Taxpayer allowance'),
 (4010,'sv','Grundavdrag'),
 (4010,'th','Taxpayer deduction'),
 (4020,'en','Spouse allowance'),
 (4020,'sv','Partneravdrag'),
 (4020,'th','Spouse allowance'),
 (4030,'en','Child allowance'),
 (4030,'sv','Barnavdrag'),
 (4030,'th','Child allowance'),
 (4040,'en','Child education allowance'),
 (4040,'sv','Skolavdrag'),
 (4040,'th','Child education allowance'),
 (4050,'en','Old age allowance'),
 (4050,'sv','Ã…ldersavdrag'),
 (4050,'th','Old age allowance'),
 (4060,'en','Dependent parent allowance'),
 (4060,'sv','FÃ¶rÃ¤ldraavdrag'),
 (4060,'th','Dependent parent allowance'),
 (4070,'en','Life insurance deduction'),
 (4070,'sv','LivfÃ¶rsÃ¤kringsavdrag'),
 (4070,'th','Life insurance deduction'),
 (4080,'en','Mortgage interest'),
 (4080,'sv','RÃ¤nteavdrag'),
 (4080,'th','Mortgage interest'),
 (4090,'en','Charitable donations'),
 (4090,'sv','VÃ¤lgÃ¶renhetsavdrag'),
 (4090,'th','Charitable donations'),
 (4100,'en','Contributions to political parties'),
 (4100,'sv','Politikavdrag'),
 (4100,'th','Contributions to political parties'),
 (5010,'en','Social security fund - employeer'),
 (5010,'sv','Arbetsgivaravgifter'),
 (5010,'th','Social security fund - Employeer'),
 (5020,'en','Social security fund - employee'),
 (5020,'sv','SocialfÃ¶rsÃ¤kring'),
 (5020,'th','Social security fund - Employee'),
 (9008,'en','Taxable sum'),
 (9008,'sv','Summa skattepliktigt'),
 (9008,'th','Taxable sum'),
 (9009,'en','Effective tax percent'),
 (9009,'sv','Genomsnittlig skattesats'),
 (9009,'th','Effective tax percent'),
 (9010,'en','Tax'),
 (9010,'sv','Skatt'),
 (9010,'th','Tax');
/*!40000 ALTER TABLE `payaccount_description` ENABLE KEYS */;


--
-- Definition of table `payaccount_group`
--

DROP TABLE IF EXISTS `payaccount_group`;
CREATE TABLE `payaccount_group` (
  `groupid` int(10) unsigned NOT NULL,
  `accountid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`groupid`,`accountid`),
  KEY `accountid` (`accountid`),
  CONSTRAINT `payaccount_group_ibfk_2` FOREIGN KEY (`groupid`) REFERENCES `payaccountgroup` (`groupid`),
  CONSTRAINT `payaccount_group_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `payaccount` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payaccount_group`
--

/*!40000 ALTER TABLE `payaccount_group` DISABLE KEYS */;
INSERT INTO `payaccount_group` (`groupid`,`accountid`) VALUES 
 (1,1010),
 (2,1010),
 (4,1010),
 (1,1020),
 (2,1020),
 (4,1020),
 (22,1020),
 (1,1030),
 (2,1030),
 (4,1030),
 (22,1030),
 (1,2010),
 (2,2010),
 (4,2010),
 (21,2010),
 (1,2020),
 (2,2020),
 (4,2020),
 (21,2020),
 (1,2030),
 (2,2030),
 (4,2030),
 (21,2030),
 (1,2031),
 (2,2031),
 (4,2031),
 (1,2032),
 (2,2032),
 (4,2032),
 (1,2040),
 (2,2040),
 (4,2040),
 (21,2040),
 (1,3010),
 (30,3010),
 (1,3020),
 (30,3020),
 (2,4010),
 (2,4020),
 (2,4030),
 (2,4040),
 (2,4050),
 (2,4060),
 (2,4070),
 (2,4080),
 (2,4090),
 (2,4100),
 (11,5010),
 (1,5020),
 (2,5020),
 (11,5020),
 (1,9010),
 (3,9010);
/*!40000 ALTER TABLE `payaccount_group` ENABLE KEYS */;


--
-- Definition of table `payaccountgroup`
--

DROP TABLE IF EXISTS `payaccountgroup`;
CREATE TABLE `payaccountgroup` (
  `groupid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `report` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payaccountgroup`
--

/*!40000 ALTER TABLE `payaccountgroup` DISABLE KEYS */;
INSERT INTO `payaccountgroup` (`groupid`,`name`,`report`) VALUES 
 (1,'payable',1),
 (2,'taxable',NULL),
 (3,'tax',1),
 (4,'earnings',NULL),
 (11,'social_security_fund',1),
 (21,'attendence',NULL),
 (22,'attendence_hourly',NULL),
 (30,'expenses',NULL);
/*!40000 ALTER TABLE `payaccountgroup` ENABLE KEYS */;


--
-- Definition of table `payaccountgroup_description`
--

DROP TABLE IF EXISTS `payaccountgroup_description`;
CREATE TABLE `payaccountgroup_description` (
  `groupid` int(10) unsigned NOT NULL,
  `language` varchar(16) NOT NULL,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`groupid`,`language`),
  CONSTRAINT `payaccountgroup_description_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `payaccountgroup` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payaccountgroup_description`
--

/*!40000 ALTER TABLE `payaccountgroup_description` DISABLE KEYS */;
INSERT INTO `payaccountgroup_description` (`groupid`,`language`,`description`) VALUES 
 (1,'en','Payable'),
 (1,'sv','Att utbetala'),
 (1,'th','Skattepriktig'),
 (2,'en','Taxable'),
 (2,'sv','Skattepliktigt'),
 (2,'th','Standard'),
 (3,'en','Tax'),
 (3,'sv','Skatt'),
 (3,'th','IntjÃ¤nt'),
 (4,'en','Earnings'),
 (4,'sv','IntjÃ¤nat'),
 (4,'th','Att utbetala'),
 (11,'en','Social security fund'),
 (11,'sv','SocialfÃ¶rsÃ¤kring'),
 (11,'th','Social security fund'),
 (21,'en','Attendence'),
 (21,'sv','NÃ¤rvaro'),
 (21,'th','NÃ¤rvaro'),
 (22,'en','Attendence (hourly)'),
 (22,'sv','NÃ¤rvaro'),
 (22,'th','UtlÃ¤gg'),
 (30,'en','Expenses'),
 (30,'sv','UtlÃ¤gg'),
 (30,'th','Expenses');
/*!40000 ALTER TABLE `payaccountgroup_description` ENABLE KEYS */;


--
-- Definition of table `payevent`
--

DROP TABLE IF EXISTS `payevent`;
CREATE TABLE `payevent` (
  `employeeid` int(10) unsigned NOT NULL,
  `periodid` int(10) unsigned NOT NULL,
  `payeventid` int(10) unsigned NOT NULL auto_increment,
  `accountid` int(10) unsigned NOT NULL,
  `value` double default NULL,
  `narrative` varchar(80) default NULL,
  `starttime` datetime default NULL,
  `endtime` datetime default NULL,
  `amount` double default NULL,
  `unit_price` double default NULL,
  `derived` smallint(5) unsigned default NULL,
  `quantity` int(11) default NULL,
  `regtime` datetime NOT NULL,
  `parentid` int(10) unsigned default NULL,
  `correction` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  USING BTREE (`payeventid`),
  KEY `employeeid` (`employeeid`),
  KEY `periodid` (`periodid`),
  KEY `accountid` (`accountid`),
  KEY `parentid` (`parentid`),
  CONSTRAINT `payevent_ibfk_4` FOREIGN KEY (`parentid`) REFERENCES `payevent` (`payeventid`),
  CONSTRAINT `payevent_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employee` (`employeeid`),
  CONSTRAINT `payevent_ibfk_2` FOREIGN KEY (`periodid`) REFERENCES `payperiod` (`periodid`),
  CONSTRAINT `payevent_ibfk_3` FOREIGN KEY (`accountid`) REFERENCES `payaccount` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payevent`
--

/*!40000 ALTER TABLE `payevent` DISABLE KEYS */;
/*!40000 ALTER TABLE `payevent` ENABLE KEYS */;


--
-- Definition of table `payment`
--

DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `paymentid` int(10) unsigned NOT NULL auto_increment,
  `supplierid` int(10) unsigned NOT NULL,
  `amount` double NOT NULL,
  `transactionid` int(10) unsigned NOT NULL,
  `createdby` varchar(16) default NULL,
  PRIMARY KEY  (`paymentid`),
  KEY `fk_payment_supplier` (`supplierid`),
  KEY `fk_payment_trans` (`transactionid`),
  KEY `createdby` (`createdby`),
  CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`createdby`) REFERENCES `user` (`username`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`),
  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`transactionid`) REFERENCES `transaction` (`transactionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payment`
--

/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;


--
-- Definition of table `payment_allocation`
--

DROP TABLE IF EXISTS `payment_allocation`;
CREATE TABLE `payment_allocation` (
  `paymentid` int(10) unsigned NOT NULL,
  `payableid` int(10) unsigned NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY  (`paymentid`,`payableid`),
  KEY `fk_payable_allocation` (`payableid`),
  CONSTRAINT `payment_allocation_ibfk_2` FOREIGN KEY (`payableid`) REFERENCES `payable` (`payableid`),
  CONSTRAINT `payment_allocation_ibfk_1` FOREIGN KEY (`paymentid`) REFERENCES `payment` (`paymentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payment_allocation`
--

/*!40000 ALTER TABLE `payment_allocation` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_allocation` ENABLE KEYS */;


--
-- Definition of table `payperiod`
--

DROP TABLE IF EXISTS `payperiod`;
CREATE TABLE `payperiod` (
  `periodid` int(10) unsigned NOT NULL default '0',
  `starttime` datetime default NULL,
  `endtime` datetime default NULL,
  `locked` smallint(5) unsigned default NULL,
  `state_receivables` smallint(6) default NULL,
  PRIMARY KEY  (`periodid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payperiod`
--

/*!40000 ALTER TABLE `payperiod` DISABLE KEYS */;
INSERT INTO `payperiod` (`periodid`,`starttime`,`endtime`,`locked`,`state_receivables`) VALUES 
 (1,'2007-01-01 00:00:00','2007-02-01 00:00:00',1,NULL),
 (2,'2007-02-01 00:00:00','2007-03-01 00:00:00',1,NULL),
 (3,'2007-03-01 00:00:00','2007-04-01 00:00:00',1,NULL),
 (4,'2007-04-01 00:00:00','2007-05-01 00:00:00',1,NULL),
 (5,'2007-05-01 00:00:00','2007-06-01 00:00:00',1,NULL),
 (6,'2007-06-01 00:00:00','2007-07-01 00:00:00',NULL,NULL),
 (7,'2007-07-01 00:00:00','2007-08-01 00:00:00',NULL,NULL),
 (8,'2007-08-01 00:00:00','2007-09-01 00:00:00',NULL,NULL),
 (9,'2007-09-01 00:00:00','2007-10-01 00:00:00',NULL,NULL),
 (10,'2007-10-01 00:00:00','2007-11-01 00:00:00',NULL,NULL),
 (11,'2007-11-01 00:00:00','2007-12-01 00:00:00',NULL,NULL),
 (12,'2007-12-01 00:00:00','2008-01-01 00:00:00',NULL,NULL);
/*!40000 ALTER TABLE `payperiod` ENABLE KEYS */;


--
-- Definition of table `permission`
--

DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission` (
  `permissionid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  USING BTREE (`permissionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `permission`
--

/*!40000 ALTER TABLE `permission` DISABLE KEYS */;
INSERT INTO `permission` (`permissionid`,`description`) VALUES 
 (1,'Administrate users'),
 (2,'Configurate payroll'),
 (3,'Administrate employees'),
 (4,'Register payevents'),
 (5,'Self service');
/*!40000 ALTER TABLE `permission` ENABLE KEYS */;


--
-- Definition of table `policy`
--

DROP TABLE IF EXISTS `policy`;
CREATE TABLE `policy` (
  `policyid` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`policyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `policy`
--

/*!40000 ALTER TABLE `policy` DISABLE KEYS */;
INSERT INTO `policy` (`policyid`) VALUES 
 (1),
 (2);
/*!40000 ALTER TABLE `policy` ENABLE KEYS */;


--
-- Definition of table `policy_accountgroup`
--

DROP TABLE IF EXISTS `policy_accountgroup`;
CREATE TABLE `policy_accountgroup` (
  `policyid` int(10) unsigned NOT NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`policyid`,`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `policy_accountgroup`
--

/*!40000 ALTER TABLE `policy_accountgroup` DISABLE KEYS */;
INSERT INTO `policy_accountgroup` (`policyid`,`groupid`) VALUES 
 (1,21),
 (1,30),
 (2,22),
 (2,30);
/*!40000 ALTER TABLE `policy_accountgroup` ENABLE KEYS */;


--
-- Definition of table `policy_attribute`
--

DROP TABLE IF EXISTS `policy_attribute`;
CREATE TABLE `policy_attribute` (
  `policyid` int(10) unsigned NOT NULL default '0',
  `attributeid` smallint(5) unsigned NOT NULL default '0',
  `fromperiodid` int(10) unsigned NOT NULL,
  `toperiodid` int(10) unsigned default NULL,
  `value` double default NULL,
  PRIMARY KEY  USING BTREE (`policyid`,`attributeid`,`fromperiodid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `policy_attribute`
--

/*!40000 ALTER TABLE `policy_attribute` DISABLE KEYS */;
INSERT INTO `policy_attribute` (`policyid`,`attributeid`,`fromperiodid`,`toperiodid`,`value`) VALUES 
 (0,2,0,NULL,NULL),
 (1,1,0,NULL,NULL),
 (1,2,0,3,NULL),
 (1,2,3,NULL,20),
 (1,5,5,NULL,30),
 (2,3,5,NULL,NULL),
 (2,4,5,NULL,8);
/*!40000 ALTER TABLE `policy_attribute` ENABLE KEYS */;


--
-- Definition of table `policy_description`
--

DROP TABLE IF EXISTS `policy_description`;
CREATE TABLE `policy_description` (
  `policyid` int(10) unsigned NOT NULL default '0',
  `language` varchar(16) NOT NULL default '',
  `description` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`policyid`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `policy_description`
--

/*!40000 ALTER TABLE `policy_description` DISABLE KEYS */;
INSERT INTO `policy_description` (`policyid`,`language`,`description`) VALUES 
 (1,'en','Salaried'),
 (1,'sv','MÃ¥nadsavlÃ¶nad'),
 (1,'th','Salaried'),
 (2,'en','Hourly paid'),
 (2,'sv','TimavlÃ¶nad'),
 (2,'th','TimavlÃ¶nad');
/*!40000 ALTER TABLE `policy_description` ENABLE KEYS */;


--
-- Definition of table `policy_payitem`
--

DROP TABLE IF EXISTS `policy_payitem`;
CREATE TABLE `policy_payitem` (
  `policyid` int(10) unsigned NOT NULL,
  `no` smallint(5) unsigned NOT NULL default '0',
  `fromperiodid` int(10) unsigned NOT NULL,
  `toperiodid` int(10) unsigned default NULL,
  `amount` double default NULL,
  `accountid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`policyid`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `policy_payitem`
--

/*!40000 ALTER TABLE `policy_payitem` DISABLE KEYS */;
INSERT INTO `policy_payitem` (`policyid`,`no`,`fromperiodid`,`toperiodid`,`amount`,`accountid`) VALUES 
 (1,1,3,NULL,NULL,1010),
 (1,2,3,NULL,NULL,5010),
 (1,3,3,NULL,NULL,5020),
 (1,4,3,NULL,NULL,9010),
 (1,5,3,NULL,NULL,9008),
 (1,6,3,NULL,NULL,9009),
 (1,7,5,NULL,NULL,2033),
 (2,1,5,NULL,NULL,5010),
 (2,2,5,NULL,NULL,5020),
 (2,3,5,NULL,NULL,9010),
 (2,4,5,NULL,NULL,9008),
 (2,5,5,NULL,NULL,9009);
/*!40000 ALTER TABLE `policy_payitem` ENABLE KEYS */;


--
-- Definition of table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `productid` varchar(32) NOT NULL,
  `description` varchar(80) NOT NULL default '',
  `purchase_price` double default '0',
  `sales_price` double default '0',
  `model` varchar(80) NOT NULL default '',
  `quantity` int(11) NOT NULL default '0',
  `categoryid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product`
--

/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` (`productid`,`description`,`purchase_price`,`sales_price`,`model`,`quantity`,`categoryid`) VALUES 
 ('1','',NULL,NULL,'Unspecified',0,1),
 ('1003','HP Compaq nx7300 15.4\" WXGA, Cel M440, 1 GB,80GB,DVDRW,WLAN,VHB',3500,5795,'HP Compaq nx7300',5,1),
 ('1004','HP Pavilion G5052 15.4\"WXGA,Cel M440,1 GB,120GB,DVDRW,WLAN,VHB',4500,5995,'HP Pavilion G5052',-4,1),
 ('1010','Canon Digital IXUS 800 IS, 6MP, 2.5\" LCD 4x optisk zoom, optisk bildstabilisator',2500,3095,'Canon Digital IXUS 800',-1,1),
 ('1011','Canon PowerShot S3 IS, 6.0 MP, 12x optisk zoom, vridbar 2.0\" LCD',2800,3995,'Canon PowerShot S3',0,1);
/*!40000 ALTER TABLE `product` ENABLE KEYS */;


--
-- Definition of table `product_supplier`
--

DROP TABLE IF EXISTS `product_supplier`;
CREATE TABLE `product_supplier` (
  `productid` varchar(32) NOT NULL default '0',
  `supplierid` int(10) unsigned NOT NULL default '0',
  `price` double default NULL,
  PRIMARY KEY  (`productid`,`supplierid`),
  KEY `fk_product_supplier` (`supplierid`),
  CONSTRAINT `fk_supplier_product` FOREIGN KEY (`productid`) REFERENCES `product` (`productid`),
  CONSTRAINT `fk_product_supplier` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_supplier`
--

/*!40000 ALTER TABLE `product_supplier` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_supplier` ENABLE KEYS */;


--
-- Definition of table `productionorder`
--

DROP TABLE IF EXISTS `productionorder`;
CREATE TABLE `productionorder` (
  `orderid` int(10) unsigned NOT NULL auto_increment,
  `createdby` varchar(16) default NULL,
  `createdtime` datetime default NULL,
  `transactionid` int(10) unsigned default NULL,
  `cancelled` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`orderid`),
  KEY `createdby` (`createdby`),
  KEY `transactionid` (`transactionid`),
  CONSTRAINT `productionorder_ibfk_2` FOREIGN KEY (`transactionid`) REFERENCES `transaction` (`transactionid`),
  CONSTRAINT `productionorder_ibfk_1` FOREIGN KEY (`createdby`) REFERENCES `user` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `productionorder`
--

/*!40000 ALTER TABLE `productionorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `productionorder` ENABLE KEYS */;


--
-- Definition of table `productionorder_item`
--

DROP TABLE IF EXISTS `productionorder_item`;
CREATE TABLE `productionorder_item` (
  `orderid` int(10) unsigned NOT NULL,
  `no` smallint(5) unsigned NOT NULL,
  `productid` varchar(32) default NULL,
  `quantity` int(11) default NULL,
  PRIMARY KEY  (`orderid`,`no`),
  KEY `fk_productionorder_product` (`productid`),
  CONSTRAINT `fk_productionorder_item` FOREIGN KEY (`orderid`) REFERENCES `productionorder` (`orderid`),
  CONSTRAINT `fk_productionorder_product` FOREIGN KEY (`productid`) REFERENCES `product` (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `productionorder_item`
--

/*!40000 ALTER TABLE `productionorder_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `productionorder_item` ENABLE KEYS */;


--
-- Definition of table `project`
--

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `projectid` int(10) unsigned NOT NULL,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`projectid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `project`
--

/*!40000 ALTER TABLE `project` DISABLE KEYS */;
INSERT INTO `project` (`projectid`,`description`) VALUES 
 (101,'Test project'),
 (900,'Internal');
/*!40000 ALTER TABLE `project` ENABLE KEYS */;


--
-- Definition of table `purchaseorder`
--

DROP TABLE IF EXISTS `purchaseorder`;
CREATE TABLE `purchaseorder` (
  `orderid` int(10) unsigned NOT NULL auto_increment,
  `supplierid` int(10) unsigned NOT NULL,
  `orderdate` datetime NOT NULL,
  `cancelled` smallint(5) unsigned NOT NULL default '0',
  `payableid` int(10) unsigned default NULL,
  `createdby` varchar(16) default NULL,
  PRIMARY KEY  (`orderid`),
  KEY `fk_purchaseorder_supplier` (`supplierid`),
  KEY `fk_purchaseorder_payable` (`payableid`),
  KEY `createdby` (`createdby`),
  CONSTRAINT `purchaseorder_ibfk_3` FOREIGN KEY (`createdby`) REFERENCES `user` (`username`),
  CONSTRAINT `purchaseorder_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`),
  CONSTRAINT `purchaseorder_ibfk_2` FOREIGN KEY (`payableid`) REFERENCES `payable` (`payableid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `purchaseorder`
--

/*!40000 ALTER TABLE `purchaseorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchaseorder` ENABLE KEYS */;


--
-- Definition of table `purchaseorder_item`
--

DROP TABLE IF EXISTS `purchaseorder_item`;
CREATE TABLE `purchaseorder_item` (
  `orderid` int(10) unsigned NOT NULL,
  `no` smallint(5) unsigned NOT NULL,
  `productid` varchar(32) NOT NULL,
  `quantity` int(11) default '0',
  `unitprice` double default NULL,
  `vat` double default NULL,
  `accountid` int(10) unsigned default NULL,
  `comment` varchar(80) default NULL,
  `received_quantity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  USING BTREE (`orderid`,`no`),
  KEY `fk_purchaseorder_product` (`productid`),
  CONSTRAINT `fk_purchaseorder_item` FOREIGN KEY (`orderid`) REFERENCES `purchaseorder` (`orderid`),
  CONSTRAINT `fk_purchaseorder_product` FOREIGN KEY (`productid`) REFERENCES `product` (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `purchaseorder_item`
--

/*!40000 ALTER TABLE `purchaseorder_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchaseorder_item` ENABLE KEYS */;


--
-- Definition of table `receipt`
--

DROP TABLE IF EXISTS `receipt`;
CREATE TABLE `receipt` (
  `receiptid` int(10) unsigned NOT NULL auto_increment,
  `customerid` int(10) unsigned NOT NULL,
  `amount` double NOT NULL,
  `transactionid` int(10) unsigned default NULL,
  `createdby` varchar(16) default NULL,
  PRIMARY KEY  USING BTREE (`receiptid`),
  KEY `fk_receipt_customer` (`customerid`),
  KEY `fk_receipt_trans` (`transactionid`),
  KEY `createdby` (`createdby`),
  CONSTRAINT `receipt_ibfk_1` FOREIGN KEY (`customerid`) REFERENCES `customer` (`customerid`),
  CONSTRAINT `receipt_ibfk_2` FOREIGN KEY (`transactionid`) REFERENCES `transaction` (`transactionid`),
  CONSTRAINT `receipt_ibfk_3` FOREIGN KEY (`createdby`) REFERENCES `user` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `receipt`
--

/*!40000 ALTER TABLE `receipt` DISABLE KEYS */;
/*!40000 ALTER TABLE `receipt` ENABLE KEYS */;


--
-- Definition of table `receipt_allocation`
--

DROP TABLE IF EXISTS `receipt_allocation`;
CREATE TABLE `receipt_allocation` (
  `receiptid` int(10) unsigned NOT NULL,
  `orderid` int(10) unsigned NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY  USING BTREE (`receiptid`,`orderid`),
  KEY `fk_order_allocation` (`orderid`),
  CONSTRAINT `receipt_allocation_ibfk_2` FOREIGN KEY (`orderid`) REFERENCES `salesorder` (`orderid`),
  CONSTRAINT `receipt_allocation_ibfk_1` FOREIGN KEY (`receiptid`) REFERENCES `receipt` (`receiptid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `receipt_allocation`
--

/*!40000 ALTER TABLE `receipt_allocation` DISABLE KEYS */;
/*!40000 ALTER TABLE `receipt_allocation` ENABLE KEYS */;


--
-- Definition of table `recur_salesorder`
--

DROP TABLE IF EXISTS `recur_salesorder`;
CREATE TABLE `recur_salesorder` (
  `orderid` int(10) unsigned NOT NULL,
  `active` smallint(6) NOT NULL default '1',
  PRIMARY KEY  (`orderid`),
  CONSTRAINT `fk_recur_salesorder` FOREIGN KEY (`orderid`) REFERENCES `salesorder` (`orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `recur_salesorder`
--

/*!40000 ALTER TABLE `recur_salesorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `recur_salesorder` ENABLE KEYS */;


--
-- Definition of table `salesorder`
--

DROP TABLE IF EXISTS `salesorder`;
CREATE TABLE `salesorder` (
  `orderid` int(10) unsigned NOT NULL auto_increment,
  `orderdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `customerid` int(10) unsigned NOT NULL default '0',
  `invoice_transid` int(10) unsigned default NULL,
  `cancelled` smallint(5) unsigned NOT NULL default '0',
  `createdby` varchar(16) default NULL,
  `invoice_sent` smallint(6) default NULL,
  `duedate` datetime default NULL,
  PRIMARY KEY  (`orderid`),
  KEY `fk_salesorder_customer` (`customerid`),
  KEY `fk_salesorder_invoice` (`invoice_transid`),
  KEY `createdby` (`createdby`),
  CONSTRAINT `salesorder_ibfk_1` FOREIGN KEY (`customerid`) REFERENCES `customer` (`customerid`),
  CONSTRAINT `salesorder_ibfk_2` FOREIGN KEY (`invoice_transid`) REFERENCES `transaction` (`transactionid`),
  CONSTRAINT `salesorder_ibfk_3` FOREIGN KEY (`createdby`) REFERENCES `user` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `salesorder`
--

/*!40000 ALTER TABLE `salesorder` DISABLE KEYS */;
/*!40000 ALTER TABLE `salesorder` ENABLE KEYS */;


--
-- Definition of table `salesorder_item`
--

DROP TABLE IF EXISTS `salesorder_item`;
CREATE TABLE `salesorder_item` (
  `orderid` int(10) unsigned NOT NULL,
  `productid` varchar(32) NOT NULL,
  `quantity` int(11) default '0',
  `unitprice` double NOT NULL,
  `no` smallint(5) unsigned NOT NULL,
  `comment` varchar(80) NOT NULL,
  `vat` double NOT NULL,
  PRIMARY KEY  USING BTREE (`orderid`,`no`),
  KEY `fk_salesorder_product` (`productid`),
  CONSTRAINT `fk_salesorder_item` FOREIGN KEY (`orderid`) REFERENCES `salesorder` (`orderid`),
  CONSTRAINT `fk_salesorder_product` FOREIGN KEY (`productid`) REFERENCES `product` (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `salesorder_item`
--

/*!40000 ALTER TABLE `salesorder_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `salesorder_item` ENABLE KEYS */;


--
-- Definition of table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE `schedule` (
  `scheduleid` int(11) NOT NULL auto_increment,
  `description` varchar(32) default NULL,
  `recur_type` smallint(6) default NULL,
  `recur_interval` smallint(6) default NULL,
  `recur_count` smallint(6) default NULL,
  PRIMARY KEY  (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `schedule`
--

/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
INSERT INTO `schedule` (`scheduleid`,`description`,`recur_type`,`recur_interval`,`recur_count`) VALUES 
 (1,'NineToFive',1,7,NULL);
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;


--
-- Definition of table `schedule_shift`
--

DROP TABLE IF EXISTS `schedule_shift`;
CREATE TABLE `schedule_shift` (
  `scheduleid` int(11) NOT NULL,
  `shiftid` int(11) NOT NULL,
  PRIMARY KEY  (`scheduleid`,`shiftid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `schedule_shift`
--

/*!40000 ALTER TABLE `schedule_shift` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_shift` ENABLE KEYS */;


--
-- Definition of table `se_taxtable`
--

DROP TABLE IF EXISTS `se_taxtable`;
CREATE TABLE `se_taxtable` (
  `year` smallint(5) unsigned NOT NULL,
  `periodlength` smallint(5) unsigned NOT NULL,
  `tableno` smallint(5) unsigned NOT NULL,
  `floor` double unsigned NOT NULL,
  `ceiling` double unsigned default NULL,
  `type` char(1) NOT NULL,
  `tax1` double unsigned default NULL,
  `tax2` double unsigned default NULL,
  `tax3` double unsigned default NULL,
  `tax4` double unsigned default NULL,
  `tax5` double unsigned default NULL,
  PRIMARY KEY  (`year`,`periodlength`,`tableno`,`floor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `se_taxtable`
--

/*!40000 ALTER TABLE `se_taxtable` DISABLE KEYS */;
/*!40000 ALTER TABLE `se_taxtable` ENABLE KEYS */;


--
-- Definition of table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `default_bankaccount` varchar(45) default NULL,
  `payroll_bankaccount` varchar(45) default NULL,
  `credit_length` int(10) unsigned default '30'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `settings`
--

/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`default_bankaccount`,`payroll_bankaccount`,`credit_length`) VALUES 
 ('12345','34567',30);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;


--
-- Definition of table `stockmove`
--

DROP TABLE IF EXISTS `stockmove`;
CREATE TABLE `stockmove` (
  `moveid` int(10) unsigned NOT NULL auto_increment,
  `productid` varchar(32) NOT NULL,
  `diff` int(11) NOT NULL,
  `narrative` varchar(80) default NULL,
  `transactionid` int(10) unsigned default NULL,
  `salesorderid` int(10) unsigned default NULL,
  `purchaseorderid` int(10) unsigned default NULL,
  `no` smallint(5) unsigned default NULL,
  `createdby` varchar(16) default NULL,
  `productionorderid` int(10) unsigned default NULL,
  PRIMARY KEY  (`moveid`),
  KEY `fk_stockmove_productionorder` (`productionorderid`),
  KEY `fk_stockmove_createdby` (`createdby`),
  KEY `fk_stockmove_productid` (`productid`),
  KEY `fk_stockmove_transaction` (`transactionid`),
  KEY `fk_stockmove_salesorder` (`salesorderid`),
  KEY `fk_stockmove_purchaseorder` (`purchaseorderid`),
  CONSTRAINT `fk_stockmove_purchaseorder` FOREIGN KEY (`purchaseorderid`) REFERENCES `purchaseorder` (`orderid`),
  CONSTRAINT `fk_stockmove_createdby` FOREIGN KEY (`createdby`) REFERENCES `user` (`username`),
  CONSTRAINT `fk_stockmove_productid` FOREIGN KEY (`productid`) REFERENCES `product` (`productid`),
  CONSTRAINT `fk_stockmove_productionorder` FOREIGN KEY (`productionorderid`) REFERENCES `productionorder` (`orderid`),
  CONSTRAINT `fk_stockmove_salesorder` FOREIGN KEY (`salesorderid`) REFERENCES `salesorder` (`orderid`),
  CONSTRAINT `fk_stockmove_transaction` FOREIGN KEY (`transactionid`) REFERENCES `transaction` (`transactionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `stockmove`
--

/*!40000 ALTER TABLE `stockmove` DISABLE KEYS */;
/*!40000 ALTER TABLE `stockmove` ENABLE KEYS */;


--
-- Definition of table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `supplierid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `email` varchar(80) default NULL,
  `streetaddress` varchar(120) default NULL,
  `city` varchar(80) default NULL,
  `zipcode` varchar(16) default NULL,
  `vatnumber` varchar(32) default NULL,
  `credit_account` int(10) unsigned default NULL,
  `credit_length` int(10) unsigned default NULL,
  PRIMARY KEY  (`supplierid`),
  KEY `fk_supplier_credit_account` (`credit_account`),
  CONSTRAINT `fk_supplier_credit_account` FOREIGN KEY (`credit_account`) REFERENCES `account` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `supplier`
--

/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` (`supplierid`,`name`,`email`,`streetaddress`,`city`,`zipcode`,`vatnumber`,`credit_account`,`credit_length`) VALUES 
 (1,'Hewlett Packard',NULL,NULL,NULL,NULL,NULL,NULL,NULL),
 (2,'Canon',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;


--
-- Definition of table `task`
--

DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `projectid` int(10) unsigned NOT NULL,
  `taskid` int(10) unsigned NOT NULL,
  `description` varchar(80) NOT NULL,
  `payaccountid` int(11) default NULL,
  PRIMARY KEY  (`projectid`,`taskid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `task`
--

/*!40000 ALTER TABLE `task` DISABLE KEYS */;
INSERT INTO `task` (`projectid`,`taskid`,`description`,`payaccountid`) VALUES 
 (101,10,'Specification',NULL),
 (101,11,'Implementation',NULL),
 (101,12,'Test',NULL),
 (900,910,'Sick leave',2030);
/*!40000 ALTER TABLE `task` ENABLE KEYS */;


--
-- Definition of table `team`
--

DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `teamid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `team`
--

/*!40000 ALTER TABLE `team` DISABLE KEYS */;
INSERT INTO `team` (`teamid`,`description`) VALUES 
 (1,'Office'),
 (2,'Factory');
/*!40000 ALTER TABLE `team` ENABLE KEYS */;


--
-- Definition of table `timedebit`
--

DROP TABLE IF EXISTS `timedebit`;
CREATE TABLE `timedebit` (
  `employeeid` int(11) NOT NULL,
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `projectid` int(11) NOT NULL,
  `taskid` int(11) NOT NULL,
  `description` varchar(80) default NULL,
  `minutes` int(11) NOT NULL,
  PRIMARY KEY  (`employeeid`,`projectid`,`taskid`,`starttime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `timedebit`
--

/*!40000 ALTER TABLE `timedebit` DISABLE KEYS */;
INSERT INTO `timedebit` (`employeeid`,`starttime`,`endtime`,`projectid`,`taskid`,`description`,`minutes`) VALUES 
 (15,'2007-05-28 00:00:00','2007-05-29 00:00:00',101,11,NULL,480),
 (15,'2007-06-04 00:00:00','2007-06-05 00:00:00',900,910,NULL,480);
/*!40000 ALTER TABLE `timedebit` ENABLE KEYS */;


--
-- Definition of table `timeregistration`
--

DROP TABLE IF EXISTS `timeregistration`;
CREATE TABLE `timeregistration` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` datetime NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `employeeid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `timeregistration`
--

/*!40000 ALTER TABLE `timeregistration` DISABLE KEYS */;
INSERT INTO `timeregistration` (`id`,`time`,`type`,`employeeid`) VALUES 
 (3,'2007-05-30 15:05:00',2,16),
 (4,'2007-05-30 16:05:00',1,16),
 (5,'2007-05-30 16:06:00',1,16),
 (6,'2007-05-30 16:06:00',2,16),
 (7,'2007-05-30 16:07:00',1,16),
 (8,'2007-06-02 18:13:06',1,15);
/*!40000 ALTER TABLE `timeregistration` ENABLE KEYS */;


--
-- Definition of table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE `transaction` (
  `transactionid` int(10) unsigned NOT NULL auto_increment,
  `narrative` varchar(80) NOT NULL default '',
  `transtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `cancel_transid` int(10) unsigned default NULL,
  `createdby` varchar(16) default NULL,
  `valid` smallint(5) unsigned NOT NULL default '1',
  PRIMARY KEY  (`transactionid`),
  KEY `createdby` (`createdby`),
  CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`createdby`) REFERENCES `user` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `transaction`
--

/*!40000 ALTER TABLE `transaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction` ENABLE KEYS */;


--
-- Definition of table `transaction_part`
--

DROP TABLE IF EXISTS `transaction_part`;
CREATE TABLE `transaction_part` (
  `transactionid` int(10) unsigned NOT NULL default '0',
  `accountid` int(10) unsigned NOT NULL default '0',
  `amount` double NOT NULL default '0',
  PRIMARY KEY  (`transactionid`,`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `transaction_part`
--

/*!40000 ALTER TABLE `transaction_part` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction_part` ENABLE KEYS */;


--
-- Definition of table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `username` varchar(16) NOT NULL default '',
  `full_name` varchar(64) default NULL,
  `password` varchar(32) default NULL,
  `employeeid` int(10) unsigned default NULL,
  `admin` smallint(5) unsigned default NULL,
  `language` varchar(8) default NULL,
  PRIMARY KEY  (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`username`,`full_name`,`password`,`employeeid`,`admin`,`language`) VALUES 
 ('admin','Admin','abc123',NULL,1,'en'),
 ('boj','Bo Jonsson','abc123',16,1,'sv'),
 ('frebe','Fredrik Bertilsson','abc123',15,1,'en'),
 ('guest','Guest','guest',1,NULL,'en'),
 ('mukda','Mukda','abc123',NULL,1,'th'),
 ('oui','Sakuntala Buttho','abc123',NULL,1,'th');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;


--
-- Definition of table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
CREATE TABLE `user_group` (
  `username` varchar(32) NOT NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`username`,`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_group`
--

/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
INSERT INTO `user_group` (`username`,`groupid`) VALUES 
 ('admin',1),
 ('admin',2),
 ('admin',3),
 ('admin',4),
 ('boj',2),
 ('boj',3),
 ('boj',4),
 ('frebe',1),
 ('frebe',2),
 ('frebe',3),
 ('frebe',4),
 ('guest',2),
 ('guest',3),
 ('mukda',3);
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;


--
-- Definition of table `usergroup`
--

DROP TABLE IF EXISTS `usergroup`;
CREATE TABLE `usergroup` (
  `groupid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  USING BTREE (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `usergroup`
--

/*!40000 ALTER TABLE `usergroup` DISABLE KEYS */;
INSERT INTO `usergroup` (`groupid`,`description`) VALUES 
 (1,'User administrator'),
 (2,'Regular staff'),
 (3,'Payroll administrator'),
 (4,'Payroll configurator');
/*!40000 ALTER TABLE `usergroup` ENABLE KEYS */;


--
-- Definition of table `usergroup_permission`
--

DROP TABLE IF EXISTS `usergroup_permission`;
CREATE TABLE `usergroup_permission` (
  `groupid` int(10) unsigned NOT NULL,
  `permissionid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`groupid`,`permissionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `usergroup_permission`
--

/*!40000 ALTER TABLE `usergroup_permission` DISABLE KEYS */;
INSERT INTO `usergroup_permission` (`groupid`,`permissionid`) VALUES 
 (1,1),
 (2,5),
 (3,3),
 (3,4),
 (4,2);
/*!40000 ALTER TABLE `usergroup_permission` ENABLE KEYS */;


--
-- Definition of table `vat_category`
--

DROP TABLE IF EXISTS `vat_category`;
CREATE TABLE `vat_category` (
  `vatcatid` int(10) unsigned NOT NULL auto_increment,
  `percent` smallint(5) unsigned NOT NULL,
  `description` varchar(45) NOT NULL,
  PRIMARY KEY  (`vatcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vat_category`
--

/*!40000 ALTER TABLE `vat_category` DISABLE KEYS */;
INSERT INTO `vat_category` (`vatcatid`,`percent`,`description`) VALUES 
 (1,7,'Default');
/*!40000 ALTER TABLE `vat_category` ENABLE KEYS */;


--
-- Definition of table `version`
--

DROP TABLE IF EXISTS `version`;
CREATE TABLE `version` (
  `dbversion` int(11) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `version`
--

/*!40000 ALTER TABLE `version` DISABLE KEYS */;
INSERT INTO `version` (`dbversion`) VALUES 
 (11);
/*!40000 ALTER TABLE `version` ENABLE KEYS */;


--
-- Definition of table `workshift`
--

DROP TABLE IF EXISTS `workshift`;
CREATE TABLE `workshift` (
  `shiftid` int(11) NOT NULL auto_increment,
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `recur_type` smallint(6) default NULL,
  `recur_interval` smallint(6) default NULL,
  `recur_count` smallint(6) default NULL,
  PRIMARY KEY  (`shiftid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `workshift`
--

/*!40000 ALTER TABLE `workshift` DISABLE KEYS */;
INSERT INTO `workshift` (`shiftid`,`starttime`,`endtime`,`recur_type`,`recur_interval`,`recur_count`) VALUES 
 (5,'2007-04-02 09:00:00','2007-04-02 17:00:00',NULL,NULL,NULL),
 (6,'2007-04-03 09:00:00','2007-04-03 17:00:00',NULL,NULL,NULL),
 (7,'2007-04-04 09:00:00','2007-04-04 17:00:00',NULL,NULL,NULL),
 (8,'2007-04-05 09:00:00','2007-04-05 17:00:00',NULL,NULL,NULL),
 (9,'2007-04-06 09:00:00','2007-04-06 17:00:00',NULL,NULL,NULL);
/*!40000 ALTER TABLE `workshift` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
