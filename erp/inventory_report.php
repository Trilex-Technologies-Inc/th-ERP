<?php
include('include.php');
include('../include/fpdf/fpdf.php');
define('HEIGHT', 6);

function ccs($str)
{
	return utf8_decode($str);	
}

class MyFPDF extends FPDF
{
	function Header()
	{
		$this->Cell(20, HEIGHT, ccs(tr("Productno")), 'B', 0);
		$this->Cell(40, HEIGHT, ccs(tr("Product")), 'B', 0);
		$this->Cell(15, HEIGHT, ccs(tr("Quantity")), 'B', 0, 'R');
		$this->Cell(30, HEIGHT, ccs(tr("Ordered qty, sales")), 'B', 0, 'R');
		$this->Cell(30, HEIGHT, ccs(tr("Ordered qty, purchase")), 'B', 1, 'R');			
	}
}

$pdf = new MyFPDF();
$pdf->setFont('Times', '', 10);
$pdf->AddPage();

$model = getParam('model');
$locationid = getParam('locationid');
$supplierid = getParam('supplierid');

$locationSQL = '';
if (!isEmpty($locationid))
	$locationSQL = " and locationid=$locationid ";
$selectSQL = "
select
    p.productid,
    model,
    (select sum(diff) from stockmove m where m.productid=p.productid $locationSQL) as quantity,
    (select sum(soi.quantity)
     from salesorder_item soi
     join salesorder so on so.orderid=soi.orderid and so.invoice_transid is null
     where soi.productid=p.productid $locationSQL) as so_quantity,
    (select sum(poi.quantity-poi.received_quantity)
     from purchaseorder_item poi
     join purchaseorder po on po.orderid=poi.orderid
     where poi.productid=p.productid $locationSQL) as po_quantity
from product p ";
if (!isEmpty($supplierid)) {
	$selectSQL .= " join supplier_price sp ";
	$selectSQL .= " on sp.productid=p.productid and supplierid=$supplierid ";
}
$selectSQL .= "
where model like '$model%'
and active=1
";
//echo "<pre>$selectSQL</pre>";

$rs = query($selectSQL);
while ($row = fetch($rs)) {
	$pdf->setFont('Times', '', 10);
	$pdf->Cell(20, HEIGHT, $row->productid, 0, 0);
	$pdf->Cell(40, HEIGHT, ccs($row->model), 0, 0);
	$pdf->setFont('Courier', '', 10);
	$pdf->Cell(15, HEIGHT, $row->quantity, 0, 0, 'R');
	$pdf->Cell(30, HEIGHT, $row->so_quantity, 0, 0, 'R');
	$pdf->Cell(30, HEIGHT, $row->po_quantity, 0, 1, 'R');	
}
$pdf->Output();

?>