create table dimension (
	dimid integer unsigned not null,
	name varchar(40),
	primary key (dimid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
insert into dimension (dimid, name)
values (1, 'General ledger');

CREATE TABLE account (
	dimid integer unsigned not null default '1',
	accountid int(10) unsigned NOT NULL,
	name varchar(45) NOT NULL,
	PRIMARY KEY  (dimid, accountid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table account add constraint  fk_account_dim
foreign key (dimid) references dimension (dimid);

INSERT INTO `account` (`accountid`,`name`) VALUES 
 (1,'Default Sales/Discounts'),
 (1010,'Petty cach'),
 (1030,'Cheque Account'),
 (1040,'Savings account'),
 (1050,'Payroll accounts'),
 (1100,'Accounts Receivable'),
 (1460,'Finished Goods Inventory'),
 (2100,'Accounts Payable'),
 (2150,'Goods Received Suspense'),
 (2300,'GST Payable'),
 (2310,'GST Recoverable'),
 (2340,'Payroll tax payable'),
 (4100,'Product sales'),
 (5000,'Cost of Sales'),
 (5500,'Direct Labour Costs'),
 (5700,'Inventory adjustment'),
 (7020,'Support Salaries & Wages'),
 (7030,'Support Salary & Wage Deductions'),
 (7040,'Management Salaries'),
 (7050,'Management Salary deductions'),
 (7080,'Payroll tax'),
 (7090,'Benefits'),
 (7650,'Travel Expenses');
insert into account (accountid,name) values (1420,'Raw material inventory');
insert into account (accountid,name) values (1440,'Work in progress inventory');
insert into account (accountid, name) values (6200, 'Communication');
insert into account (accountid, name) values (6300, 'Travelling expenses');
insert into account (accountid, name) values (7400, 'Office rent');
insert into account (accountid, name) values (7500, 'Office supplies');
insert into account (accountid, name) values (7600, 'Automotive expenses');
insert into account (accountid, name) values (7610, 'Communication expenses');
insert into account (accountid, name) values (4200, 'Sales exchage Gains/Losses');


CREATE TABLE `accountgroup` (
  `groupid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `accountgroup` (`groupid`,`description`) VALUES 
 (1,'Assets'),
 (2,'Liabilities'),
 (3,'Expenses'),
 (4,'Revenues');
insert into accountgroup (groupid, description) values (6, 'Bank accounts');


CREATE TABLE account_group (
	groupid int(10) unsigned NOT NULL,
	dimid integer unsigned not null default '1',
	accountid int(10) unsigned NOT NULL,
	PRIMARY KEY  (groupid,dimid,accountid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table account_group add constraint fk_account_group
FOREIGN KEY (dimid, accountid) REFERENCES account (dimid, accountid);
alter table account_group add constraint fk_group_account
FOREIGN KEY (groupid) REFERENCES accountgroup (groupid);

INSERT INTO `account_group` (`groupid`,`accountid`) VALUES 
 (1,1010),
 (1,1030),
 (1,1040),
 (1,1050),
 (1,1100),
 (1,1460),
 (1,2310),
 (2,2100),
 (2,2150),
 (2,2300),
 (3,5000),
 (3,5500),
 (3,5700),
 (3,7020),
 (3,7030),
 (3,7040),
 (3,7050),
 (4,1),
 (4,4100);
insert into account_group (accountid, groupid) values (1420, 1);
insert into accountgroup (groupid, description) values (5, 'Purchase debit');
insert into account_group (groupid, accountid) values (5, 1460);
insert into account_group (groupid, accountid) values (5, 1420);
insert into account_group (groupid, accountid) values (3, 6200);
insert into account_group (groupid, accountid) values (5, 6200);
insert into account_group (groupid, accountid) values (3, 6300);
insert into account_group (groupid, accountid) values (5, 6300);
insert into account_group (groupid, accountid) values (3, 7400);
insert into account_group (groupid, accountid) values (5, 7400);
insert into account_group (groupid, accountid) values (3, 7500);
insert into account_group (groupid, accountid) values (5, 7500);
insert into account_group (groupid, accountid) values (3, 7600);
insert into account_group (groupid, accountid) values (5, 7600);
insert into account_group (groupid, accountid) values (3, 7610);
insert into account_group (groupid, accountid) values (5, 7610);
INSERT INTO account_group (groupid,accountid) VALUES (4, 4200);
insert into account_group (groupid, dimid, accountid) values (6, 1, '1010');
insert into account_group (groupid, dimid, accountid) values (6, 1, '1030');
insert into account_group (groupid, dimid, accountid) values (6, 1, '1040');


CREATE TABLE `accountconf` (
	dimid integer unsigned not null default '1',
  `account_receivable` int(10) unsigned NOT NULL,
  `account_payable` int(10) unsigned NOT NULL,
  `vat_payable` int(10) unsigned NOT NULL,
  `vat_recoverable` int(10) unsigned NOT NULL,
  `finished_goods` int(10) unsigned NOT NULL,
  `cost_of_sales` int(10) unsigned NOT NULL,
  `goods_received_suspense` int(10) unsigned NOT NULL,
  `inventory_adjustment` int(11) unsigned default NULL,
  `default_cash` int(11) unsigned default NULL,
  `default_sales` int(11) unsigned default NULL,
  raw_material integer unsigned
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table accountconf add constraint fk_accountconf_account_receivable
FOREIGN KEY (dimid, account_receivable) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_account_payable
FOREIGN KEY (dimid, account_payable) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_account_vat_payable
FOREIGN KEY (dimid, vat_payable) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_vat_recoverable
FOREIGN KEY (dimid, vat_recoverable) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_finished_goods
 FOREIGN KEY (dimid, finished_goods) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_cost_of_sales
FOREIGN KEY (dimid, cost_of_sales) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_goods_received
FOREIGN KEY (dimid, goods_received_suspense) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_inventory_adjustment
FOREIGN KEY (dimid, inventory_adjustment) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_default_cash
FOREIGN KEY (dimid, default_cash) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_default_sales
FOREIGN KEY (dimid, default_sales) REFERENCES account (dimid, accountid);
alter table accountconf add constraint fk_accountconf_raw_material
FOREIGN KEY (dimid, raw_material) REFERENCES account (dimid, accountid);

INSERT INTO `accountconf` (
`account_receivable`,
`account_payable`,
`vat_payable`,
`vat_recoverable`,
`finished_goods`,
`cost_of_sales`,
`goods_received_suspense`,
`inventory_adjustment`,
`default_cash`,
`default_sales`, 
raw_material) 
VALUES 
 (1100,2100,2300,2310,1460,5000,2150,5700,1010,1,1420);

CREATE TABLE `advanced_percent` (
  `apid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `description` varchar(120) NOT NULL,
  PRIMARY KEY  USING BTREE (`apid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `ap_bracket` (
  `apid` int(10) unsigned NOT NULL default '0',
  `bracketid` smallint(5) unsigned NOT NULL default '0',
  `ceiling` double NOT NULL,
  `percent` smallint(5) unsigned default NULL,
  PRIMARY KEY  USING BTREE (`apid`,`bracketid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `attribute` (
  `attributeid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  type smallint,
  PRIMARY KEY  (`attributeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `attribute_description` (
  `attributeid` int(10) unsigned NOT NULL default '0',
  `language` varchar(16) NOT NULL default '',
  `description` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`attributeid`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table unittype
(
  unittype integer unsigned not null auto_increment,
  description varchar(60) not null,
  primary key (unittype)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into unittype (unittype, description) values (1, '');
insert into unittype (unittype, description) values (2, 'h');
insert into unittype (unittype, description) values (3, 'km');
insert into unittype (unittype, description) values (4, 'kg');

CREATE TABLE `vat_category` (
  `vatcatid` int(10) unsigned NOT NULL auto_increment,
  `percent` double unsigned NOT NULL,
  `description` varchar(45) NOT NULL,
  PRIMARY KEY  (`vatcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
insert into vat_category (vatcatid, percent, description)
values (1, 6,  'Default');
insert into vat_category (vatcatid, percent, description)
values (2, 0,  'No VAT');
                      
CREATE TABLE `category` (
  `categoryid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  `vatcatid` int(10) unsigned NOT NULL,
  `expense_accountid` int(10) unsigned default NULL,
  `revenue_accountid` int(10) unsigned default NULL,
  `stock` smallint(6) NOT NULL default '1',
  inventory_accountid integer unsigned,
  unittype integer unsigned,
  dimid integer unsigned not null default '1',
  PRIMARY KEY  (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table category add constraint fk_category_inventory
foreign key (dimid, inventory_accountid) references account(dimid, accountid);
alter table category add constraint fk_category_unittype foreign key (unittype)
references unittype (unittype);
alter table category add  constraint fk_category_expense
foreign key (dimid, expense_accountid) references account(dimid, accountid);
alter table category add  constraint fk_category_revenue
foreign key (dimid, revenue_accountid) references account(dimid, accountid);
alter table category add constraint fk_category_vat
foreign key (vatcatid) references vat_category (vatcatid);

INSERT INTO `category` (`categoryid`,`description`,`vatcatid`,`expense_accountid`,`revenue_accountid`,`stock`, inventory_accountid) VALUES
 (1,'Default',1,5000,4100,1, 1460);
insert into category (description, vatcatid, revenue_accountid, 	stock)
values ('Roundning', 2, 4200, 0);

CREATE TABLE `companyinfo` (
  `companyname` varchar(80) default NULL,
  `vatnumber` varchar(32) default NULL,
  `streetaddress` varchar(128) default NULL,
  `city` varchar(32) default NULL,
  `zipcode` varchar(20) default NULL,
  `email` varchar(80) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into companyinfo (companyname) values ('Demo company');

create table pricelist (
	listid integer unsigned not null auto_increment,
	description varchar(80),
	vat_included smallint not null default '0',
	primary key (listid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
insert into pricelist (listid, description, vat_included)
values (1, 'VAT/GST excluded', 0);
insert into pricelist (listid, description, vat_included)
values (2, 'VAT/GST included', 1);

CREATE TABLE `customer` (
  `customerid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `streetaddress` varchar(120) NOT NULL,
  `city` varchar(80) NOT NULL,
  `zipcode` varchar(16) NOT NULL,
  `email` varchar(80) default NULL,
  `vatnumber` varchar(32) default NULL,
  credit_account integer unsigned,
  credit_length integer unsigned default null,
  use_vat smallint not null default '1',
  discount smallint unsigned default null,
  dimid integer unsigned not null default '1',
  pricelistid integer unsigned not null default '1',
  PRIMARY KEY  (`customerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table customer add constraint fk_customer_credit_account 
foreign key (dimid, credit_account) references account (dimid, accountid);
alter table customer add constraint fk_customer_pricelist
foreign key (pricelistid) references pricelist (listid);

CREATE TABLE `team` (
  `teamid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payperiod` (
  `periodid` int(11) NOT NULL,
  `starttime` datetime default NULL,
  `endtime` datetime default NULL,
  `locked` smallint(5) unsigned default NULL,
  state_receivables smallint,
  PRIMARY KEY  (`periodid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `payperiod` (`periodid`,`starttime`,`endtime`,`locked`) VALUES 
 (1,'2007-01-01 00:00:00','2007-02-01 00:00:00',1),
 (2,'2007-02-01 00:00:00','2007-03-01 00:00:00',1),
 (3,'2007-03-01 00:00:00','2007-04-01 00:00:00',1),
 (4,'2007-04-01 00:00:00','2007-05-01 00:00:00',1),
 (5,'2007-05-01 00:00:00','2007-06-01 00:00:00',1),
 (6,'2007-06-01 00:00:00','2007-07-01 00:00:00',NULL),
 (7,'2007-07-01 00:00:00','2007-08-01 00:00:00',NULL),
 (8,'2007-08-01 00:00:00','2007-09-01 00:00:00',NULL),
 (9,'2007-09-01 00:00:00','2007-10-01 00:00:00',NULL),
 (10,'2007-10-01 00:00:00','2007-11-01 00:00:00',NULL),
 (11,'2007-11-01 00:00:00','2007-12-01 00:00:00',NULL),
 (12,'2007-12-01 00:00:00','2008-01-01 00:00:00',NULL);

create table periodcycle
(
   cycleid integer unsigned not null auto_increment,
   description varchar(80),
   primary key (cycleid)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
insert into periodcycle (cycleid, description)
values (1, 'Monthly');
insert into periodcycle (cycleid, description)
values (2, 'Bi-weekly');
create table period
(
	cycleid integer unsigned not null,
	periodid integer unsigned not null,
	starttime datetime not null,
	endtime datetime,
	state_receivables smallint,
	primary key (cycleid, periodid)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table period add constraint fk_period_cyle
foreign key (cycleid) references periodcycle (cycleid);
INSERT INTO period (cycleid,periodid,starttime,endtime) VALUES 
 (1,1,'2007-01-01 00:00:00','2007-02-01 00:00:00'),
 (1,2,'2007-02-01 00:00:00','2007-03-01 00:00:00'),
 (1,3,'2007-03-01 00:00:00','2007-04-01 00:00:00'),
 (1,4,'2007-04-01 00:00:00','2007-05-01 00:00:00'),
 (1,5,'2007-05-01 00:00:00','2007-06-01 00:00:00'),
 (1,6,'2007-06-01 00:00:00','2007-07-01 00:00:00'),
 (1,7,'2007-07-01 00:00:00','2007-08-01 00:00:00'),
 (1,8,'2007-08-01 00:00:00','2007-09-01 00:00:00'),
 (1,9,'2007-09-01 00:00:00','2007-10-01 00:00:00'),
 (1,10,'2007-10-01 00:00:00','2007-11-01 00:00:00'),
 (1,11,'2007-11-01 00:00:00','2007-12-01 00:00:00'),
 (1,12,'2007-12-01 00:00:00','2008-01-01 00:00:00');

CREATE TABLE `payaccount` (
  `accountid` int(10) unsigned NOT NULL auto_increment,
  `formula` varchar(256) default NULL,
  `calcseq` smallint(5) unsigned default NULL,
  `inputtype` smallint(5) unsigned default NULL,
  dimid integer unsigned not null default '1',
  `glaccountid` int(10) unsigned default NULL,
  PRIMARY KEY  (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE policy (
  policyid integer unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`policyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `employee` (
  `employeeid` integer unsigned NOT NULL auto_increment,
  `givenname` varchar(64) default NULL,
  `surname` varchar(64) default NULL,
  `active` smallint(5) unsigned default NULL,
  `bank_account` varchar(45) default NULL,
  `calctime` datetime default NULL,
  `street_address` varchar(80) NOT NULL,
  `zipcode` varchar(16) NOT NULL,
  `city` varchar(80) NOT NULL,
  `email` varchar(80) default NULL,
  policyid integer unsigned not null,
  PRIMARY KEY  (`employeeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table employee add foreign key (policyid) references policy (policyid);

CREATE TABLE emp_attribute (
  employeeid integer unsigned NOT NULL default '0',
  attributeid int(10) unsigned NOT NULL default '0',
  fromtime datetime NOT NULL,
  regtime datetime not null,
  value double default NULL,
  PRIMARY KEY  (employeeid, attributeid, regtime, fromtime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table emp_attribute add constraint fk_emp_attribute FOREIGN KEY (employeeid) REFERENCES employee (employeeid);
alter table emp_attribute add constraint fk_attribute_emp FOREIGN KEY (attributeid) REFERENCES attribute (attributeid);

CREATE TABLE `emp_payitem` (
  `employeeid` int(10) unsigned NOT NULL,
  `no` int(10) unsigned NOT NULL,
  `fromperiodid` int(10) unsigned NOT NULL,
  `toperiodid` int(10) unsigned default NULL,
  `accountid` int(10) unsigned NOT NULL,
  `value` double default NULL,
  PRIMARY KEY  (`employeeid`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `emp_schedule` (
  `employeeid` int(11) NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date default NULL,
  `scheduleid` int(11) NOT NULL,
  PRIMARY KEY  (`employeeid`,`valid_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `emp_team` (
  `employeeid` int(10) unsigned NOT NULL,
  `teamid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`employeeid`,`teamid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `language` (
  `language` varchar(16) NOT NULL,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `language` (`language`,`description`) VALUES 
 ('en','English'),
 ('sv','Svenska'),
 ('th','Thai');
insert into language (language, description) values ('ca', 'Canada');
 

CREATE TABLE `location` (
  `locationid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(45) NOT NULL,
  `streetaddress` varchar(120) default NULL,
  `city` varchar(45) default NULL,
  `zipcode` varchar(12) default NULL,
  `email` varchar(80) default NULL,
  PRIMARY KEY  (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into location (locationid, name)
values (1, 'Default');

CREATE TABLE `transaction` (
  `transactionid` int(10) unsigned NOT NULL auto_increment,
  `narrative` varchar(80) NOT NULL default '',
  `transtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `cancel_transid` int(10) unsigned default NULL,
  PRIMARY KEY  (`transactionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `supplier` (
  `supplierid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(80) NOT NULL default '',
  `email` varchar(80) default NULL,
  streetaddress varchar(120),
  city varchar(80),
  zipcode varchar(16),
  vatnumber varchar(32),
  credit_account integer unsigned,
  credit_length integer unsigned default null,
  dimid integer unsigned not null default '1',
  PRIMARY KEY  (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table supplier add constraint fk_supplier_credit_account 
foreign key (dimid, credit_account) references account (dimid, accountid);

CREATE TABLE `payable` (
  `payableid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(120) default NULL,
  `amount` double NOT NULL,
  `vat` double NOT NULL,
  `supplierid` int(10) unsigned NOT NULL,
  `transactionid` int(10) unsigned NOT NULL,
  duedate datetime,
  PRIMARY KEY  (`payableid`),
  KEY `fk_payable_supplier` (`supplierid`),
  KEY `fk_payable_trans` (`transactionid`),
  CONSTRAINT `payable_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`),
  CONSTRAINT `payable_ibfk_2` FOREIGN KEY (`transactionid`) REFERENCES `transaction` (`transactionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payaccount_description` (
  `accountid` int(10) unsigned NOT NULL,
  `language` varchar(16) NOT NULL,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`accountid`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table payaccount add constraint fk_payaccount_account 
foreign key (dimid, glaccountid) references account (dimid, accountid);

CREATE TABLE `payaccountgroup` (
  `groupid` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `report` smallint(5) unsigned default NULL,
  PRIMARY KEY  (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payaccount_group` (
  `groupid` int(10) unsigned NOT NULL,
  `accountid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`groupid`,`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `payaccountgroup_description` (
  `groupid` int(10) unsigned NOT NULL,
  `language` varchar(16) NOT NULL,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`groupid`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `daily_form` (
  `formid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) default NULL,
  `teamid` int(10) unsigned default NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`formid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  PRIMARY KEY  USING BTREE (`payeventid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `payment` (
  `paymentid` int(10) unsigned NOT NULL auto_increment,
  `supplierid` int(10) unsigned NOT NULL,
  `amount` double NOT NULL,
  `transactionid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`paymentid`),
  KEY `fk_payment_supplier` (`supplierid`),
  KEY `fk_payment_trans` (`transactionid`),
  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`transactionid`) REFERENCES `transaction` (`transactionid`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `payment_allocation` (
  `paymentid` int(10) unsigned NOT NULL,
  `payableid` int(10) unsigned NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY  (`paymentid`,`payableid`),
  KEY `fk_payable_allocation` (`payableid`),
  CONSTRAINT `payment_allocation_ibfk_2` FOREIGN KEY (`payableid`) REFERENCES `payable` (`payableid`),
  CONSTRAINT `payment_allocation_ibfk_1` FOREIGN KEY (`paymentid`) REFERENCES `payment` (`paymentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `permission` (
  `permissionid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  USING BTREE (`permissionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `permission` (`permissionid`,`description`) VALUES 
 (1,'Administrate users'),
 (2,'Configurate payroll'),
 (3,'Administrate employees'),
 (4,'Register payevents'),
 (5,'Self service');
insert into permission (permissionid, description) values (6, 'Sell');
insert into permission (permissionid, description) values (7, 'Purchase');
insert into permission (permissionid, description) values (8, 'Receive goods');
insert into permission (permissionid, description) values (9, 'Manage products');

CREATE TABLE `policy_accountgroup` (
  `policyid` int(10) unsigned NOT NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`policyid`,`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table policy_attribute (
	policyid integer unsigned not null,
	attributeid integer unsigned not null,
	tabid integer unsigned,
	row smallint unsigned,
	col smallint unsigned,
	primary key (policyid, attributeid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table policy_attribute add constraint fk_policy_attribute 
foreign key (policyid) references policy (policyid);
alter table policy_attribute add constraint fk_attribute_policy
foreign key (attributeid) references attribute (attributeid);

create table policy_attribute_value (
	policyid integer unsigned not null,
	attributeid integer unsigned not null,
	fromtime datetime not null,
	regtime datetime not null,
	value double default null,
	primary key (policyid, attributeid, fromtime, regtime)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table policy_attribute_value add constraint fk_policy_attribute_value
foreign key (policyid) references policy (policyid);
alter table policy_attribute_value add constraint fk_attribute_policy_value
foreign key (attributeid) references attribute (attributeid);



CREATE TABLE `policy_description` (
  `policyid` int(10) unsigned NOT NULL default '0',
  `language` varchar(16) NOT NULL default '',
  `description` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`policyid`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `policy_payitem` (
  `policyid` int(10) unsigned NOT NULL,
  `no` smallint(5) unsigned NOT NULL default '0',
  `fromperiodid` int(10) unsigned NOT NULL,
  `toperiodid` int(10) unsigned default NULL,
  `amount` double default NULL,
  `accountid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`policyid`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `product` (
  `productid` varchar(32) NOT NULL,
  `description` varchar(80) NOT NULL default '',
  `purchase_price` double default '0',
  `model` varchar(80) NOT NULL default '',
  `quantity` int(11) NOT NULL default '0',
  `categoryid` int(10) unsigned NOT NULL,
  unittype integer unsigned,
  active smallint not null default '1',
  PRIMARY KEY  (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table product add constraint fk_product_unittype foreign key (unittype)
references unittype (unittype);
INSERT INTO product (productid,description,purchase_price,model,quantity,categoryid)
VALUES (1,'',NULL,'Unspecified',0,1);
insert into product (productid, description, categoryid, active)
values (2, 'Rounding', 2, 0);

CREATE TABLE `product_supplier` (
  `productid` varchar(32) NOT NULL default '0',
  `supplierid` int(10) unsigned NOT NULL default '0',
  `price` double default NULL,
  PRIMARY KEY  (`productid`,`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table product_supplier add constraint fk_product_supplier foreign key (supplierid) references supplier(supplierid);
alter table product_supplier add constraint fk_supplier_product foreign key (productid) references product(productid);

CREATE TABLE `project` (
  `projectid` int(10) unsigned NOT NULL,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  (`projectid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `purchaseorder` (
  `orderid` int(10) unsigned NOT NULL auto_increment,
  `supplierid` int(10) unsigned NOT NULL,
  `orderdate` datetime NOT NULL,
  `cancelled` smallint(5) unsigned NOT NULL default '0',
  `payableid` int(10) unsigned default NULL,
  locationid integer unsigned not null default '1',
  PRIMARY KEY  (`orderid`),
  KEY `fk_purchaseorder_supplier` (`supplierid`),
  KEY `fk_purchaseorder_payable` (`payableid`),
  CONSTRAINT `purchaseorder_ibfk_2` FOREIGN KEY (`payableid`) REFERENCES `payable` (`payableid`),
  CONSTRAINT `purchaseorder_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `supplier` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table purchaseorder add constraint fk_purchaseorder_location
foreign key (locationid) references location (locationid);

CREATE TABLE `purchaseorder_item` (
  `orderid` int(10) unsigned NOT NULL,
  `no` smallint(5) unsigned NOT NULL,
  `productid` varchar(32) NOT NULL,
  `quantity` int(10) NOT NULL,
  `unitprice` double default NULL,
  `vat` double default NULL,
  `accountid` int(10) unsigned default NULL,
  `comment` varchar(80) default NULL,
  `received_quantity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  USING BTREE (`orderid`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table purchaseorder_item add constraint fk_purchaseorder_product foreign key (productid) references product(productid);
alter table purchaseorder_item add constraint fk_purchaseorder_item foreign key (orderid) references purchaseorder(orderid);

CREATE TABLE `receipt` (
  `receiptid` int(10) unsigned NOT NULL auto_increment,
  `customerid` int(10) unsigned NOT NULL,
  `amount` double NOT NULL,
  `transactionid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`receiptid`),
  KEY `fk_receipt_customer` (`customerid`),
  KEY `fk_receipt_trans` (`transactionid`),
  CONSTRAINT `receipt_ibfk_2` FOREIGN KEY (`transactionid`) REFERENCES `transaction` (`transactionid`),
  CONSTRAINT `receipt_ibfk_1` FOREIGN KEY (`customerid`) REFERENCES `customer` (`customerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `salesorder` (
  `orderid` int(10) unsigned NOT NULL auto_increment,
  `orderdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `customerid` int(10) unsigned NOT NULL default '0',
  `invoice_transid` int(10) unsigned default NULL,
  `cancelled` smallint(5) unsigned NOT NULL default '0',
  invoice_sent smallint,
  duedate datetime,
  credit_orgid integer unsigned default null,
  comment varchar(256),
  locationid integer unsigned not null default '1',
  PRIMARY KEY  (`orderid`),
  KEY `fk_salesorder_customer` (`customerid`),
  KEY `fk_salesorder_invoice` (`invoice_transid`),
  CONSTRAINT `salesorder_ibfk_2` FOREIGN KEY (`invoice_transid`) REFERENCES `transaction` (`transactionid`),
  CONSTRAINT `salesorder_ibfk_1` FOREIGN KEY (`customerid`) REFERENCES `customer` (`customerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table salesorder add constraint fk_salesorder_location
foreign key (locationid) references location (locationid);

create table recur_salesorder (
   orderid integer unsigned not null,
   active smallint not null default '1',
   cycleid integer unsigned not null default '1',
   primary key (orderid)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table recur_salesorder add constraint fk_recur_salesorder foreign key (orderid) 
references salesorder (orderid);
		
CREATE TABLE `receipt_allocation` (
  `receiptid` int(10) unsigned NOT NULL,
  `orderid` int(10) unsigned NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY  USING BTREE (`receiptid`,`orderid`),
  KEY `fk_order_allocation` (`orderid`),
  CONSTRAINT `receipt_allocation_ibfk_2` FOREIGN KEY (`orderid`) REFERENCES `salesorder` (`orderid`),
  CONSTRAINT `receipt_allocation_ibfk_1` FOREIGN KEY (`receiptid`) REFERENCES `receipt` (`receiptid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `salesorder_item` (
  `orderid` int(10) unsigned NOT NULL,
  `productid` varchar(32) NOT NULL,
  `quantity` int(10) NOT NULL default '0',
  `unitprice` double NOT NULL,
  `no` smallint(5) unsigned NOT NULL,
  `comment` varchar(80) default null,
  `vat` double NOT NULL,
  PRIMARY KEY  USING BTREE (`orderid`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table salesorder_item add constraint fk_salesorder_product foreign key (productid) references product(productid);
alter table salesorder_item add constraint fk_salesorder_item foreign key (orderid) references salesorder(orderid);


CREATE TABLE `schedule` (
  `scheduleid` int(11) NOT NULL auto_increment,
  `description` varchar(32) default NULL,
  `recur_type` smallint(6) default NULL,
  `recur_interval` smallint(6) default NULL,
  `recur_count` smallint(6) default NULL,
  PRIMARY KEY  (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `schedule` (`scheduleid`,`description`,`recur_type`,`recur_interval`,`recur_count`) VALUES 
 (1,'NineToFive',1,7,NULL);

CREATE TABLE `schedule_shift` (
  `scheduleid` int(11) NOT NULL,
  `shiftid` int(11) NOT NULL,
  PRIMARY KEY  (`scheduleid`,`shiftid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `settings` (
  `default_bankaccount` varchar(45) default NULL,
  `payroll_bankaccount` varchar(45) default NULL,
  credit_length integer unsigned default '30',
  rounding smallint
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `task` (
  `projectid` int(10) unsigned NOT NULL,
  `taskid` int(10) unsigned NOT NULL,
  `description` varchar(80) NOT NULL,
  `payaccountid` int(11) default NULL,
  PRIMARY KEY  (`projectid`,`taskid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE `timeregistration` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `time` datetime NOT NULL,
  `type` smallint(5) unsigned NOT NULL,
  `employeeid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE transaction_part (
	transactionid int(10) unsigned NOT NULL default '0',
	dimid integer unsigned not null default '1',
	accountid int(10) unsigned NOT NULL default '0',
	amount double NOT NULL default '0',
	PRIMARY KEY  (transactionid,dimid,accountid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table transaction_part add constraint fk_transaction_account 
foreign key (dimid, accountid) references account (dimid, accountid);


CREATE TABLE `user` (
  `username` varchar(16) NOT NULL default '',
  `full_name` varchar(64) default NULL,
  `password` varchar(32) default NULL,
  `employeeid` int(10) unsigned default NULL,
  `admin` smallint(5) unsigned default NULL,
  `language` varchar(8) default NULL,
  locationid integer unsigned,
  PRIMARY KEY  (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table user add constraint fk_user_location
foreign key (locationid) references location (locationid);

INSERT INTO `user` (`username`,`full_name`,`password`,`employeeid`,`admin`,`language`) VALUES 
 ('admin','Admin','abc123',NULL,1,'en');


CREATE TABLE `user_group` (
  `username` varchar(32) NOT NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`username`,`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user_group` (`username`,`groupid`) VALUES 
 ('admin',1),
 ('admin',2),
 ('admin',3),
 ('admin',4);
insert into user_group (username, groupid) values ('admin', 7);

CREATE TABLE `usergroup` (
  `groupid` int(10) unsigned NOT NULL auto_increment,
  `description` varchar(80) NOT NULL,
  PRIMARY KEY  USING BTREE (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `usergroup` (`groupid`,`description`) VALUES 
 (1,'User administrator'),
 (2,'Regular staff'),
 (3,'Payroll administrator'),
 (4,'Payroll configurator');
insert into usergroup (groupid, description) values (5, 'Salesman');
insert into usergroup (groupid, description) values (6, 'Purchaser');
insert into usergroup (groupid, description) values (7, 'Order/Stock admin');


CREATE TABLE `usergroup_permission` (
  `groupid` int(10) unsigned NOT NULL,
  `permissionid` int(10) unsigned NOT NULL,
  PRIMARY KEY  USING BTREE (`groupid`,`permissionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `usergroup_permission` (`groupid`,`permissionid`) VALUES 
 (1,1),
 (2,5),
 (3,3),
 (3,4),
 (4,2);
insert into usergroup_permission (groupid, permissionid) values (5, 6);
insert into usergroup_permission (groupid, permissionid) values (6, 7);
insert into usergroup_permission (groupid, permissionid) values (7, 6);
insert into usergroup_permission (groupid, permissionid) values (7, 7);
insert into usergroup_permission (groupid, permissionid) values (7, 8);
insert into usergroup_permission (groupid, permissionid) values (7, 9);

CREATE TABLE `workshift` (
  `shiftid` int(11) NOT NULL auto_increment,
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `recur_type` smallint(6) default NULL,
  `recur_interval` smallint(6) default NULL,
  `recur_count` smallint(6) default NULL,
  PRIMARY KEY  (`shiftid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table productionorder
(
  orderid integer unsigned not null auto_increment,
  createdby varchar(16),
  createdtime datetime,
  transactionid integer unsigned,
  cancelled smallint unsigned not null default 0,
  primary key (orderid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table productionorder add foreign key (createdby) references user (username);
alter table productionorder add foreign key (transactionid) references transaction (transactionid);

create table productionorder_item
(
  orderid integer unsigned not null,
  no smallint unsigned not null,
  productid varchar(32),
  quantity integer,
  primary key (orderid, no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table productionorder_item add constraint fk_productionorder_product foreign key (productid) references product(productid);
alter table productionorder_item add constraint fk_productionorder_item foreign key (orderid) references productionorder(orderid);

CREATE TABLE `stockmove` (
  `moveid` int(10) unsigned NOT NULL auto_increment,
  `productid` varchar(32) NOT NULL,
  `diff` int(11) NOT NULL,
  `narrative` varchar(80) default NULL,
  `transactionid` int(10) unsigned default NULL,
  `salesorderid` int(10) unsigned default NULL,
  `purchaseorderid` int(10) unsigned default NULL,
  `no` smallint(5) unsigned default NULL,
  createdby varchar(16) default null,
  productionorderid integer unsigned,
  locationid integer unsigned not null default '1',
  PRIMARY KEY  (`moveid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table stockmove add constraint fk_stockmove_productionorder foreign key (productionorderid) references productionorder (orderid);
alter table stockmove add constraint fk_stockmove_createdby foreign key (createdby) references user (username);
alter table stockmove add constraint fk_stockmove_productid foreign key (productid) references product (productid);
alter table stockmove add constraint fk_stockmove_transaction foreign key (transactionid) references transaction (transactionid);
alter table stockmove add constraint fk_stockmove_salesorder foreign key (salesorderid) references salesorder (orderid);
alter table stockmove add constraint fk_stockmove_purchaseorder foreign key (purchaseorderid) references purchaseorder (orderid);
alter table stockmove add constraint fk_stockmove_location foreign key (locationid) references location (locationid);

alter table ap_bracket add FOREIGN KEY (apid) REFERENCES advanced_percent (apid);
alter table attribute_description add FOREIGN KEY (attributeid) REFERENCES attribute (attributeid);

alter table emp_payitem add FOREIGN KEY (employeeid) REFERENCES employee (employeeid);     
alter table emp_payitem add FOREIGN KEY (accountid) REFERENCES payaccount (accountid);     
alter table emp_schedule modify employeeid integer unsigned;
alter table emp_schedule add FOREIGN KEY (employeeid) REFERENCES employee (employeeid);
alter table emp_team add FOREIGN KEY (employeeid) REFERENCES employee (employeeid);
alter table emp_team add FOREIGN KEY (teamid) REFERENCES team (teamid);
alter table payaccount_description add FOREIGN KEY (accountid) REFERENCES payaccount (accountid);
alter table payaccount_group add FOREIGN KEY (accountid) REFERENCES payaccount (accountid);
alter table payaccount_group add FOREIGN KEY (groupid) REFERENCES payaccountgroup (groupid);        
alter table payaccountgroup_description add FOREIGN KEY (groupid) REFERENCES payaccountgroup (groupid);
alter table daily_form add FOREIGN KEY (teamid) REFERENCES team (teamid);
alter table daily_form add FOREIGN KEY (groupid) REFERENCES payaccountgroup (groupid);
alter table payperiod modify periodid integer unsigned;
alter table payevent add FOREIGN KEY (employeeid) REFERENCES employee (employeeid);        
alter table payevent add FOREIGN KEY (periodid) REFERENCES payperiod (periodid);
alter table payevent add FOREIGN KEY (accountid) REFERENCES payaccount (accountid);
alter table payevent add FOREIGN KEY (parentid) REFERENCES payevent (payeventid);

alter table salesorder add createdby varchar(16) default null;
alter table salesorder add foreign key (createdby) references user (username);

alter table purchaseorder add createdby varchar(16) default null;
alter table purchaseorder add foreign key (createdby) references user (username);

alter table transaction add createdby varchar(16) default null;
alter table transaction add foreign key (createdby) references user (username);

alter table receipt add createdby varchar(16) default null;
alter table receipt add foreign key (createdby) references user (username);

alter table payment add createdby varchar(16) default null;
alter table payment add foreign key (createdby) references user (username);

alter table payable add createdby varchar(16) default null;
alter table payable add foreign key (createdby) references user (username);

alter table customer modify streetaddress varchar(120) default null;
alter table customer modify zipcode varchar(16) default null;
alter table customer modify city varchar(80) default null;

insert into customer (customerid, name, pricelistid) values (1, 'Cash customer', 2);

create table logger
(
loggid integer unsigned NOT NULL auto_increment,
loggtext varchar(2000),
loggtime datetime, 
username varchar(16),
level smallint not null default '1',
primary key (loggid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
alter table receipt modify transactionid integer unsigned default null;
alter table transaction add valid smallint unsigned not null default 1;

create table bom
(
parentid varchar(32) not null,
childid varchar(32) not null,
quantity integer default 1,
primary key (parentid, childid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table bom add constraint fk_bom_parent foreign key (parentid) references product (productid);
alter table bom add constraint fk_bom_child foreign key (childid) references product (productid);

create table version
(
  dbversion integer
) ENGINE=InnoDB DEFAULT CHARSET=utf8;  

create table se_taxtable
(
	year smallint unsigned not null,
	periodlength smallint unsigned not null,
	tableno smallint unsigned not null,
	floor double unsigned not null,
	ceiling double unsigned,
	type char(1) not null,
	tax1 double unsigned,
	tax2 double unsigned,
	tax3 double unsigned,
	tax4 double unsigned,
	tax5 double unsigned,
	primary key (year, periodlength, tableno, floor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table attribute_value
(
  attributeid integer unsigned not null,
  fromtime datetime not null,
  regtime datetime not null,
  value double,
  primary key (attributeid, regtime, fromtime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table attribute_value add foreign key (attributeid) references attribute (attributeid);

create table phone_category
(
  phonecatid integer unsigned not null auto_increment,
  description varchar(60) not null,
  primary key (phonecatid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into phone_category (phonecatid, description) values (1, 'Office');
insert into phone_category (phonecatid, description) values (2, 'Mobile');
insert into phone_category (phonecatid, description) values (3, 'Home');
insert into phone_category (phonecatid, description) values (4, 'Pager');
insert into phone_category (phonecatid, description) values (5, 'Fax no');

create table customer_phone
(
	customerid integer unsigned not null,
	telephoneno varchar(40) not null,
	phonecatid integer unsigned,
	primary key (customerid, telephoneno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table customer_phone add constraint fk_customer_phone foreign key (customerid)
references customer (customerid);
alter table customer_phone add constraint fk_customer_phone_cat foreign key (phonecatid)
references phone_category (phonecatid);

create table session 
(
	sessionid integer unsigned not null auto_increment,
	username varchar(32),
	remote_host varchar(80),
	logintime datetime,
	primary key (sessionid)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table session add constraint fk_session_user 
foreign key (username) references user (username);

create table emp_tab (
    tabid integer unsigned not null auto_increment,
    name varchar(32),
    no_of_cols smallint unsigned not null default '1',
    primary key (tabid)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
insert into emp_tab (tabid, name)	values (1, 'General');

create table invoice_footer (
	rowno integer unsigned not null auto_increment,
	text varchar(120),
	primary key (rowno)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table sales_price (
	productid varchar(32) not null,
	listid integer unsigned not null,
	price double,
	primary key (productid, listid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table sales_price add constraint fk_sales_price_product
foreign key (productid) references product (productid);
alter table sales_price add constraint fk_sales_price_list
foreign key (listid) references pricelist (listid);

create table supplier_price (
	supplierid integer unsigned not null,
	productid varchar(32) not null,
	price double not null,
	primary key (supplierid, productid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
alter table supplier_price add constraint fk_supplier_price_supplier
foreign key (supplierid) references supplier (supplierid);
alter table supplier_price add constraint fk_supplier_price_product
foreign key (productid) references product (productid);

insert into version (dbversion) values (35);

