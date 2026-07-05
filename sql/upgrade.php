<?php
define('DBVERSION', 64);

function upgrade()
{
	$rs = query("show tables like 'version'");
	if (num_rows($rs) == 0) {
		set_time_limit(200);
		tx("runScript", array("../sql/therp-new.sql"));
	}
	$mess = null;
	$dbversion = findValue("select dbversion from version");
	if ($dbversion < DBVERSION) {
		while ($dbversion < DBVERSION) {
			$dbversion++;
			$mess .= tx("upgradeVersion", array($dbversion));
		}
	}
	if ($mess != null)
		$_REQUEST['upgrademess'] = $mess;
}

function upgradeVersion($dbversion)
{
	call_user_func("upgrade$dbversion");
	sql("update version set dbversion=$dbversion");
	return "Upgraded to database version $dbversion<br>";
}
function upgrade64()
{
	// add barcode support
	sql("alter table product add barcode varchar(13)");
}
function upgrade63()
{
	sql("
	create table movesorder
	(
	   orderid              int(10) unsigned not null auto_increment,
	   orderdate            datetime not null default '0000-00-00 00:00:00',
	   locationid           int(10) unsigned not null default 1,
	   toid                 int(10) unsigned not null default 0,
	   invoice_transid      int(10) unsigned,
	   cancelled            smallint(5) unsigned not null default 0,
	   sent					smallint(5) unsigned not null default 0,
	   received				smallint(5) unsigned not null default 0,
	   comment              national varchar(256),
	   createdby            national varchar(16),
	   orderedby            national varchar(60),
	   no                   int(11),
	   primary key (orderid),
	   key idx_movesorder_no (no)
	) engine=InnoDB DEFAULT CHARSET=utf8
	");

	sql("
	create table movesorder_item
	(
	   orderid              int(10) unsigned not null,
	   productid            national varchar(32) not null,
	   quantity             int(10) not null default 0,
	   no                   smallint(5) unsigned not null,
	   comment              national varchar(80),
	   primary key (no, orderid)
	) engine=InnoDB DEFAULT CHARSET=utf8
	");
	// add movesorderid for the goods move order's stock history
	sql("alter table stockmove add movesorderid int(10)");
}
function upgrade62()
{
	// add chinese language 's default option
	sql("insert into language (language, description) values ('cn', 'Chinese')");
}

function upgrade61()
{
	sql("alter table travelconf add perdiem_productid varchar(32)");
	sql("
	alter table travelconf add	constraint fk_travelconf_product_perdiem
	foreign key (perdiem_productid) references product (productid)");
	sql("alter table travelconf add night_productid varchar(32)");
	sql("
	alter table travelconf add	constraint fk_travelconf_product_night
	foreign key (night_productid) references product (productid)");
	sql("alter table trip add night_allowance smallint not null default '0'");
}

function upgrade60()
{
	sql("update settings set invoice_template='standard'");
}

function upgrade59()
{
	sql("
	create table formula (
		formulaid integer not null,
		name varchar(80),
		expression varchar(2000),
		primary key (formulaid)
	) engine=InnoDB DEFAULT CHARSET=utf8
	");
}

function upgrade58()
{
	$count = findValue("select count(*) from settings", 0);
	if ($count == 0) {
		sql("insert into settings (credit_length) values (30)");
	}
}

function upgrade57()
{
	sql("
	create table travelconf (
		carcompensation_productid varchar(32),
		constraint fk_travelconf_product_car
		foreign key (carcompensation_productid) references product (productid)
	) engine=InnoDB DEFAULT CHARSET=utf8");
	sql("insert into travelconf (carcompensation_productid) values (null)");
	sql("alter table trip add transactionid integer unsigned");
	sql("
	alter table trip add constraint fk_trip_transaction
	foreign key (transactionid) references transaction (transactionid)");
}

function upgrade56()
{
	sql("alter table trip modify tripid integer not null auto_increment");
}

function upgrade55()
{
	sql("alter table salesorder add no integer default null");
	sql("create unique index idx_salesorder_no on salesorder (no)");
	sql("update salesorder set no=orderid");
}

function upgrade54()
{
	sql("alter table payaccount add description varchar(80)");
	sql("
	update payaccount g set description=
	(select description from payaccount_description d where d.accountid=g.accountid and language='en')");	
}

function upgrade53()
{
	sql("
	create table trip (
		tripid integer not null,
		employeeid integer unsigned not null,
		starttime datetime,
		endtime datetime,
		origin varchar(80),
		destination varchar(80),
		purpuse varchar(200),
		distance double,
		primary key (tripid),
		constraint fk_trip_employee
		foreign key (employeeid) references employee (employeeid)		
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("alter table policy add dimid integer default '1'");
	sql("alter table policy add glaccountid integer");
	/*sql("
	alter table policy add constraint fk_policy_account
	foreign key (dimid, glaccountid) references account (dimid, accountid)");*/
	sql("alter table payevent_account add transactionid integer unsigned");
	sql("
	alter table payevent_account add constraint fk_payevent_transaction
	foreign key (transactionid) references transaction (transactionid)");
	sql("alter table payevent_account rename payevent_debit");
	sql("alter table payaccountgroup add description varchar(80)");
	sql("
	update payaccountgroup g set description=
	(select description from payaccountgroup_description d where d.groupid=g.groupid and language='en')");
	sql("
	insert into payaccountgroup (groupid, name, description)
	values (5, 'general_ledger', 'General ledger')"); 
}

function upgrade52()
{
	sql("
	create table payevent_account (
		payeventid integer unsigned not null,
		dimid integer unsigned not null,
		glaccountid integer unsigned not null,
		share double default '1',
		primary key (payeventid, dimid, glaccountid),
		constraint fk_payevent_account 
		foreign key (payeventid) references payevent (payeventid),
		constraint fk_account_payevent
		foreign key (dimid, glaccountid) references account (dimid, accountid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("alter table salesorder add orderedby varchar(60)");
	sql("alter table customer add contactname varchar(80)");
	sql("
	create table invoice_template (
		name varchar(20),
		description varchar(80),
		primary key (name)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	insert into invoice_template (name, description) values 
	('standard', 'Standard'),
	('se', 'Svensk')");
	sql("
	update salesorder_item si set vat=
	(select percent 
	 from vat_category vc
	 join category c on c.vatcatid=vc.vatcatid
	 join product p on p.categoryid=c.categoryid
	 where productid=si.productid) 
	");
}

function upgrade51()
{
	sql("
	create table rangeset (
		rangesetid integer not null,
		name varchar(20),
		description varchar(80),
		primary key (rangesetid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	create table range (
		rangesetid integer not null,
		rangeid integer not null,
		ceiling double,
		value double,
		primary key (rangesetid, rangeid),
		constraint fk_range_rangeset 
		foreign key (rangesetid) references rangeset (rangesetid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
}

function upgrade50()
{
	sql("alter table companyinfo add telephoneno varchar(40)");
	sql("alter table employee add birthdate date");
	sql("alter table attribute add object smallint");
	sql("update attribute set object=1");
	sql("alter table attribute add description varchar(80)");
	sql("
	update attribute a set description=
	(select description from attribute_description ad 
	 where ad.attributeid=a.attributeid and language='sv') 
	");
	sql("
	alter table product_attribute_option_value
	drop foreign key fk_product_attribute_option_value");
	$rs = query("select attributeid, name from product_attribute");
	$type = 3;
	$object = 2;
	while ($row = fetch($rs)) {
		sql("
		insert into attribute (name, description, type, object)
		values ('$row->name', '$row->name', $type, $object)");
		$attrid = insert_id();
		sql("
		insert into attribute_option (attributeid, optionid, description)
		select $attrid,
			optionid,
			description
		from product_attribute_option
		where attributeid=$row->attributeid");
		sql("
		update product_attribute_option_value
		set attributeid=$attrid+100000
		where attributeid=$row->attributeid");		
	}
	sql("
	update product_attribute_option_value
	set attributeid=attributeid-100000");
	sql("
	alter table product_attribute_option_value 
	add constraint fk_product_attribute_option_value
	foreign key (attributeid, optionid) references attribute_option (attributeid, optionid)");
	sql("
	create table company_attribute (
		name varchar(40) not null,
		value varchar(80),
		primary key (name)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("alter table settings add invoice_template varchar(16)");
}

function upgrade49()
{
	sql("alter table companyinfo add registrationno varchar(20)");
}

function upgrade48()
{
	sql("alter table product add oscommerceid integer default null");
	sql("alter table salesorder add oscommerceid integer default null");	
	sql("insert into user (username, full_name) values ('oscommerce', 'osCommerce')");
}

function upgrade47()
{
	sql("alter table timedebit add orderid integer unsigned default null");
	sql("
	alter table timedebit add constraint fk_timedebit_salesorder
	foreign key (orderid) references salesorder (orderid)");
	sql("alter table project add customerid integer unsigned default null");
	sql("
	alter table project add constraint fk_project_customer
	foreign key (customerid) references customer (customerid)");
}

function upgrade46()
{
	sql("alter table task add productid varchar(32) default null");
	sql("
	alter table task add constraint fk_task_product
	foreign key (productid) references product (productid)");
}

function upgrade45()
{
	sql("
	create table attribute_option (
		attributeid integer unsigned not null,
		optionid integer unsigned not null,
		description varchar(80),
		primary key (attributeid, optionid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
}


function upgrade44()
{
	sql("alter table transaction add createdtime datetime");
	sql("update transaction set createdtime=transtime");	
}


function upgrade43()
{
	sql("
	create table supplier_phone
	(
		supplierid integer unsigned not null,
		telephoneno varchar(40) not null,
		phonecatid integer unsigned,
		primary key (supplierid, telephoneno),
		foreign key (phonecatid) references phone_category (phonecatid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	create table country
	(
		countrycode varchar(2),
		name varchar(80),
		primary key (countrycode)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("alter table supplier add countrycode varchar(2)");
	sql("
	alter table supplier add constraint fk_supplier_country
	foreign key (countrycode) references country (countrycode)");
	sql("alter table supplier add contact varchar(80)");
	sql("alter table supplier_price add supplier_productcode varchar(80)");
	sql("alter table supplier_price modify price decimal(10,2) default null");
	sql("alter table product add reorder_level integer");
	sql("alter table product add reorder_qty integer");		
	sql("
insert into country(countrycode, name) values 
('AE', 'United Arab Emirates'),
('BH', 'Bahrain'),
('DZ', 'Algeria'),
('EG', 'Egypt'),
('IQ', 'Iraq'),
('JO', 'Jordan'),
('KW', 'Kuwait'),
('LB', 'Lebanon'),
('LY', 'Libya'),
('MA', 'Morocco'),
('OM', 'Oman'),
('QA', 'Qatar'),
('SA', 'Saudi Arabia'),
('SD', 'Sudan'),
('SY', 'Syria'),
('TN', 'Tunisia'),
('YE', 'Yemen'),
('IN', 'India'),
('IL', 'Israel'),
('JP', 'Japan'),
('KR', 'South Korea'),
('TH', 'Thailand'),
('VN', 'Vietnam'),
('CN', 'China'),
('HK', 'Hong Kong'),
('TW', 'Taiwan'),
('BY', 'Belarus'),
('BG', 'Bulgaria'),
('ES', 'Spain'),
('CZ', 'Czech Republic'),
('DK', 'Denmark'),
('AT', 'Austria'),
('CH', 'Switzerland'),
('DE', 'Germany'),
('LU', 'Luxembourg'),
('GR', 'Greece'),
('AU', 'Australia'),
('CA', 'Canada'),
('GB', 'United Kingdom'),
('IE', 'Ireland'),
('NZ', 'New Zealand'),
('ZA', 'South Africa'),
('AR', 'Argentina'),
('BO', 'Bolivia'),
('CL', 'Chile'),
('CO', 'Colombia'),
('CR', 'Costa Rica'),
('DO', 'Dominican Republic'),
('EC', 'Ecuador'),
('GT', 'Guatemala'),
('HN', 'Honduras'),
('MX', 'Mexico'),
('NI', 'Nicaragua'),
('PA', 'Panama'),
('PE', 'Peru'),
('PR', 'Puerto Rico'),
('PY', 'Paraguay'),
('SV', 'El Salvador'),
('UY', 'Uruguay'),
('VE', 'Venezuela'),
('EE', 'Estonia'),
('FI', 'Finland'),
('BE', 'Belgium'),
('FR', 'France'),
('HR', 'Croatia'),
('HU', 'Hungary'),
('IS', 'Iceland'),
('IT', 'Italy'),
('LT', 'Lithuania'),
('LV', 'Latvia'),
('MK', 'Macedonia'),
('NL', 'Netherlands'),
('NO', 'Norway'),
('PL', 'Poland'),
('BR', 'Brazil'),
('PT', 'Portugal'),
('RO', 'Romania'),
('RU', 'Russia'),
('SK', 'Slovakia'),
('SI', 'Slovenia'),
('AL', 'Albania'),
('BA', 'Bosnia and Herzegovina'),
('CS', 'Serbia and Montenegro'),
('SE', 'Sweden'),
('TR', 'Turkey'),
('UA', 'Ukraine'),
('US', 'United States')
");	
}

function upgrade42()
{
	sql("delete from tmp_transaction_part");
	sql("delete from tmp_transaction");
	sql("alter table tmp_transaction_part drop foreign key fk_tmp_transaction_part");
	sql("
	alter table tmp_transaction_part add constraint fk_tmp_transaction_part
	foreign key (transactionid) references tmp_transaction (transactionid)");
}

function upgrade41()
{
	sql("alter table transaction_part modify amount decimal(10,2) not null default '0'");
}

function upgrade40()
{
	sql("
	create table product_attribute (
		attributeid integer unsigned not null auto_increment,
		name varchar(32),
		primary key (attributeid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	create table product_attribute_option (
		attributeid integer unsigned not null,
		optionid integer unsigned not null,
		description varchar(80),
		primary key (attributeid, optionid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	alter table product_attribute_option add constraint fk_product_attribute_option
	foreign key (attributeid) references product_attribute (attributeid)");
	sql("
	create table product_attribute_option_value (
		productid varchar(32) not null,
		attributeid integer unsigned not null,
		optionid integer unsigned not null,
		primary key (productid, attributeid, optionid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	alter table product_attribute_option_value add constraint fk_product_attribute_product
	foreign key (productid) references product (productid)");
	sql("
	alter table product_attribute_option_value add constraint fk_product_attribute_option_value
	foreign key (attributeid, optionid) references product_attribute_option (attributeid, optionid)");
}

function upgrade39()
{
	sql("
	CREATE TABLE tmp_transaction (
	  `transactionid` int(10) unsigned NOT NULL auto_increment,
	  `narrative` varchar(80) NOT NULL default '',
	  `transtime` datetime NOT NULL default '0000-00-00 00:00:00',
	  createdby varchar(16) default null,
	  locked smallint not null default '0',
	  PRIMARY KEY  (`transactionid`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	CREATE TABLE tmp_transaction_part (
		transactionid int(10) unsigned NOT NULL default '0',
		dimid integer unsigned not null default '1',
		accountid int(10) unsigned NOT NULL default '0',
		amount double NOT NULL default '0',
		PRIMARY KEY  (transactionid,dimid,accountid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	alter table tmp_transaction_part add constraint fk_tmp_transaction_account
	foreign key (dimid, accountid) references account (dimid, accountid)");
	sql("
	alter table transaction_part add constraint fk_transaction_part
	foreign key (transactionid) references transaction (transactionid)");
	sql("
	alter table tmp_transaction_part add constraint fk_tmp_transaction_part
	foreign key (transactionid) references transaction (transactionid)");
}



function upgrade2()
{
	sql("alter table employee add policyid integer unsigned not null");
	$periodid = findValue("select periodid
  	                       from payperiod where isnull(locked) or locked=0
	                       order by payperiod.starttime limit 1");
	$rs = query("select employeeid from employee");
	while ($row = fetch($rs)) {
		$policyid = findValue("select
  							   p.policyid
							   from employee_policy ep
							   join policy p on p.policyid=ep.policyid
							   where employeeid=$row->employeeid and fromperiodid<=$periodid
							   order by fromperiodid desc
							   limit 1");
		sql("update employee set policyid=$policyid where employeeid=$row->employeeid");
	}
	sql("alter table employee add foreign key (policyid) references policy (policyid)");
	sql("drop table employee_policy");
}

function upgrade3()
{
	sql("create table se_taxtable
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
		 ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
}

function upgrade4()
{
	sql("
	create table attribute_value
	(
	  attributeid integer unsigned not null,
	  fromperiodid integer unsigned not null,
	  value double
	) ENGINE=InnoDB DEFAULT CHARSET=utf8
	");
	sql("alter table attribute_value add foreign key (attributeid) references attribute (attributeid)");
	sql("alter table attribute_value add foreign key (fromperiodid) references payperiod (periodid)");
}

function upgrade5()
{
	sql("alter table category add inventory_accountid integer unsigned");
	sql("alter table category add foreign key (inventory_accountid) references account(accountid)");
	sql("update category set inventory_accountid=1460 where categoryid=1");
}

function upgrade6()
{
	runScript("../sql/upgrade6.sql");
}

function upgrade7()
{
	sql("alter table salesorder add invoice_sent smallint");
	sql("create table recur_salesorder (
	       orderid integer unsigned not null,
		   active smallint not null default '1',
		   primary key (orderid)
		)");
	sql("alter table payperiod add state_receivables smallint");
	sql("alter table recur_salesorder add constraint fk_recur_salesorder foreign key (orderid) references salesorder (orderid)");
}

function upgrade8()
{
	sql("alter table supplier add streetaddress varchar(120)");
	sql("alter table supplier add city varchar(80)");
	sql("alter table supplier add zipcode varchar(16)");
	sql("alter table supplier add vatnumber varchar(32)");
	sql("alter table supplier add credit_account integer unsigned");
	sql("alter table supplier add constraint fk_supplier_credit_account foreign key (credit_account) references account (accountid)");
	sql("alter table supplier add credit_length integer unsigned default null");
	sql("alter table settings add credit_length integer unsigned default '30'");
	sql("alter table payable add duedate datetime");
}

function upgrade9()
{
	sql("alter table customer add credit_account integer unsigned");
	sql("alter table customer add constraint fk_customer_credit_account foreign key (credit_account) references account (accountid)");
	sql("alter table customer add credit_length integer unsigned default null");
	sql("alter table salesorder add duedate datetime");
	sql("insert into account (accountid, name) values (6200, 'Communication')");
	sql("insert into account (accountid, name) values (6300, 'Travelling expenses')");
	sql("insert into account (accountid, name) values (7400, 'Office rent')");
	sql("insert into account (accountid, name) values (7500, 'Office supplies')");
	sql("insert into account (accountid, name) values (7600, 'Automotive expenses')");
	sql("insert into account (accountid, name) values (7610, 'Communication expenses')");
	sql("insert into accountgroup (groupid, description) values (5, 'Purchase debit')");
	sql("insert into account_group (groupid, accountid) values (5, 1460)");
	sql("insert into account_group (groupid, accountid) values (5, 1420)");
	sql("insert into account_group (groupid, accountid) values (3, 6200)");
	sql("insert into account_group (groupid, accountid) values (5, 6200)");
	sql("insert into account_group (groupid, accountid) values (3, 6300)");
	sql("insert into account_group (groupid, accountid) values (5, 6300)");
	sql("insert into account_group (groupid, accountid) values (3, 7400)");
	sql("insert into account_group (groupid, accountid) values (5, 7400)");
	sql("insert into account_group (groupid, accountid) values (3, 7500)");
	sql("insert into account_group (groupid, accountid) values (5, 7500)");
	sql("insert into account_group (groupid, accountid) values (3, 7600)");
	sql("insert into account_group (groupid, accountid) values (5, 7600)");
	sql("insert into account_group (groupid, accountid) values (3, 7610)");
	sql("insert into account_group (groupid, accountid) values (5, 7610)");
}

function upgrade10()
{
	sql("alter table salesorder_item modify quantity integer default '0'");
	sql("alter table purchaseorder_item modify quantity integer default '0'");
}

function upgrade11()
{
	sql("alter table customer add use_vat smallint not null default '1'");
}

function upgrade12()
{
	sql("CREATE TABLE emp_attribute2 (
		 employeeid integer unsigned NOT NULL,
         attributeid integer unsigned NOT NULL,
         fromtime datetime NOT NULL,
         regtime datetime not null,
         value double default NULL,
         PRIMARY KEY  (employeeid, attributeid, regtime, fromtime)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

	sql("insert into emp_attribute2 (employeeid, attributeid, fromtime, regtime, value)
	     select employeeid,
			attributeid,
		    p1.starttime,
			now(),
			value
		from emp_attribute ea
		join payperiod p1 on p1.periodid=ea.fromperiodid");

    sql("drop table emp_attribute");
	sql("alter table emp_attribute2 rename emp_attribute");
	sql("alter table emp_attribute add constraint fk_emp_attribute FOREIGN KEY (employeeid)
	     REFERENCES employee (employeeid)");
	sql("alter table emp_attribute add constraint fk_attribute_emp FOREIGN KEY (attributeid)
 	     REFERENCES attribute (attributeid)");
}

function upgrade13()
{
	sql("
	create table attribute_value2
	(
	  attributeid integer unsigned not null,
	  fromtime datetime not null,
	  regtime datetime not null,
	  value double,
	  primary key (attributeid, regtime, fromtime)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8
	");
	sql("insert into attribute_value2 (attributeid, fromtime, regtime, value)
	     select attributeid,
		   starttime,
		   now(),
		   value
		 from attribute_value av
		 join payperiod p on av.fromperiodid=p.periodid");
	sql("drop table attribute_value");
	sql("alter table attribute_value2 rename attribute_value");
	sql("alter table attribute_value add foreign key (attributeid)
	     references  attribute(attributeid)");
}

function upgrade14()
{
	sql("
	create table phone_category
	(
	  phonecatid integer unsigned not null auto_increment,
	  description varchar(60) not null,
	  primary key (phonecatid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("insert into phone_category (phonecatid, description) values (1, 'Office phoneno')");
	sql("insert into phone_category (phonecatid, description) values (2, 'Mobileno')");
	sql("insert into phone_category (phonecatid, description) values (3, 'Home phoneno')");
	sql("insert into phone_category (phonecatid, description) values (4, 'Pager')");
	sql("insert into phone_category (phonecatid, description) values (5, 'Fax no')");
	sql("
	create table customer_phone
	(
		customerid integer unsigned not null,
		telephoneno varchar(40) not null,
		phonecatid integer unsigned,
		primary key (customerid, telephoneno)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("alter table customer_phone add constraint fk_customer_phone foreign key (customerid)
	     references customer (customerid)");
	sql("alter table customer_phone add constraint fk_customer_phone_cat foreign key (phonecatid)
	     references phone_category (phonecatid)");
	sql("
	create table unittype
	(
	  unittype integer unsigned not null auto_increment,
	  description varchar(60) not null,
	  primary key (unittype)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("insert into unittype (unittype, description) values (1, '')");
	sql("insert into unittype (unittype, description) values (2, 'h')");
	sql("insert into unittype (unittype, description) values (3, 'km')");
	sql("insert into unittype (unittype, description) values (4, 'kg')");
	sql("alter table category add unittype integer unsigned");
	sql("alter table category add constraint fk_category_unittype foreign key (unittype)
	     references unittype (unittype)");
	sql("alter table product add unittype integer unsigned");
	sql("alter table product add constraint fk_product_unittype foreign key (unittype)
	     references unittype (unittype)");
}

function upgrade15()
{
	sql("alter table salesorder add credit_orgid integer unsigned default null");
	sql("alter table salesorder_item modify comment varchar(80) default null");
}

function upgrade16()
{
	sql("
	create table periodcycle
	(
	   cycleid integer unsigned not null auto_increment,
	   description varchar(80),
	   primary key (cycleid)
	)  ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("insert into periodcycle (cycleid, description)
	     values (1, 'Monthly')");
	sql("insert into periodcycle (cycleid, description)
	     values (2, 'Bi-weekly')");
	sql("
	create table period
	(
		cycleid integer unsigned not null,
		periodid integer unsigned not null,
		starttime datetime not null,
		endtime datetime,
		state_receivables smallint,
		primary key (cycleid, periodid)
	)  ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("alter table period add constraint fk_period_cyle
	     foreign key (cycleid) references periodcycle (cycleid)");
	sql("
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
	 (1,12,'2007-12-01 00:00:00','2008-01-01 00:00:00')
	");
	sql("alter table recur_salesorder add cycleid integer unsigned not null default '1'");

}

function upgrade17()
{
	sql("
	create table session
	(
		sessionid integer unsigned not null auto_increment,
		username varchar(32),
		remote_host varchar(80),
		logintime datetime,
		primary key (sessionid)
	)  ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("alter table session add constraint fk_session_user foreign key (username) references user (username)");
}

function upgrade18()
{
	sql("alter table customer add discount smallint unsigned default null");
	sql("insert into vat_category (percent, description)
	     values (0, 'No VAT')");
	$vatcatid = insert_id();
	sql("insert into account (accountid, name)
	     values (4200, 'Sales exchage Gains/Losses')");
	sql("INSERT INTO account_group (groupid,accountid)
	     VALUES (4, 4200)");
	sql("insert into category (description, vatcatid, revenue_accountid, 	stock)
	     values ('Roundning', $vatcatid, 4200, 0)");
	$categoryid = insert_id();
	sql("insert into product (productid, description, categoryid)
	     values (2, 'Rounding', $categoryid)");
}

function upgrade19()
{
	sql("create table emp_tab (
	           tabid integer unsigned not null auto_increment,
	           name varchar(32),
	           no_of_cols smallint unsigned not null default '1',
	           primary key (tabid)
	       )  ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	insert into emp_tab (tabid, name)
	values (1, 'General')");
	sql("
	create table policy_attribute_value (
		policyid integer unsigned not null,
		attributeid integer unsigned not null,
		fromtime datetime not null,
		regtime datetime not null,
		value double default null,
		primary key (policyid, attributeid, fromtime, regtime)
	)  ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	alter table policy_attribute_value add constraint fk_policy_attribute_value
	foreign key (policyid) references policy (policyid)");
	sql("
	alter table policy_attribute_value add constraint fk_attribute_policy_value
	foreign key (attributeid) references attribute (attributeid)");
	sql("
	insert into policy_attribute_value (policyid, attributeid, fromtime, regtime, value)
    select
		policyid,
       attributeid,
	   starttime,
	   now(),
	   value
	from policy_attribute pa
	join payperiod p on pa.fromperiodid=p.periodid");
	sql("drop table policy_attribute");
	sql("
	create table policy_attribute (
		policyid integer unsigned not null,
		attributeid integer unsigned not null,
		tabid integer unsigned,
		row smallint unsigned,
		col smallint unsigned,
		primary key (policyid, attributeid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	alter table policy_attribute add constraint fk_policy_attribute
	foreign key (policyid) references policy (policyid)");
	sql("
	alter table policy_attribute add constraint fk_attribute_policy
	foreign key (attributeid) references attribute (attributeid)");
	sql("
	insert into policy_attribute (policyid, attributeid)
    select distinct policyid, attributeid
	from policy_attribute_value
	");
}

function upgrade20()
{
	include("../sql/upgrade20.php");
}

function upgrade21()
{
	sql("alter table attribute add type smallint");
}

function upgrade22()
{
	sql("alter table settings add rounding smallint");
	sql("insert into permission (permissionid, description) values (6, 'Sell')");
	sql("insert into permission (permissionid, description) values (7, 'Purchase')");
	sql("insert into permission (permissionid, description) values (8, 'Receive goods')");
	sql("insert into permission (permissionid, description) values (9, 'Manage products')");
	sql("insert into usergroup (groupid, description) values (5, 'Salesman')");
	sql("insert into usergroup (groupid, description) values (6, 'Purchaser')");
	sql("insert into usergroup (groupid, description) values (7, 'Order/Stock admin')");
	sql("insert into usergroup_permission (groupid, permissionid) values (5, 6)");
	sql("insert into usergroup_permission (groupid, permissionid) values (6, 7)");
	sql("insert into usergroup_permission (groupid, permissionid) values (7, 6)");
	sql("insert into usergroup_permission (groupid, permissionid) values (7, 7)");
	sql("insert into usergroup_permission (groupid, permissionid) values (7, 8)");
	sql("insert into usergroup_permission (groupid, permissionid) values (7, 9)");
	sql("insert into user_group (username, groupid) values ('admin', 7)");
}

function upgrade23()
{
	sql("
	insert into accountgroup (groupid, description)
	values (6, 'Bank accounts')");
	sql("insert into account_group (groupid, dimid, accountid) values (6, 1, '1010')");
	sql("insert into account_group (groupid, dimid, accountid) values (6, 1, '1030')");
	sql("insert into account_group (groupid, dimid, accountid) values (6, 1, '1040')");
}

function upgrade24()
{
	sql("update phone_category set description='Office' where phonecatid=1");
	sql("update phone_category set description='Mobile' where phonecatid=2");
	sql("update phone_category set description='Home' where phonecatid=3");
	sql("update phone_category set description='Fax' where phonecatid=5");
}

function upgrade25()
{
}

function upgrade26()
{
	sql("insert into language (language, description) values ('ca', 'Canada')");
}

function upgrade27()
{
	sql("
	create table invoice_footer (
		rowno integer unsigned not null auto_increment,
        text varchar(120),
        primary key (rowno)
    )  ENGINE=InnoDB DEFAULT CHARSET=utf8");
}

function upgrade28()
{
	sql("alter table product add active smallint not null default '1'");
	sql("update product set active=0 where productid=2");
}

function upgrade29()
{
	sql("alter table vat_category modify percent double not null");
}

function upgrade30()
{
	sql("alter table salesorder add comment varchar(256)");
}

function upgrade31()
{
	sql("alter table logger add level smallint not null default '1'");
}

function upgrade32()
{
	sql("
	create table pricelist (
		listid integer unsigned not null auto_increment,
		description varchar(80),
		vat_included smallint not null default '0',
		primary key (listid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	insert into pricelist (listid, description, vat_included)
	values (1, 'VAT/GST excluded', 0)");
	sql("
	insert into pricelist (listid, description, vat_included)
	values (2, 'VAT/GST included', 1)");
	sql("
	create table sales_price (
		productid varchar(32) not null,
		listid integer unsigned not null,
		price double,
		primary key (productid, listid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	alter table sales_price add constraint fk_sales_price_product
	foreign key (productid) references product (productid)");
	sql("
	alter table sales_price add constraint fk_sales_price_list
	foreign key (listid) references pricelist (listid)");
	sql("
	insert into sales_price (productid, listid, price)
	select productid, 1, sales_price
	from product");
	sql("
	insert into sales_price (productid, listid, price)
	select p.productid, 2, price * (1+percent/100)
	from sales_price sp
    join product p on p.productid=sp.productid
	join category c on c.categoryid=p.categoryid
	join vat_category vc on vc.vatcatid=c.vatcatid
	where sp.listid=1
	");
	sql("alter table product drop sales_price");
	sql("alter table customer add pricelistid integer unsigned not null default '1'");
	sql("
	alter table customer add constraint fk_customer_pricelist
	foreign key (pricelistid) references pricelist (listid)");
	sql("update customer set pricelistid=2 where customerid=1");
}

function upgrade33()
{
	sql("delete from location");
	sql("alter table location modify streetaddress varchar(120) default null");
	sql("alter table location modify city varchar(45) default null");
	sql("alter table location modify zipcode varchar(12) default null");
	sql("
	insert into location (locationid, name, streetaddress, city, zipcode, email)
	select 1, 'Default', streetaddress, city, zipcode, email
	from companyinfo");
	sql("alter table purchaseorder add locationid integer unsigned not null default '1'");
	sql("
	alter table purchaseorder add constraint fk_purchaseorder_location
	foreign key (locationid) references location (locationid)");
	sql("alter table salesorder add locationid integer unsigned not null default '1'");
	sql("
	alter table salesorder add constraint fk_salesorder_location
	foreign key (locationid) references location (locationid)");
	sql("alter table stockmove add locationid integer unsigned not null default '1'");
	sql("
	alter table stockmove add constraint fk_stockmove_location
	foreign key (locationid) references location (locationid)");
}

function upgrade34()
{
	$rec = find("select * from vat_category where vatcatid=1");
	if ($rec == null) {
		sql("
		insert into vat_category (vatcatid, percent, description)
		values (1, 6,  'No VAT')");
	}
	sql("
	alter table category add constraint fk_category_vat
	foreign key (vatcatid) references vat_category (vatcatid)");
}

function upgrade35()
{
	sql("alter table user add locationid integer unsigned");
	sql("
	alter table user add constraint fk_user_location
	foreign key (locationid) references location (locationid)");
	sql("
	create table supplier_price (
		supplierid integer unsigned not null,
		productid varchar(32) not null,
		price double not null,
		primary key (supplierid, productid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	alter table supplier_price add constraint fk_supplier_price_supplier
	foreign key (supplierid) references supplier (supplierid)");
	sql("
	alter table supplier_price add constraint fk_supplier_price_product
	foreign key (productid) references product (productid)");
}

function upgrade36()
{
	sql("
	insert into account (dimid, accountid, name)
	values (1, 1103, 'Consigment receivable')");
	sql("insert into account_group (dimid, accountid, groupid) values (1, 1103, 1)");
	sql("
	insert into account (dimid, accountid, name)
	values (1, 1463, 'Consignment inventory')");
	sql("insert into account_group (dimid, accountid, groupid) values (1, 1463, 1)");
	sql("
	insert into account (dimid, accountid, name)
	values (1, 2103, 'Consignment payable')");
	sql("insert into account_group (dimid, accountid, groupid) values (1, 2103, 2)");
	sql("alter table category add consignment smallint not null default '0'");
	sql("alter table accountconf add consignment_receivable integer unsigned");
	sql("
	alter table accountconf add constraint fk_accountconf_consignment_receivable
	foreign key (dimid, consignment_receivable) references account (dimid, accountid)");
	sql("alter table accountconf add consignment_inventory integer unsigned");
	sql("
	alter table accountconf add constraint fk_accountconf_consignment_inventory
	foreign key (dimid, consignment_inventory) references account (dimid, accountid)");
	sql("alter table accountconf add consignment_payable integer unsigned");
	sql("
	alter table accountconf add constraint fk_accountconf_consignment_payable
	foreign key (dimid, consignment_payable) references account (dimid, accountid)");
	sql("
	update accountconf set
		consignment_receivable=1103,
		consignment_inventory=1463,
		consignment_payable=2103");
}

function upgrade37()
{
	sql("alter table account modify name varchar(200)");
}

function upgrade38()
{
	sql("insert into accountgroup (groupid, description) values (9999, 'Tmp')");
	sql("update accountgroup set description='Revenue' where groupid=3");
	sql("update accountgroup set description='Expenses' where groupid=4");
	sql("update account_group set groupid=9999 where groupid=3");
	sql("update account_group set groupid=3 where groupid=4");
	sql("update account_group set groupid=4 where groupid=9999");
	sql("delete from accountgroup where groupid=9999");
	sql("insert into accountgroup (groupid, description) values (7, 'Favorites')");
}

?>
