<?php
include('include.php');
include('../include/report.inc.php');

$starttime = parseDate(getParam('starttime'));
if (isEmpty($starttime))
	$starttime = roundTime(time(), TYPE_MONTHS);
$endtime = parseDate(getParam('endtime'));
if (isEmpty($endtime))
	$endtime = addTime($starttime, TYPE_MONTHS);

$narrative = getParam('narrative');
$sql = "
select
	t.transactionid,
	unix_timestamp(transtime) as transtime,
	narrative,
	tp.accountid,
	amount,
	name
from transaction t
join transaction_part tp on tp.transactionid=t.transactionid
join account a on a.accountid=tp.accountid
where narrative like '$narrative%'
and transtime between from_unixtime($starttime) and from_unixtime($endtime)
and valid = 1
order by transactionid desc
";
$rs = query($sql);

class ThisPDF extends MyPDF
{
	function Header()
	{
	}
}

$pdf = new ThisPDF();
$pdf->SetAutoPageBreak(false);
$pdf->setFont('Arial', '', 10);
$pdf->AddPage();

$lastTransid = null;
while ($row = fetch($rs)) {
	if ($row->transactionid != $lastTransid) {
		$pdf->KeepTogether_end();
		$pdf->KeepTogether_begin();
		$pdf->setFont('Arial', 'B', 10);
		$pdf->Cell(25, ROWHEIGHT, $row->transactionid, 'T', 0);
		$pdf->Cell(80, ROWHEIGHT, utf8_decode($row->narrative), 'T', 0);
		$pdf->Cell(40, ROWHEIGHT, formatDate($row->transtime), 'T', 0, 'R');
		$pdf->Cell(40, ROWHEIGHT, $row->createdby, 'T', 1, 'R');
		$lastTransid = $row->transactionid;
	}
	$pdf->setFont('Arial', '', 10);
	$pdf->Cell(10, ROWHEIGHT, '', 0, 0);
	$pdf->Cell(15, ROWHEIGHT, $row->accountid, 0, 0);
	$pdf->Cell(80, ROWHEIGHT, utf8_decode($row->name), 0, 0);
	$pdf->setFont('Courier', '', 10);
	$pdf->Cell(40, ROWHEIGHT, $row->amount, 0, 1, 'R');
}
$pdf->KeepTogether_end();

$pdf->Output();

?>
