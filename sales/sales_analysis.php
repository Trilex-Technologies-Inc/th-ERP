<?php
	include('include.php');

    $model = getParam('model');
    $locationid = getParam('locationid');
	$type = getParam('type', TYPE_MONTHS);
	if ($type == TYPE_MONTHS)
		$start = getMonthStepperDate();
	else
		$start = getYearStepperDate();
	$end = addTime($start, $type);

	$locationSQL = "1=1";
	if (!isEmpty($locationid)) {
		$locationSQL = "locationid=$locationid";
	}
	$selectSQL = "
	select
	    p.productid,
	    model,
	    (select sum(soi.quantity)
	     from salesorder_item soi
	     join salesorder so on so.orderid=soi.orderid and orderdate >= from_unixtime($start) and orderdate < from_unixtime($end)
	     where soi.productid=p.productid and $locationSQL) as quantity,
	    (select sum(soi.quantity*soi.unitprice)
	     from salesorder_item soi
	     join salesorder so on so.orderid=soi.orderid and orderdate >= from_unixtime($start) and orderdate < from_unixtime($end)
	     where soi.productid=p.productid and $locationSQL) as revenue
	     from product p
	where model like '$model%'
	and productid not in (" . PRODUCTID_UNSPECIFIED . ", " . PRODUCTID_ROUNDING . ")
	";
	
	$locations = rs2array(query("select locationid, name from location"));	

?>

<head>
<title>thERP - <?php etr("Sales analysis") ?></title>
<?php
styleSheet();
?>
</head>

<body>

<?php menubar('index.php') ?>
<?php title(tr("Sales analysis")) ?>

<form name=searchform action="sales_analysis.php" method="GET">
<div class="border">
<center>
<table>
<tr>
<td><?php etr("Product") ?>:</td><td><?php textbox('model', $model) ?></td><td width=20/>
<td><?php etr("Location") ?>:</td><td><?php combobox('locationid', $locations, $locationid, true) ?></td>
<td><?php searchButton() ?></td>
</tr>
</table>
<?php 
$yearsChecked = '';
$monthsChecked = '';
if ($type == TYPE_YEARS) {
	yearStepper($start);
	$yearsChecked = 'checked';
} else {
	monthStepper($start); 
	$monthsChecked = 'checked';
}
echo "<input type=radio name=type value='" . TYPE_YEARS . "' $yearsChecked onClick='document.searchform.submit()'>" . tr("Years") . "</input>";
echo "<input type=radio name=type value='" . TYPE_MONTHS . "' $monthsChecked onClick='document.searchform.submit()'>" . tr("Months") . "</input>";
?>
</center>
</div>
</form>
&nbsp;

<center>
<input type=hidden name=orderid value='<?php echo $orderid ?>'/>
<table>
<th><?php etr("Productno") ?></th>
<th><?php etr("Product") ?></th>
<th><?php etr("Quantity") ?></th>
<th><?php etr("Revenue") ?></th>

<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
    	$href = "product.php?productid=$row->productid";
        echo "<tr class='$class'>";
        echo "<td>$row->productid</td>";
        echo "<td><a href='$href'>$row->model</a></td>";
		$href = "sales.php?productid=$row->productid&starttime=$start&endtime=$end";
        echo "<td align=right><a href='$href'>$row->quantity</a></td>";
        echo "<td align=right><a href='$href'>" . formatMoney($row->revenue) . "</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
</center>
<?php bottom() ?>
</body>
