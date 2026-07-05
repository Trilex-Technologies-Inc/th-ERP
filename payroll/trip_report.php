<?php
include('include.php');
include('../include/report.inc.php');

$tripid = getParam('tripid');

$trip = find("
select
	t.employeeid,
	origin,
	destination,
	purpuse,
     unix_timestamp(starttime) as starttime,
     unix_timestamp(endtime) as endtime,
    distance,
    transactionid,
    night_allowance,
    givenname,
    surname
from trip t
join employee e on e.employeeid=t.employeeid
where tripid=$tripid", true);

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

$pdf->SetFont("Arial", 'B', 14);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Trip report")), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(50, ROWHEIGHT, '', null, 1);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Traveller").":"), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8_decode("$trip->givenname $trip->surname"), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Datum"). ": "), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8_decode(formatInterval($trip->starttime, $trip->endtime)), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Orgin").":"), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8_decode($trip->orgin), null, 0);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Destination"). ": "), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8_decode($trip->destination), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Purpose"). ":"), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8_decode($trip->purpuse), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Distance"). ":"), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8_decode($trip->distance), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8_decode(tr("Night allowance"). ": "), null, 0);
$text = tr($trip->night_allowance ? "Yes" : "No");
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8_decode($text), null, 1);

$pdf->Output();

?>
