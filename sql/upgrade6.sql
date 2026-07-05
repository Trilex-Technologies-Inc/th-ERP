CREATE TABLE `product2` (
  `productid` varchar(32) NOT NULL,
  `description` varchar(80) NOT NULL default '',
  `purchase_price` double default '0',
  `sales_price` double default '0',
  `model` varchar(80) NOT NULL default '',
  `quantity` int(11) NOT NULL default '0',
  `categoryid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into product2 (productid, description, purchase_price, sales_price, model, quantity, categoryid)
select productid, description, purchase_price, sales_price, model, quantity, categoryid
from product;

CREATE TABLE `product_supplier2` (
  `productid` varchar(32) NOT NULL,
  `supplierid` int(10) unsigned NOT NULL,
  `price` double default NULL,
  PRIMARY KEY  (`productid`,`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into product_supplier2 (productid, supplierid, price)
select productid, supplierid, price
from product_supplier;

CREATE TABLE `purchaseorder_item2` (
  `orderid` int(10) unsigned NOT NULL,
  `no` smallint(5) unsigned NOT NULL,
  `productid` varchar(32) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unitprice` double default NULL,
  `vat` double default NULL,
  `accountid` int(10) unsigned default NULL,
  `comment` varchar(80) default NULL,
  `received_quantity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  USING BTREE (`orderid`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into purchaseorder_item2 
select * from purchaseorder_item;

CREATE TABLE `salesorder_item2` (
  `orderid` int(10) unsigned NOT NULL default '0',
  `productid` varchar(32) NOT NULL,
  `quantity` int(10) unsigned NOT NULL default '0',
  `unitprice` double NOT NULL,
  `no` smallint(5) unsigned NOT NULL,
  `comment` varchar(80) NOT NULL,
  `vat` double NOT NULL,
  PRIMARY KEY  USING BTREE (`orderid`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into salesorder_item2
select * from salesorder_item;

CREATE TABLE `stockmove2` (
  `moveid` int(10) unsigned NOT NULL auto_increment,
  `productid` varchar(32) NOT NULL,
  `diff` int(11) NOT NULL,
  `narrative` varchar(80) default NULL,
  `transactionid` int(10) unsigned default NULL,
  `salesorderid` int(10) unsigned default NULL,
  `purchaseorderid` int(10) unsigned default NULL,
  `no` smallint(5) unsigned default NULL,
   productionorderid integer unsigned,
   createdby varchar(16),
  PRIMARY KEY  (`moveid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into stockmove2 (moveid, productid, diff, narrative, transactionid, salesorderid, purchaseorderid, no, productionorderid, createdby)
select moveid, productid, diff, narrative, transactionid, salesorderid, purchaseorderid, no, productionorderid, createdby
from stockmove;

create table bom2
(
parentid varchar(32) not null,
childid varchar(32) not null,
quantity integer default 1,
primary key (parentid, childid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into bom2
select * from bom;

create table productionorder_item2
(
  orderid integer unsigned not null,
  no smallint unsigned not null,
  productid varchar(32) not null,
  quantity integer,
  primary key (orderid, no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into productionorder_item2
select * from productionorder_item;

drop table productionorder_item;
drop table bom;
drop table stockmove;
drop table salesorder_item;
drop table purchaseorder_item;
drop table product_supplier;
drop table product;

alter table product2 rename product;

alter table product_supplier2 rename product_supplier;
alter table product_supplier add constraint fk_product_supplier foreign key (supplierid) references supplier(supplierid);
alter table product_supplier add constraint fk_supplier_product foreign key (productid) references product(productid);

alter table purchaseorder_item2 rename purchaseorder_item;
alter table purchaseorder_item add constraint fk_purchaseorder_product foreign key (productid) references product(productid);
alter table purchaseorder_item add constraint fk_purchaseorder_item foreign key (orderid) references purchaseorder(orderid);

alter table salesorder_item2 rename salesorder_item;
alter table salesorder_item add constraint fk_salesorder_product foreign key (productid) references product(productid);
alter table salesorder_item add constraint fk_salesorder_item foreign key (orderid) references salesorder(orderid);

alter table stockmove2 rename stockmove;
alter table stockmove add constraint fk_stockmove_productionorder foreign key (productionorderid) references productionorder (orderid);
alter table stockmove add constraint fk_stockmove_createdby foreign key (createdby) references user (username);
alter table stockmove add constraint fk_stockmove_productid foreign key (productid) references product (productid);
alter table stockmove add constraint fk_stockmove_transaction foreign key (transactionid) references transaction (transactionid);
alter table stockmove add constraint fk_stockmove_salesorder foreign key (salesorderid) references salesorder (orderid);
alter table stockmove add constraint fk_stockmove_purchaseorder foreign key (purchaseorderid) references purchaseorder (orderid);

alter table bom2 rename bom;
alter table bom add constraint fk_bom_parent foreign key (parentid) references product (productid);
alter table bom add constraint fk_bom_child foreign key (childid) references product (productid);

alter table productionorder_item2 rename productionorder_item;
alter table productionorder_item add constraint fk_productionorder_product foreign key (productid) references product(productid);
alter table productionorder_item add constraint fk_productionorder_item foreign key (orderid) references productionorder(orderid);
