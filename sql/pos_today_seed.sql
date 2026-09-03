-- Create two completed POS sales for the current day.
-- Run after sql/pos_seed.sql and the attached therp.sql schema.
-- This uses fresh IDs, so it is safe to run without overwriting existing sales.

START TRANSACTION;

SET @user = COALESCE((SELECT username FROM user ORDER BY username LIMIT 1), 'admin');
SET @trans1 = COALESCE((SELECT MAX(transactionid) FROM transaction), 0) + 1;
SET @order1 = COALESCE((SELECT MAX(orderid) FROM salesorder), 0) + 1;

-- Sale 1: two coffees and one sandwich, total 11.13 including 6% VAT.
INSERT INTO transaction (transactionid, narrative, transtime, createdby, valid, createdtime)
VALUES (@trans1, CONCAT('POS demo seed sale ', @order1), NOW() - INTERVAL 2 HOUR, @user, 1, NOW());
INSERT INTO transaction_part (transactionid, dimid, accountid, amount) VALUES
 (@trans1, 1, 1010, 11.13),
 (@trans1, 1, 4100, -10.50),
 (@trans1, 1, 2300, -0.63);
INSERT INTO salesorder (orderid, orderdate, customerid, invoice_transid, cancelled, locationid, createdby)
VALUES (@order1, NOW() - INTERVAL 2 HOUR, 1, @trans1, 0, 1, @user);
INSERT INTO salesorder_item (orderid, productid, quantity, unitprice, no, comment, vat) VALUES
 (@order1, 'POS-001', 2, 2.50, 1, 'POS demo seed', 6),
 (@order1, 'POS-003', 1, 5.50, 2, 'POS demo seed', 6);
INSERT INTO receipt (customerid, amount, transactionid, createdby)
VALUES (1, 11.13, @trans1, @user);
SET @receipt1 = LAST_INSERT_ID();
INSERT INTO receipt_allocation (receiptid, orderid, amount) VALUES (@receipt1, @order1, 11.13);

SET @trans2 = @trans1 + 1;
SET @order2 = @order1 + 1;
-- Sale 2: one fruit and one tea, total 4.24 including 6% VAT.
INSERT INTO transaction (transactionid, narrative, transtime, createdby, valid, createdtime)
VALUES (@trans2, CONCAT('POS demo seed sale ', @order2), NOW() - INTERVAL 30 MINUTE, @user, 1, NOW());
INSERT INTO transaction_part (transactionid, dimid, accountid, amount) VALUES
 (@trans2, 1, 1010, 4.24),
 (@trans2, 1, 4100, -4.00),
 (@trans2, 1, 2300, -0.24);
INSERT INTO salesorder (orderid, orderdate, customerid, invoice_transid, cancelled, locationid, createdby)
VALUES (@order2, NOW() - INTERVAL 30 MINUTE, 1, @trans2, 0, 1, @user);
INSERT INTO salesorder_item (orderid, productid, quantity, unitprice, no, comment, vat) VALUES
 (@order2, 'POS-005', 1, 2.00, 1, 'POS demo seed', 6),
 (@order2, 'POS-002', 1, 2.00, 2, 'POS demo seed', 6);
INSERT INTO receipt (customerid, amount, transactionid, createdby)
VALUES (1, 4.24, @trans2, @user);
SET @receipt2 = LAST_INSERT_ID();
INSERT INTO receipt_allocation (receiptid, orderid, amount) VALUES (@receipt2, @order2, 4.24);

COMMIT;
