<?php
include('../include/fpdf/fpdf.php');
include('include.php');

$employeeid0 = getParam("employeeid");
$selfservice = false;
if ($employeeid0 == 'current') {
	checkPermission(PERMISSION_SELF_SERVICE);
	$employeeid = getCurrentEmployee();
	$selfservice = true;
} else {
	checkPermission(PERMISSION_ADMINISTRATE_EMPLOYEES);
	$employeeid = $employeeid0;
}
$periodid = getParam('periodid');

$emp = find("select
               givenname,
			   surname,
			   street_address,
			   zipcode,
			   city
   	         from employee
			 where employeeid=$employeeid");
$rows = query("select
                  a.accountid,
				  description,
				  quantity,
				  inputtype,
				  unit_price,
				  amount
			   from payevent pe
			   join payaccount a on a.accountid=pe.accountid
			   where employeeid=$employeeid and periodid=$periodid
			   order by calcseq
			");

$period = find("select unix_timestamp(starttime) as starttime,
                       unix_timestamp(endtime) as endtime
				from payperiod
				where periodid=$periodid");
$netPay = findValue("select sum(amount)
                     from payevent pe
					 join payaccount_group g on g.accountid=pe.accountid and g.groupid=" . GROUPID_PAYABLE . "
					 where employeeid=$employeeid and periodid=$periodid");

define('MARGIN', 5);
define('WIDTH', 205);
define('HEIGHT', 290);
define('ROWHEIGHT', 6);

$pdf=new FPDF();
$pdf->AddPage();
$pdf->Line(MARGIN,MARGIN, MARGIN, HEIGHT);
$pdf->Line(MARGIN,HEIGHT, WIDTH, HEIGHT);
$pdf->Line(WIDTH,HEIGHT, WIDTH, MARGIN);
$pdf->Line(WIDTH,MARGIN, MARGIN, MARGIN);


$pdf->SetFont('Times','B',14);
$pdf->Cell(110, ROWHEIGHT, '', 0, 0);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(100, ROWHEIGHT, tr('Pay slip'), 0, 1);
$pdf->SetFont('Times','',12);
$pdf->Cell(110, ROWHEIGHT, '', 0, 0);
$pdf->Cell(100, ROWHEIGHT, formatDate($period->starttime) . ' - ' . formatDate($period->endtime), 0, 1);
$pdf->Cell(110, ROWHEIGHT, '', 0, 0);
$pdf->Cell(100, ROWHEIGHT, '', 0, 1);
$pdf->Cell(110, ROWHEIGHT, '', 0, 0);
$pdf->Cell(100, ROWHEIGHT, '', 0, 1);
$pdf->Cell(110, ROWHEIGHT, '', 0, 0);
$pdf->Cell(100, ROWHEIGHT, utf8_decode($emp->givenname . ' ' . $emp->surname), 0, 1);
$pdf->Cell(110, ROWHEIGHT, '', 0, 0);
$pdf->Cell(100, ROWHEIGHT, utf8_decode($emp->street_address), 0, 1);
$pdf->Cell(110, ROWHEIGHT, '', 0, 0);
$address = utf8_decode("$emp->city $emp->zipcode");
$pdf->Cell(100, ROWHEIGHT, $address, 0, 1);
$pdf->Ln(ROWHEIGHT);

$pdf->SetFont('Arial','',10);
$pdf->Cell(20, ROWHEIGHT, utf8_decode(tr("Account")), 1);
$pdf->Cell(80, ROWHEIGHT, utf8_decode(tr("Description")), 1);
$pdf->Cell(15, ROWHEIGHT, utf8_decode(tr("Quantity")), 1, 0, 'R');
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Unit price")), 1, 0, 'R');
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Amount")), 1, 1, 'R');
while ($row = fetch($rows)) {
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(20, ROWHEIGHT, $row->accountid, 'L');
	$pdf->Cell(80, ROWHEIGHT, utf8_decode($row->description), 'LR');
	$pdf->SetFont('Courier','',10);
	$pdf->Cell(15, ROWHEIGHT, formatQuantity($row->quantity, $row->inputtype), 'LR', 0, 'R');
	$pdf->Cell(30, ROWHEIGHT, formatMoney($row->unit_price), 'LR', 0, 'R');
	$pdf->Cell(30, ROWHEIGHT, formatMoney($row->amount), 'LR', 1, 'R');
}
$pdf->SetFont('Arial','',10);
$pdf->Cell(115, ROWHEIGHT, '', 'T');
$pdf->SetFont('Arial','',10);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr('To pay')), 'TLRB', 0, 'R');
$pdf->SetFont('Courier','',10);
$pdf->Cell(30, ROWHEIGHT, formatMoney($netPay), 'TRB', 1, 'R');

$pdf->Output();
?>