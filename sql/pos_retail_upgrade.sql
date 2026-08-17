-- Retail POS extensions for thERP (MariaDB/MySQL)
-- Additive migration: existing orders, receipts, and accounting remain intact.

CREATE TABLE IF NOT EXISTS pos_payment_method (
  methodid varchar(20) NOT NULL,
  description varchar(60) NOT NULL,
  active smallint NOT NULL DEFAULT 1,
  PRIMARY KEY (methodid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pos_payment (
  paymentid int unsigned NOT NULL AUTO_INCREMENT,
  orderid int unsigned NOT NULL,
  methodid varchar(20) NOT NULL,
  amount decimal(12,2) NOT NULL,
  reference varchar(80) DEFAULT NULL,
  createdby varchar(16) DEFAULT NULL,
  createdtime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (paymentid),
  KEY pos_payment_order (orderid),
  CONSTRAINT fk_pos_payment_order FOREIGN KEY (orderid) REFERENCES salesorder(orderid),
  CONSTRAINT fk_pos_payment_method FOREIGN KEY (methodid) REFERENCES pos_payment_method(methodid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pos_shift (
  shiftid int unsigned NOT NULL AUTO_INCREMENT,
  username varchar(16) NOT NULL,
  locationid int unsigned NOT NULL,
  opened_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at datetime DEFAULT NULL,
  opening_cash decimal(12,2) NOT NULL DEFAULT 0,
  closing_cash decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (shiftid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pos_store_credit (
  creditid int unsigned NOT NULL AUTO_INCREMENT,
  customerid int unsigned NOT NULL,
  amount decimal(12,2) NOT NULL,
  balance decimal(12,2) NOT NULL,
  reference varchar(80) DEFAULT NULL,
  expires_at datetime DEFAULT NULL,
  createdby varchar(16) DEFAULT NULL,
  createdtime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (creditid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pos_return (
  returnid int unsigned NOT NULL AUTO_INCREMENT,
  orderid int unsigned NOT NULL,
  reason varchar(255) NOT NULL,
  refund_method varchar(20) NOT NULL,
  total decimal(12,2) NOT NULL DEFAULT 0,
  createdby varchar(16) DEFAULT NULL,
  createdtime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (returnid),
  KEY pos_return_order (orderid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pos_return_item (
  returnid int unsigned NOT NULL,
  order_line smallint unsigned NOT NULL,
  productid varchar(32) NOT NULL,
  quantity decimal(12,3) NOT NULL,
  amount decimal(12,2) NOT NULL,
  PRIMARY KEY (returnid, order_line),
  CONSTRAINT fk_pos_return_item FOREIGN KEY (returnid) REFERENCES pos_return(returnid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO pos_payment_method (methodid, description, active) VALUES
 ('cash', 'Cash', 1), ('card', 'Card', 1), ('bank', 'Bank transfer', 1),
 ('gift', 'Gift card', 1), ('store_credit', 'Store credit', 1)
ON DUPLICATE KEY UPDATE description=VALUES(description), active=VALUES(active);

INSERT INTO permission (permissionid, description) VALUES
 (20, 'POS apply discount'), (21, 'POS void sale'), (22, 'POS refund sale'), (23, 'POS close shift')
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT IGNORE INTO usergroup_permission (groupid, permissionid)
SELECT 1, permissionid FROM permission WHERE permissionid IN (20,21,22,23);
