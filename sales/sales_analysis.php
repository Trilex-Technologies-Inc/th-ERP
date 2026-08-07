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
<div class="border p-3 mb-4">
	<div class="row g-3 align-items-end justify-content-center">
		<div class="col-md-4">
			<label class="form-label"><?php etr("Product") ?></label>
			<?php textbox('model', $model) ?>
		</div>
		<div class="col-md-4">
			<label class="form-label"><?php etr("Location") ?></label>
			<?php combobox('locationid', $locations, $locationid, true) ?>
		</div>
		<div class="col-auto">
			<?php searchButton() ?>
		</div>
	</div>
	<div class="mt-3 d-flex flex-wrap gap-3 align-items-center justify-content-center">
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
echo "<div class='form-check'><input class='form-check-input' type='radio' name='type' value='" . TYPE_YEARS . "' $yearsChecked onClick='document.searchform.submit()'><label class='form-check-label'>" . tr("Years") . "</label></div>";
echo "<div class='form-check'><input class='form-check-input' type='radio' name='type' value='" . TYPE_MONTHS . "' $monthsChecked onClick='document.searchform.submit()'><label class='form-check-label'>" . tr("Months") . "</label></div>";
?>
	</div>
</div>
</form>
&nbsp;

<div class="table-responsive">
<table class="table table-sm table-striped table-hover align-middle w-100">
<thead>
<tr>
<th><?php etr("Productno") ?></th>
<th><?php etr("Product") ?></th>
<th class="text-end"><?php etr("Quantity") ?></th>
<th class="text-end"><?php etr("Revenue") ?></th>
</tr>
</thead>
<tbody>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
    	$href = "product.php?productid=$row->productid";
        echo "<tr class='$class'>";
        echo "<td>$row->productid</td>";
        echo "<td><a href='$href'>$row->model</a></td>";
		$href = "sales.php?productid=$row->productid&starttime=$start&endtime=$end";
        echo "<td class='text-end'><a href='$href'>$row->quantity</a></td>";
        echo "<td class='text-end'><a href='$href'>" . formatMoney($row->revenue) . "</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</tbody>
</table>
</div>
<?php bottom() ?>
</body>
