update companyinfo set
	vatnumber='123456',
	streetaddress='Demo street 1',
	city='Chicago',
	zipcode='12345',
	email='demo@therpsoft.com';

INSERT IGNORE INTO `customer` (`customerid`,`name`,`streetaddress`,`city`,`zipcode`,`email`,`vatnumber`) VALUES
 (2,'Kalle Anka','Ankeborgsv 1','Ankeborg','12345',NULL,NULL),
 (3,'Fantomen','Ddskallegrottan','Bengalien','23456',NULL,NULL),
 (4,'Olle','','','',NULL,NULL),
 (5,'Nisse','','','',NULL,NULL);

INSERT IGNORE INTO `team` (`teamid`,`description`) VALUES
 (1,'Office'),
 (2,'Factory');

INSERT IGNORE INTO `employee` (`employeeid`,`givenname`,`surname`,`active`,`bank_account`,`calctime`,`street_address`,`zipcode`,`city`,`email`, policyid) VALUES
 (1,'Christian','Darren',1,'1234 5678 90124','2007-05-22 10:28:39','311 West Wisconsin Ave.','WI 54208-2289',' Milwaukee',NULL,1),
 (2,'Ronnie','Cruz',1,'553645645645','2007-05-22 18:01:39','41 Marietta Street NW','GA 30304-3388','Atlanta',NULL,1),
 (3,'Paola','Leilo',1,'123456','2007-05-22 20:36:02','2704 Dixie Rd.','FL 33801','Lakeland',NULL,1),
 (4,'Amanda','Lawrence',1,'','2007-05-13 11:43:05','','','',NULL,1),
 (5,'???????','??????',1,'??????','2007-05-13 11:43:06','','','',NULL,1),
 (6,'Judith','Scott',1,'345345345','2007-05-22 10:35:55','2670 Broadway Street','CO 90202-4802','Denver',NULL,2),
 (7,'Lance','Smith',1,'345345345','2007-05-22 18:10:19','2701 Prospect Ave,  P.O. Box 202601','MT 69620-2601','Helena',NULL,2),
 (8,'Michelle','Harrison',1,'23523534523','2007-05-22 10:35:04','524 E 7th St','NE 69230','Cozad',NULL,2),
 (9,'Peter','Weight',1,'','2007-05-22 10:35:39','265 1th Street NW','GA 30418','Atlanta',NULL,2);
INSERT IGNORE INTO `employee` (`employeeid`,`givenname`,`surname`,`active`,`bank_account`,`calctime`,`street_address`,`zipcode`,`city`,`email`, policyid) VALUES
 (10,'Sarah','Hunley',1,'643634','2007-05-22 10:35:56','5804 North Lamar Blvd.','Texas 78852','Austin',NULL,2),
 (11,'Brad','Evans',1,'','2007-05-22 10:35:56','31 Medical Drive','UT 84214-4610','Salt Lake City',NULL,2),
 (12,'Ana','Mercedes',1,'','2007-05-22 10:35:57','414-216 W. Chestnut Street','KY 40202','Louisville',NULL,2),
 (13,'Mia','Kelly',1,'','2007-05-22 18:34:58','625 Grand Ave.','WY 82070-3846','Laramie',NULL,2),
 (14,'John','Rhodes',1,'','2007-05-22 10:35:58','514 W. 5th St.','ND 58318','Bottineau',NULL,2),
 (15,'Fredrik','Bertilsson',1,'353453','2007-05-29 19:57:34','Stiglöstgatan 77','586 46','Linköping',NULL,2),
 (16,'Bo','Jonsson',1,'345345345','2007-05-29 21:49:36','','','Karlskoga',NULL,2);

INSERT IGNORE INTO `emp_attribute` (employeeid,attributeid, fromtime,regtime,value) VALUES
 (1,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 26000),
 (2,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 15000),
 (2,2,'2007-01-01 00:00:00','2007-01-01 00:00:00', 150),
 (2,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 50),
 (3,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 18000),
 (4,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 22000),
 (6,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 18000),
 (6,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 100),
 (7,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 110),
 (8,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 21000),
 (8,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 85),
 (9,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 15000),
 (9,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 120),
 (10,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 90),
 (11,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 150),
 (11,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 150),
 (12,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 80),
 (13,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 105),
 (14,3,'2007-01-01 00:00:00','2007-01-01 00:00:00', 95),
 (15,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 25000),
 (16,1,'2007-01-01 00:00:00','2007-01-01 00:00:00', 35000);

INSERT IGNORE INTO `emp_payitem` (`employeeid`,`no`,`fromperiodid`,`toperiodid`,`accountid`,`value`) VALUES
 (1,1,3,NULL,4030,2),
 (7,1,5,NULL,4020,NULL);

