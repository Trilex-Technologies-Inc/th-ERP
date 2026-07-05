<?php

function createInvoicePDF($orderid, $filename = '', $type = 'invoice')
{
	$template = findValue("select invoice_template from settings");
	include("invoice_pdf_$template.inc.php");
	buildInvoicePDF($orderid, $filename, type);	
}

?>