<?php
include('../include/ConvertCharset.class.php');

function ccs($text)
{
	$lang = getLanguage();
	if ($lang == 'en')
		return $text;
	if ($lang == 'sv')
		return utf8_decode($text);
	if ($lang == 'cn'){
		return iconv("UTF-8","GB2312//IGNORE",$text);
	}
	if ($text == null)
		$text = '';
	if (strlen($text) == 0)
		$text = ' ';
	$converter = new ConvertCharset;	
	$text = $converter->Convert($text, "UTF-8", "cp874", 0);
	return $text;
}

function setHeaderFont($pdf)
{	
	if (getLanguage() == 'th')
		$pdf->setFont('Angsab', '', 20);
	elseif (getLanguage() == 'cn')
		$pdf->setFont('GB', '', 14);
	else
		$pdf->setFont('Arial', 'B', 14);
	return $pdf;
}

function setLabelFont($pdf, $bold = false)
{
	$style = $bold ? 'B' : '';
	if (getLanguage() == 'th')
		$pdf->setFont('Angsa' . $style, '', 14);
	elseif (getLanguage() == 'cn')
		$pdf->setFont('GB', $style, 10);
	else
		$pdf->setFont('Arial', $style, 10);
	return $pdf;
}

function setLogoFont($pdf, $bold = false)
{
	$style = $bold ? 'B' : '';
	if (getLanguage() == 'th')
		$pdf->setFont('Angsa' . $style, '', 16);
	elseif (getLanguage() == 'cn')
		$pdf->setFont('GB', $style, 12);
	else
		$pdf->setFont('Arial', $style, 12);
	return $pdf;
}

function setNormalFont($pdf, $bold = false)
{
	$style = $bold ? 'B' : '';
	if (getLanguage() == 'th')
		$pdf->setFont('Angsa' . $style, '', 14);
	elseif (getLanguage() == 'cn')
		$pdf->setFont('GB', $style, 10);
	else
		$pdf->setFont('Times', $style, 10);
	return $pdf;
}

function setNumericFont($pdf, $bold = false)
{
	$style = $bold ? 'B' : '';
	$pdf->setFont('Courier', $style, 10);
	return $pdf;
}


