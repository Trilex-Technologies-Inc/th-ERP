<?php
	sql("
	create table dimension (
		dimid integer unsigned not null,
		name varchar(40),
		primary key (dimid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	insert into dimension (dimid, name)
	values (1, 'General ledger')");
	
	sql("
	CREATE TABLE account2 (
		dimid integer unsigned not null default '1',
    	accountid int(10) unsigned NOT NULL,
		name varchar(45) NOT NULL,
		PRIMARY KEY  (dimid, accountid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	insert into account2 (dimid, accountid, name)
	select 1, accountid, name
	from account");
	
	sql("
	CREATE TABLE account_group2 (
		groupid int(10) unsigned NOT NULL,
		dimid integer unsigned not null,
		accountid int(10) unsigned NOT NULL,
		PRIMARY KEY  (groupid,dimid,accountid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	insert into account_group2 (groupid, dimid, accountid)
	select groupid, 1, accountid from account_group");
	
	sql("alter table accountconf add dimid integer unsigned not null default '1'");
	sql("alter table accountconf drop foreign key accountconf_ibfk_1");
	sql("alter table accountconf drop foreign key accountconf_ibfk_2");
	sql("alter table accountconf drop foreign key accountconf_ibfk_3");
	sql("alter table accountconf drop foreign key accountconf_ibfk_4");
	sql("alter table accountconf drop foreign key accountconf_ibfk_5");
	sql("alter table accountconf drop foreign key accountconf_ibfk_6");
	sql("alter table accountconf drop foreign key accountconf_ibfk_7");
	sql("alter table accountconf drop foreign key accountconf_ibfk_8");
	sql("alter table accountconf drop foreign key accountconf_ibfk_9");
	sql("alter table accountconf drop foreign key accountconf_ibfk_10");
	sql("alter table accountconf drop foreign key accountconf_ibfk_11");
	
	sql("drop table bankaccount");
	
	sql("alter table category add dimid integer unsigned not null default '1'");
	sql("alter table category drop foreign key category_ibfk_1");
	
	sql("alter table customer add dimid integer unsigned not null default '1'");
	sql("alter table customer drop foreign key fk_customer_credit_account");
	sql("alter table customer drop index fk_customer_credit_account");

	sql("alter table payaccount add dimid integer unsigned not null default '1'");
	
	sql("
	CREATE TABLE transaction_part2 (
		transactionid int(10) unsigned NOT NULL default '0',
		dimid integer unsigned not null default '1',
		accountid int(10) unsigned NOT NULL default '0',
		amount double NOT NULL default '0',
		PRIMARY KEY  (transactionid,dimid,accountid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	sql("
	insert into transaction_part2 (transactionid, dimid, accountid, amount)
	select transactionid, 1, accountid, amount 
	from transaction_part");
	sql("drop table transaction_part");
	sql("alter table transaction_part2 rename transaction_part");

	sql("alter table supplier add dimid integer unsigned not null default '1'");
	sql("alter table supplier drop foreign key fk_supplier_credit_account");
	sql("alter table supplier drop index fk_supplier_credit_account");
	
	sql("drop table account_group");
	sql("alter table account_group2 rename account_group");

	sql("drop table account");
	sql("alter table account2 rename account");
	sql("
	alter table account add constraint
	foreign key fk_account_dim (dimid) references dimension (dimid)");
	
	sql("alter table accountconf add constraint fk_accountconf_account_receivable
	FOREIGN KEY (dimid, account_receivable) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_account_payable
	FOREIGN KEY (dimid, account_payable) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_account_vat_payable
	FOREIGN KEY (dimid, vat_payable) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_vat_recoverable
	FOREIGN KEY (dimid, vat_recoverable) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_finished_goods
	 FOREIGN KEY (dimid, finished_goods) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_cost_of_sales
	FOREIGN KEY (dimid, cost_of_sales) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_goods_received
	FOREIGN KEY (dimid, goods_received_suspense) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_inventory_adjustment
	FOREIGN KEY (dimid, inventory_adjustment) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_default_cash
	FOREIGN KEY (dimid, default_cash) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_default_sales
	FOREIGN KEY (dimid, default_sales) REFERENCES account (dimid, accountid)");
	sql("alter table accountconf add constraint fk_accountconf_raw_material
	FOREIGN KEY (dimid, raw_material) REFERENCES account (dimid, accountid)");

	sql("alter table category add  constraint fk_category_inventory
	foreign key (dimid, inventory_accountid) references account(dimid, accountid)");
	sql("alter table category add  constraint fk_category_expense
	foreign key (dimid, expense_accountid) references account(dimid, accountid)");
	sql("alter table category add  constraint fk_category_revenue
	foreign key (dimid, revenue_accountid) references account(dimid, accountid)");

	sql("alter table customer add constraint fk_customer_credit_account 
	foreign key (dimid, credit_account) references account (dimid, accountid)");

	sql("alter table supplier add constraint fk_supplier_credit_account 
	foreign key (dimid, credit_account) references account (dimid, accountid)");
	
	sql("alter table payaccount add constraint fk_payaccount_account 
	foreign key (dimid, glaccountid) references account (dimid, accountid)");

	sql("alter table transaction_part add constraint fk_transaction_account 
	foreign key (dimid, accountid) references account (dimid, accountid)");

	sql("
	alter table account_group add constraint fk_account_group
	FOREIGN KEY (dimid, accountid) REFERENCES account (dimid, accountid)");
	sql("
	alter table account_group add constraint fk_group_account
	FOREIGN KEY (groupid) REFERENCES accountgroup (groupid)");
?>
