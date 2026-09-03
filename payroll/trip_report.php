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
$pdf->Cell(30, ROWHEIGHT, utf8ToLatin1(tr("Trip report")), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(50, ROWHEIGHT, '', null, 1);
$pdf->Cell(30, ROWHEIGHT, utf8ToLatin1(tr("Traveller").":"), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8ToLatin1("$trip->givenname $trip->surname"), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8ToLatin1(tr("Datum"). ": "), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8ToLatin1(formatInterval($trip->starttime, $trip->endtime)), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8ToLatin1(tr("Orgin").":"), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8ToLatin1($trip->orgin), null, 0);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8ToLatin1(tr("Destination"). ": "), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8ToLatin1($trip->destination), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8ToLatin1(tr("Purpose"). ":"), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8ToLatin1($trip->purpuse), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8ToLatin1(tr("Distance"). ":"), null, 0);
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8ToLatin1($trip->distance), null, 1);
$pdf->SetFont("Arial", '', 12);
$pdf->Cell(30, ROWHEIGHT, utf8ToLatin1(tr("Night allowance"). ": "), null, 0);
$text = tr($trip->night_allowance ? "Yes" : "No");
$pdf->SetFont("Times", '', 12);
$pdf->Cell(50, ROWHEIGHT, utf8ToLatin1($text), null, 1);

$pdf->Output();

?>
