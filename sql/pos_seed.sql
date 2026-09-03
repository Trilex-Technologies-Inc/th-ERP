-- thERP Point of Sale starter data
-- Run after sql/therp-new.sql (or the current schema) as the application database user.
-- The statements are intentionally rerunnable.

-- POS searches this field; older installations may not have it yet.
ALTER TABLE product ADD COLUMN IF NOT EXISTS barcode varchar(64) DEFAULT NULL;

INSERT INTO location (locationid, name, streetaddress, city, zipcode)
VALUES (1, 'Main store', '1 Market Street', 'Demo City', '10000')
ON DUPLICATE KEY UPDATE name = VALUES(name), streetaddress = VALUES(streetaddress), city = VALUES(city), zipcode = VALUES(zipcode);

-- CUSTOMERID_CASH is 1 in include/therp_include.php.
INSERT INTO customer (customerid, name, streetaddress, city, zipcode, use_vat, pricelistid)
VALUES (1, 'Cash customer', '', '', '', 1, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), use_vat = VALUES(use_vat), pricelistid = VALUES(pricelistid);

INSERT INTO category (categoryid, description, vatcatid, revenue_accountid, stock)
VALUES (10, 'POS products', 1, 4100, 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), vatcatid = VALUES(vatcatid), revenue_accountid = VALUES(revenue_accountid), stock = VALUES(stock);

INSERT INTO product (productid, description, purchase_price, model, quantity, categoryid, active, barcode)
VALUES
 ('POS-001', 'Everyday coffee', 1.20, 'Coffee', 100, 10, 1, '735000000001'),
 ('POS-002', 'Tea', 0.90, 'Tea', 100, 10, 1, '735000000002'),
 ('POS-003', 'Sandwich', 2.50, 'Sandwich', 50, 10, 1, '735000000003'),
 ('POS-004', 'Bottled water', 0.60, 'Water', 100, 10, 1, '735000000004'),
 ('POS-005', 'Fruit', 0.75, 'Fruit', 80, 10, 1, '735000000005')
ON DUPLICATE KEY UPDATE description = VALUES(description), purchase_price = VALUES(purchase_price), model = VALUES(model), categoryid = VALUES(categoryid), active = VALUES(active), barcode = VALUES(barcode);

INSERT INTO sales_price (productid, listid, price)
VALUES
 ('POS-001', 1, 2.50), ('POS-002', 1, 2.00), ('POS-003', 1, 5.50),
 ('POS-004', 1, 1.50), ('POS-005', 1, 2.00)
ON DUPLICATE KEY UPDATE price = VALUES(price);
