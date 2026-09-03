-- Test data for sql/pos_retail_upgrade.sql
START TRANSACTION;
INSERT INTO pos_shift (username, locationid, opened_at, opening_cash)
SELECT username, 1, NOW(), 100.00 FROM user ORDER BY admin DESC, username LIMIT 1;

INSERT INTO pos_store_credit (customerid, amount, balance, reference, createdby)
SELECT 1, 25.00, 25.00, 'TEST-CREDIT-001', username FROM user ORDER BY admin DESC, username LIMIT 1;

-- Attach example tender records to the two latest completed cash sales.
INSERT INTO pos_payment (orderid, shiftid, methodid, amount, reference, createdby, createdtime)
SELECT so.orderid,
       (SELECT MAX(ps.shiftid) FROM pos_shift ps),
       IF(MOD(so.orderid,2)=0, 'card', 'cash'),
       ROUND(SUM(si.quantity*si.unitprice*(1+si.vat/100)),2),
       CONCAT('TEST-', so.orderid), so.createdby, so.orderdate
FROM salesorder so JOIN salesorder_item si ON si.orderid=so.orderid
WHERE so.customerid=1 AND so.invoice_transid IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM pos_payment pp WHERE pp.orderid=so.orderid)
GROUP BY so.orderid, so.createdby, so.orderdate
ORDER BY so.orderid DESC LIMIT 2;
COMMIT;
