<?php

function ccs($str)
{
	return utf8_decode($str);	
}

function setHeaderFont($pdf)
{	
	$pdf->setFont('Arial', 'B', 14);
	return $pdf;
}

function setLabelFont($pdf, $bold = false)
{
	$style = $bold ? 'B' : '';
	$pdf->setFont('Arial', $style, 10);
	return $pdf;
}

function setLogoFont($pdf, $bold = false)
{
	$style = $bold ? 'B' : '';
	$pdf->setFont('Arial', $style, 12);
	return $pdf;
}

function setNormalFont($pdf, $bold = false)
{
	$style = $bold ? 'B' : '';
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
	include('../include/fpdf/fpdf.php');
	$rightAlign = 'R';
	$order = find("select
	                  orderid,
	                  no,
					  name,
					  c.customerid,
					  unix_timestamp(transtime) as invoicedate,
					  unix_timestamp(duedate) as duedate,
					  orderedby
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
					  u.description as unittype,
					  comment
					from salesorder_item si
					join product p on p.productid=si.productid					
				    left outer join unittype u on u.unittype=p.unittype
					where orderid=$orderid 
					and si.productid != " . PRODUCTID_ROUNDING . "
					");

	$company = find("
	select companyname, streetaddress, city, zipcode, vatnumber, registrationno, telephoneno
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
	define('LEFTMARGIN', 15);
	define('WIDTH', 205);
	define('HEIGHT', 290);
	define('ROWHEIGHT', 6);

	$pdf=new FPDF();
	$pdf->AddPage();
	$pdf->SetAutoPageBreak(false);
	$pdf->SetLeftMargin(LEFTMARGIN);

	$leftX = LEFTMARGIN;
	$x = $leftX;
	$y = 2 * MARGIN + ROWHEIGHT;
	$pdf = setLogoFont($pdf, true);
	$pdf->Text($x, $y, $company->companyname);
	$y += ROWHEIGHT;		
	$pdf = setLabelFont($pdf, false);
	$y += ROWHEIGHT;	
	$pdf = setNormalFont($pdf, false);
	$pdf->Text($x, $y, ccs($company->streetaddress));
	$y += ROWHEIGHT;
	$postal = "$company->zipcode $company->city";
	$companyPostal = $postal;
	$pdf->Text($x, $y, ccs("$postal"));
	$pdf = setLabelFont($pdf, false);
	$x = $leftX;

	$rightX = 120;
	$x = $rightX;
	$y = 2*MARGIN + ROWHEIGHT;	
	$pdf = setHeaderFont($pdf);
	$label = 'Faktura'; 
	$pdf->Text($x, $y, $label);
	$pdf = setLabelFont($pdf);
	$label = "Fakturanr:";
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, $label);
	$pdf = setNumericFont($pdf, true);
	$x += 25;
	$pdf->Text($x, $y, $order->no);
	$pdf = setLabelFont($pdf);
	$x = $rightX;
	$label = "Fakturadatum";
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, $label . ": ");
	$pdf = setNumericFont($pdf);
	$x += 25;
	$pdf->Text($x, $y, formatDate($order->invoicedate));
	$x = $rightX;
	$pdf = setLabelFont($pdf);
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, "Förfallodatum: ");
	$pdf = setNumericFont($pdf);
	$x += 25;
	$pdf->Text($x, $y, formatDate($order->duedate));
	$x = $rightX;
	$y += ROWHEIGHT;
	$pdf = setNormalFont($pdf);
	if (!isEmpty($order->orderedby)) {
		$y += ROWHEIGHT;
		$pdf->Text($x, $y, ccs($order->orderedby));
	}
	$customername = $customer->name;
	if ($order->customerid == CUSTOMERID_CASH)
		$customername = '';
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, ccs($customername));
	$y += ROWHEIGHT;
	$pdf->Text($x, $y, ccs($customer->streetaddress));
	$postal = "$customer->zipcode $customer->city";
	$y += ROWHEIGHT;		
	$pdf->Text($x, $y, ccs($postal));
	$x = $rightX;
	$pdf->SetX(0);
	$pdf->SetY($y+ROWHEIGHT);

	$pdf = setLabelFont($pdf);
	//$pdf->Cell(20, ROWHEIGHT, "Artikelnr", 1);
	$pdf->Cell(110, ROWHEIGHT, "Beskrivning", 1);
	$pdf->Cell(18, ROWHEIGHT, "Antal", 1, 0, $rightAlign);
	$pdf->Cell(22, ROWHEIGHT, "Apris", 1, 0, $rightAlign);
	$pdf->Cell(30, ROWHEIGHT, "Belopp", 1, 1, $rightAlign);
	while ($row = fetch($items)) {
		$pdf = setLabelFont($pdf);
		//$pdf->Cell(20, ROWHEIGHT, ccs($row->productid), 'LR');
		$pdf = setNormalFont($pdf);
		$text = $row->model;
		if ($row->comment != null)
			$text .= " - $row->comment";
		$pdf->Cell(110, ROWHEIGHT, ccs($text), 'LR');
		$pdf = setNumericFont($pdf);
		$text = $row->quantity . ' ' . $row->unittype;
		$pdf->Cell(18, ROWHEIGHT, ccs($text), 'LR', 0, 'R');
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
	$rs = query("
	select vat, sum(quantity*unitprice*vat/100) as amount
	from salesorder_item
	where orderid=$orderid
	group by vat
	having vat > 0");
	while ($row = fetch($rs)) {
		$percent = $row->vat;
		$vat = $row->amount;		
		$pdf->Cell(100, ROWHEIGHT, '', "$top");
		$pdf->Cell(20, ROWHEIGHT, '', "$top", 0, 'R');
		$pdf = setLabelFont($pdf);
		$text = "Moms " . $percent . "%"; 
		$pdf->Cell(30, ROWHEIGHT, $text, $top . 'R', 0, $rightAlign);
		$pdf = setNumericFont($pdf);
		$pdf->Cell(30, ROWHEIGHT, formatMoney($vat), $top . 'R', 1, 'R');
	}
	
	$pdf->Cell(100, ROWHEIGHT, '', '');
	$pdf->Cell(20, ROWHEIGHT, '', '', 0, 'R');
	$pdf = setLabelFont($pdf, true);
	$label = 'To pay';
	$pdf->Cell(30, ROWHEIGHT, ccs(tr($label)), '', 0, $rightAlign);
	$pdf = setNumericFont($pdf, true);
	$border = 'B';
	$pdf->Cell(30, ROWHEIGHT, formatMoney($total + $vatTotal), $border . 'RL', 1, 'R');
	$pdf->Cell(100, 2*ROWHEIGHT, '', '', 1);
	
	$pdf = setNormalFont($pdf);	
	$rs = query("select text from invoice_footer order by rowno");
	while ($row = fetch($rs)) {
		$pdf->Cell(200, ROWHEIGHT, ccs($row->text), '', 1);
	}
	
	
	$y = 270;
	$pdf->Line(MARGIN, $y, WIDTH, $y);
	$pdf->SetX(0);
	$pdf->SetY($y+2);
	$rowheight = 5;
	$pdf = setNormalFont($pdf);
	$pdf->Cell(75, $rowheight, $company->companyname, 0, 0);
	$pdf->Cell(75, $rowheight, 'Org.nr. ' . $company->registrationno, 0, 0);
	$pdf->Cell(75, $rowheight, 'Telefon ' . $company->telephoneno, 0, 0);
	$pdf->Ln();
	$pdf->Cell(75, $rowheight, ccs($company->streetaddress), 0, 0);
	$pdf->Cell(75, $rowheight, 'Momsreg.nr. ' . $company->vatnumber, 0, 0);	
	$bg = findValue("select value from company_attribute where name='BG'", null);
	if ($bg != null)
		$pdf->Cell(75, $rowheight, 'Bankgiro ' . $bg, 0, 0);
	$pdf->Ln();
	$pdf->Cell(75, $rowheight, ccs($companyPostal), 0, 0);
	$pdf->Cell(75, $rowheight, 'Vi innehar F-skattebevis', 0, 0);	
	$pg = findValue("select value from company_attribute where name='PG'", null);
	if ($bg != null)
		$pdf->Cell(75, $rowheight, 'PlusGiro ' . $pg, 0, 0);
	
	$dest = '';
	if (!isEmpty($filename)) {
		$dest = 'F';
	}
	$pdf->Output($filename, $dest);
}

?>