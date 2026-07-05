<?php
include('include.php');
include('salesorder.inc.php');
include('invoice_pdf.inc.php');

$orderid = getParam('orderid');
$type = getParam('type', 'invoice');

createInvoicePDF($orderid, '', $type);
?>