INSERT IGNORE INTO `emp_team` (`employeeid`,`teamid`) VALUES
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

INSERT IGNORE INTO `location` (`locationid`,`name`,`streetaddress`,`city`,`zipcode`,`email`) VALUES
 (2,'Link�ping','Stigltsgatan 77','Linkping','584 46',NULL);

INSERT IGNORE INTO `product` (`productid`,`description`,`purchase_price`,`model`,`quantity`,`categoryid`) VALUES
 (1003,'HP Compaq nx7300 15.4\" WXGA, Cel M440, 1 GB,80GB,DVDRW,WLAN,VHB',3500,'HP Compaq nx7300',5,1),
 (1004,'HP Pavilion G5052 15.4\"WXGA,Cel M440,1 GB,120GB,DVDRW,WLAN,VHB',4500,'HP Pavilion G5052',-4,1),
 (1010,'Canon Digital IXUS 800 IS, 6MP, 2.5\" LCD 4x optisk zoom, optisk bildstabilisator',2500,'Canon Digital IXUS 800',-1,1),
 (1011,'Canon PowerShot S3 IS, 6.0 MP, 12x optisk zoom, vridbar 2.0\" LCD',2800,'Canon PowerShot S3',0,1);

INSERT IGNORE INTO sales_price(productid, listid, price) values
(1003, 1, 5100),
(1003, 2, 5795),
(1004, 1, 5200),
(1004, 2, 5995),
(1010, 1, 2500),
(1010, 2, 3095),
(1011, 1, 3200),
(1011, 2, 3995);

INSERT IGNORE INTO `project` (`projectid`,`description`) VALUES
 (101,'Test project'),
 (900,'Internal');

UPDATE `settings` SET `default_bankaccount` = '12345', `payroll_bankaccount` = '34567';

INSERT IGNORE INTO `supplier` (`supplierid`,`name`,`email`) VALUES
 (1,'Hewlett Packard',NULL),
 (2,'Canon',NULL);

INSERT IGNORE INTO supplier_price (supplierid, productid, price) values
(1, 1003, 3500),
(1, 1004, 4500),
(2, 1010, 2500),
(2, 1011, 2800);

INSERT IGNORE INTO `task` (`projectid`,`taskid`,`description`,`payaccountid`) VALUES
 (101,10,'Specification',NULL),
 (101,11,'Implementation',NULL),
 (101,12,'Test',NULL),
 (900,910,'Sick leave',2030);

INSERT IGNORE INTO `timedebit` (`employeeid`,`starttime`,`endtime`,`projectid`,`taskid`,`description`,`minutes`) VALUES
 (15,'2007-05-28 00:00:00','2007-05-29 00:00:00',101,11,NULL,480),
 (15,'2007-06-04 00:00:00','2007-06-05 00:00:00',900,910,NULL,480);

INSERT IGNORE INTO `timeregistration` (`id`,`time`,`type`,`employeeid`) VALUES
 (3,'2007-05-30 15:05:00',2,16),
 (4,'2007-05-30 16:05:00',1,16),
 (5,'2007-05-30 16:06:00',1,16),
 (6,'2007-05-30 16:06:00',2,16),
 (7,'2007-05-30 16:07:00',1,16),
 (8,'2007-06-02 18:13:06',1,15);

INSERT IGNORE INTO `user` (`username`,`full_name`,`password`,`employeeid`,`admin`,`language`) VALUES
 ('boj','Bo Jonsson','abc123',16,1,'sv'),
 ('frebe','Fredrik Bertilsson','abc123',15,1,'en'),
 ('guest','Guest','guest',1,NULL,'en'),
 ('mukda','Mukda','abc123',NULL,1,'th'),
 ('oui','Sakuntala Buttho','abc123',NULL,1,'th');

INSERT IGNORE INTO `user_group` (`username`,`groupid`) VALUES
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

INSERT IGNORE INTO `workshift` (`shiftid`,`starttime`,`endtime`,`recur_type`,`recur_interval`,`recur_count`) VALUES
 (5,'2007-04-02 09:00:00','2007-04-02 17:00:00',NULL,NULL,NULL),
 (6,'2007-04-03 09:00:00','2007-04-03 17:00:00',NULL,NULL,NULL),
 (7,'2007-04-04 09:00:00','2007-04-04 17:00:00',NULL,NULL,NULL),
 (8,'2007-04-05 09:00:00','2007-04-05 17:00:00',NULL,NULL,NULL),
 (9,'2007-04-06 09:00:00','2007-04-06 17:00:00',NULL,NULL,NULL);

 INSERT IGNORE INTO `daily_form` (`formid`,`description`,`teamid`,`groupid`) VALUES
 (1,'Daily report - Office',1,21),
 (2,'Daily report - Factory',2,22);
