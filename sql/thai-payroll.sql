INSERT INTO `advanced_percent` (`apid`,`name`,`description`) VALUES 
 (1,'th_tax_ap','');

INSERT INTO `ap_bracket` (`apid`,`bracketid`,`ceiling`,`percent`) VALUES 
 (1,1,100000,0),
 (1,2,500000,10),
 (1,3,1000000,20),
 (1,4,4000000,30),
 (1,5,999999999,37);

 INSERT INTO `attribute` (`attributeid`,`name`, description, object) VALUES 
 (1,'salary','Salary', 1),
 (2,'sickdays_per_year', 'Sick leave days per year', 1),
 (3,'hourrate', 'Hour rate', 1),
 (4,'hours_per_day', 'Hours per day', 1),
 (5,'late_arrival_penalty', 'Late arrival penalty', 1);

INSERT INTO `payaccount` (`accountid`,`formula`,`calcseq`,`inputtype`,`glaccountid`, description) VALUES 
 (1010,'attribute(salary)',1010,0,7040, 'Monthly salary'),
 (1020,'attribute(hourrate)',1020,1,NULL,'Hourly pay'),
 (1030,'attribute(hourrate) * attribute(hours_per_day)',1030,2,NULL,'Daily pay'),
 (2010,'(-1) * attribute(salary) / 160',2010,1,7040, 'Hour absence'),
 (2020,'(-1) * attribute(salary)/21',2020,2,7040, 'Day absence'),
 (2030,'sick_leave(2031, 2032)',2030,2,7040, 'Sick leave'),
 (2031,'0',2031,2,NULL, 'Paid sick leave'),
 (2032,'(-1) * attribute(salary) / 21',2032,2,7040,'Unpaid sick leave'),
 (2033,'yearAccountQuantitySum(2031)',2033,2,NULL, 'Numer of paid sick days'),
 (2040,'(-1) * attribute(late_arrival_penalty)',2040,3,NULL,'Late arrival'),
 (3010,'',3010,0,NULL,'Travel expenses'),
 (3020,'',3020,0,NULL,'Health care'),
 (4010,'',4010,0,NULL,'Taxpayer allowance'),
 (4020,'-30000',4020,0,NULL,'Spouse allowance'),
 (4030,'-1250',4030,3,NULL,'Child allowance'),
 (4040,'',4040,3,NULL,'Child education allowance'),
 (4050,'',4050,0,NULL,'Old age allowance'),
 (4060,'',4060,0,NULL,'Dependent parent allowance'),
 (4070,'',4070,0,NULL,'Life insurance deduction'),
 (4080,'',4080,0,NULL,'Mortgage interest'),
 (4090,'',4090,0,NULL,'Charitable donations'),
 (4100,'',4100,0,NULL,'Contributions to political parties'),
 (5010,'(-1) * periodSum(earnings) * 0.05',5010,0,NULL,'Social security fund - employeer'),
 (5020,'(-1) * periodSum(earnings) * 0.05',5020,0,NULL,'Social security fund - employee'),
 (9008,'periodSum(taxable)',9008,0,NULL,'Taxable sum');
INSERT INTO `payaccount` (`accountid`,`formula`,`calcseq`,`inputtype`,`glaccountid`, description) VALUES 
 (9009,'advanced_percent(th_tax_ap, 12*periodSum(taxable))',9009,0,NULL,'Effective tax percent'),
 (9010,'(-1) * advanced_percent(th_tax_ap, 12*periodSum(taxable)) * periodSum(taxable)',9010,0,NULL, 'Tax');

 INSERT INTO `payaccountgroup` (`groupid`,`name`,`report`, description) VALUES 
 (1,'payable',1, 'Payable'),
 (2,'taxable',NULL, 'Taxable'),
 (3,'tax',1, 'Skatt'),
 (4,'earnings',NULL, 'Earnings'),
 (11,'social_security_fund',1, 'Social security fund'),
 (21,'attendence',NULL, 'Attendence'),
 (22,'attendence_hourly',NULL, 'Attendence (hourly)'),
 (30,'expenses',NULL, 'Expenses');

INSERT INTO `payaccount_group` (`groupid`,`accountid`) VALUES 
 (1,1010),
 (1,1020),
 (1,1030),
 (1,2010),
 (1,2020),
 (1,2030),
 (1,2031),
 (1,2032),
 (1,2040),
 (1,3010),
 (1,3020),
 (1,5020),
 (1,9010),
 (2,1010),
 (2,1020),
 (2,1030),
 (2,2010),
 (2,2020),
 (2,2030),
 (2,2031),
 (2,2032),
 (2,2040),
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
 (2,5020),
 (3,9010),
 (4,1010),
 (4,1020),
 (4,1030),
 (4,2010),
 (4,2020),
 (4,2030),
 (4,2031),
 (4,2032),
 (4,2040),
 (11,5010),
 (11,5020),
 (21,2010),
 (21,2020),
 (21,2030),
 (21,2040),
 (22,1020),
 (22,1030),
 (30,3010),
 (30,3020);

INSERT INTO `policy` (`policyid`) VALUES 
 (1),
 (2);

INSERT INTO `policy_accountgroup` (`policyid`,`groupid`) VALUES 
 (1,21),
 (1,30),
 (2,22),
 (2,30);

INSERT INTO `policy_attribute` (`policyid`,`attributeid`) VALUES 
 (1,1),
 (1,5),
 (2,3);

insert into policy_attribute_value (policyid, attributeid, fromtime, regtime, value) values
 (1,2,'2007-01-01',now(),30),
 (2,4,'2007-01-01',now(),8);

INSERT INTO `policy_description` (`policyid`,`language`,`description`) VALUES 
 (1,'en','Salaried'),
 (1,'sv','Månadsavlönad'),
 (1,'th','Salaried'),
 (2,'en','Hourly paid'),
 (2,'sv','Timavlönad'),
 (2,'th','Timavlönad');

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