function buildInvoicePDF($orderid, $filename = '', $type = 'invoice')
{
	include('../include/fpdf/chinese.php');
	include('../include/fpdf/fpdf.php');
	$rightAlign = 'R';
	$order = find("select
	                  orderid,
	                  no,
					  name,
					  c.customerid,
					  unix_timestamp(transtime) as invoicedate,
					  unix_timestamp(duedate) as duedate
				   from salesorder so
				   join customer c on so.customerid=c.customerid
				   join transaction t on t.transactionid=invoice_transid
				   where so.orderid=$orderid");
	$incVAT = $order->customerid == CUSTOMERID_CASH;
	$items = query("select
	                  p.productid,
					  model,
					  si.quantity,
					  unitprice,
					  vat,
					  no,
					  u.description as unittype
					from salesorder_item si
					join product p on p.productid=si.productid
				    left outer join unittype u on u.unittype=p.unittype
					where orderid=$orderid 
					and si.productid != " . PRODUCTID_ROUNDING . "
					");

	$company = find("
	select companyname, streetaddress, city, zipcode, vatnumber, registrationno 
	from companyinfo");
	$customer = find("select name, streetaddress, city, zipcode, vatnumber from customer 
	                  where customerid=$order->customerid");
	$customerPhones = query("
	select telephoneno, description 
	from customer_phone cp
	join phone_category c on c.phonecatid=cp.phonecatid
	where customerid=$order->customerid");

	$total = getSalesOrderTotalEx($orderid);
	$vatTotal = getSalesOrderTotalVat($orderid);
	$rounding = findValue("select unitprice from salesorder_item 
						   where orderid=$orderid and productid=" . PRODUCTID_ROUNDING);

	define('MARGIN', 5);
	define('WIDTH', 205);
	define('HEIGHT', 290);
	define('ROWHEIGHT', 6);

	if (getLanguage() == 'cn'){
		$pdf=new PDF_Chinese();
		$pdf->AddGBFont();
	}
	else{
		$pdf=new FPDF();
	}
	
	$pdf->AddPage();
	$pdf->AddFont('angsa','','angsa.php'); 
	$pdf->AddFont('angsab','','angsab.php'); 
	$pdf->Line(MARGIN,MARGIN, MARGIN, HEIGHT);
	$pdf->Line(MARGIN,HEIGHT, WIDTH, HEIGHT);
	$pdf->Line(WIDTH,HEIGHT, WIDTH, MARGIN);
	$pdf->Line(WIDTH,MARGIN, MARGIN, MARGIN);

	$leftX = MARGIN * 2;
	$x = $leftX;
	$y = 2 * MARGIN + ROWHEIGHT;
	$logofile = "../images/$company->companyname" . ".png";
	if (!file_exists($logofile))
		$logofile = "../images/companylogo.png";
	if (file_exists($logofile)) {
		$pdf->Image($logofile, MARGIN+1, MARGIN+1);
		$pdf = setLabelFont($pdf, true);
		$y += 12;		
	} else {
		$pdf = setLogoFont($pdf, true);
		$pdf->Text($x, $y, ccs($company->companyname));
		$y += ROWHEIGHT;		
	}
	$pdf = setLabelFont($pdf, false);
	$y += ROWHEIGHT;	
	$pdf = setLabelFont($pdf, false);
	$pdf->Text($x, $y, ccs(tr("Address") . ":"));
	$x += 25;
	$pdf = setNormalFont($pdf, false);
	$pdf->Text($x, $y, ccs($company->streetaddress));
	$y += ROWHEIGHT;
	$postal = "$company->city $company->zipcode";
	if (getLanguage() == 'sv')
		$postal = "$company->zipcode $company->city";
	$pdf->Text($x, $y, ccs("$postal"));
	$pdf = setLabelFont($pdf, false);
	$x = $leftX;
	if (!isEmpty($company->vatnumber)) {
		$y += ROWHEIGHT;
		$pdf = setLabelFont($pdf, false);		
		$pdf->Text($x, $y, ccs(tr("VAT number")) . ': ');
		$x += 25;
		$pdf = setNumericFont($pdf, false);		
		$pdf->Text($x, $y, ccs($company->vatnumber));
		$x = $leftX;
	}
	if (!isEmpty($company->registrationno)) {
		$pdf = setLabelFont($pdf, false);		
		$y += ROWHEIGHT;
		$text = ccs(tr("Registrationno")) . ': ';
		$pdf->Text($x, $y, $text);
		$x += 25;
		$pdf = setNumericFont($pdf, false);		
		$pdf->Text($x, $y, ccs($company->registrationno));
		$x = $rightX;
	}

	$rightX = 120;
	$x = $rightX;
	$y = 2*MARGIN + ROWHEIGHT;	
	$pdf = setHeaderFont($pdf);
	switch ($type) {
		case 'invoice': $label = 'Invoice'; break;
		case 'receipt': $label = 'Receipt'; break;
		case 'credit': $label = 'Credit note'; break;
	}
	$pdf->Text($x, $y, ccs(tr($label)));
	$pdf = setLabelFont($pdf);
	$label = "Order #";
	if ($type == 'invoice')
		$label = "Invoice #";
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, ccs(tr($label)));
	$pdf = setNumericFont($pdf, true);
	$x += 25;
	$pdf->Text($x, $y, $order->no);
	$pdf = setLabelFont($pdf);
	$x = $rightX;
	$label = $type == 'invoice' ? "Invoice date" : "Receipt date";
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, ccs(tr($label)) . ": ");
	$pdf = setNumericFont($pdf);
	$x += 25;
	$pdf->Text($x, $y, formatDate($order->invoicedate));
	$x = $rightX;
	$pdf = setLabelFont($pdf);
	if ($type == 'invoice') {
		$y += ROWHEIGHT;
		$pdf->Text($x, $y, ccs(tr("Due date") . ": "));
		$pdf = setNumericFont($pdf);
		$x += 25;
		$pdf->Text($x, $y, formatDate($order->duedate));
		$x = $rightX;
	}
	$y += ROWHEIGHT;
	$pdf = setNormalFont($pdf);
	$customername = $customer->name;
	if ($order->customerid == CUSTOMERID_CASH)
		$customername = '';
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, ccs($customername));
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, ccs($customer->streetaddress));
	$postal = "$customer->city $customer->zipcode";
	if (getLanguage() == 'sv')
		$postal = "$customer->zipcode $customer->city";
	$y += ROWHEIGHT;		
	$pdf->Text($x, $y, ccs($postal));
	$y += ROWHEIGHT;		
	while ($row = fetch($customerPhones)) {
		$text = $row->telephoneno . ' (' . $row->description . ')';		
		$pdf->Text($x, $y, ccs($text));
		$x += 80;
	}	
	$x = $rightX;
	if (!isEmpty($customer->vatnumber)) {
		$y += ROWHEIGHT;		
		$text1 = ccs(tr("VAT number")) . ': ';
		$text2 = $customer->vatnumber;
		$pdf->Text($text1);
		$pdf = setNumericFont($pdf);
		$x = $rightX;
		$pdf->Text($x, $y, $text2);
	}	
	$pdf->SetX(0);
	$pdf->SetY($y+ROWHEIGHT);

	$pdf = setLabelFont($pdf);
	$pdf->Cell(20, ROWHEIGHT, ccs(tr("Productno")), 1);
	$pdf->Cell(90, ROWHEIGHT, ccs(tr("Product")), 1);
	$pdf->Cell(18, ROWHEIGHT, ccs(tr("Quantity")), 1, 0, $rightAlign);
	$pdf->Cell(22, ROWHEIGHT, ccs(tr("Unit price")), 1, 0, $rightAlign);
	$pdf->Cell(30, ROWHEIGHT, ccs(tr("Amount")), 1, 1, $rightAlign);
	while ($row = fetch($items)) {
		$pdf = setLabelFont($pdf);
		$pdf->Cell(20, ROWHEIGHT, ccs($row->productid), 'LR');
		$pdf = setNormalFont($pdf);
		$pdf->Cell(90, ROWHEIGHT, ccs($row->model), 'LR');
		if (getLanguage() == 'cn'){
			$pdf = setNormalFont($pdf);
			$text = $row->quantity . '' . ccs($row->unittype).'กก';
		}
		else{
			$pdf = setNumericFont($pdf);
			$text = $row->quantity . '' . ccs($row->unittype);
		}
		$pdf->Cell(18, ROWHEIGHT, $text, 'LR', 0, 'R');
		$pdf = setNumericFont($pdf);
		$unitprice = $row->unitprice;
		if ($incVAT)
			$unitprice += $row->vat;
		$pdf->Cell(22, ROWHEIGHT, formatMoney($unitprice), 'LR', 0, 'R');
		$pdf->Cell(30, ROWHEIGHT, formatMoney($unitprice*$row->quantity), 'LR', 1, 'R');
	}
	if (!$incVAT) {
		$pdf = setLabelFont($pdf);
		$pdf->Cell(100, ROWHEIGHT, '', 'T');
		$pdf->Cell(20, ROWHEIGHT, '', 'T', 0, 'R');
		$pdf->Cell(30, ROWHEIGHT, ccs(tr('Subtotal')), 'TR', 0, $rightAlign);
		$pdf = setNumericFont($pdf);
		$pdf->Cell(30, ROWHEIGHT, formatMoney($total), 'TR', 1, 'R');
		$top = '';
	} else
		$top = 'T';
	$pdf->Cell(100, ROWHEIGHT, '', "$top");
	$pdf->Cell(20, ROWHEIGHT, '', "$top", 0, 'R');
	$pdf = setLabelFont($pdf);
	$pdf->Cell(30, ROWHEIGHT, ccs(tr('VAT')), $top . 'R', 0, $rightAlign);
	$pdf = setNumericFont($pdf);
	$pdf->Cell(30, ROWHEIGHT, formatMoney($vatTotal), $top . 'R', 1, 'R');
	
	if ($rounding >= 0.01) {	
		$pdf->Cell(100, ROWHEIGHT, '', '');
		$pdf->Cell(20, ROWHEIGHT, '', '', 0, 'R');
		$pdf = setLabelFont($pdf);
		$pdf->Cell(30, ROWHEIGHT, ccs(tr('Rounding')), '', 0, $rightAlign);
		$pdf = setNumericFont($pdf);
		$pdf->Cell(30, ROWHEIGHT, formatMoney($rounding), 'RL', 1, 'R');	
	}
	
	$pdf->Cell(100, ROWHEIGHT, '', '');
	$pdf->Cell(20, ROWHEIGHT, '', '', 0, 'R');
	$pdf = setLabelFont($pdf, true);
	$label = 'To pay';
	if ($type == 'credit')
		$label = 'To refund';
	$pdf->Cell(30, ROWHEIGHT, ccs(tr($label)), '', 0, $rightAlign);
	$pdf = setNumericFont($pdf, true);
	$border = 'B';
	$showExchange = $type == 'receipt';
	if ($showExchange) {
		$payedGross = findValue("
		select sum(amount) from receipt_allocation 
		where orderid=$orderid and amount > 0");
	 	$exchange = $payedGross - $total - $vatTotal;
	 	if (round($exchange, 2) == 0)
	 		$showExchange = false;
	}
	if ($showExchange)
		$border = '';
	$pdf->Cell(30, ROWHEIGHT, formatMoney($total + $vatTotal), $border . 'RL', 1, 'R');
	if ($showExchange) {
		$pdf->Cell(100, ROWHEIGHT, '', '');
		$pdf->Cell(20, ROWHEIGHT, '', '', 0, 'R');
		$pdf = setLabelFont($pdf);
		$pdf->Cell(30, ROWHEIGHT, ccs(tr('Payed')), '', 0, 'R');
		$pdf = setNumericFont($pdf);
		$pdf->Cell(30, ROWHEIGHT, formatMoney($payedGross), 'RL', 1, 'R');
		$pdf->Cell(100, ROWHEIGHT, '', '');
		$pdf->Cell(20, ROWHEIGHT, '', '', 0, 'R');
		$pdf = setLabelFont($pdf);
		$pdf->Cell(30, ROWHEIGHT, ccs(tr('Exchange')), '', 0, 'R');
		$pdf = setNumericFont($pdf);
		$pdf->Cell(30, ROWHEIGHT, formatMoney($exchange), 'BRL', 1, 'R');
	}
	$pdf->Cell(100, 2*ROWHEIGHT, '', '', 1);
	$pdf = setLabelFont($pdf);
	if ($type == 'receipt') {
		$pdf->Cell(100, ROWHEIGHT, ccs(tr('Customer signature')), '');
		$pdf->Cell(100, ROWHEIGHT, ccs(tr('Cashier signature')), '', 1);
		$pdf->Cell(100, 2*ROWHEIGHT, '____________________________________', '');
		$pdf->Cell(100, 2*ROWHEIGHT, '____________________________________', '', 1);
	} else if ($type == 'invoice') {
		$comment = findValue("select comment from salesorder where orderid=$orderid", null);
		$pdf = setNormalFont($pdf);
		if ($comment != null) {
			$pdf->Cell(200, ROWHEIGHT, ccs($comment), '', 1);
		}
		$rs = query("select text from invoice_footer order by rowno");
		while ($row = fetch($rs)) {
			$pdf->Cell(200, ROWHEIGHT, ccs($row->text), '', 1);
		}
	}
	
	$dest = '';
	if (!isEmpty($filename)) {
		$dest = 'F';
	}
	$pdf->Output($filename, $dest);
}

?>