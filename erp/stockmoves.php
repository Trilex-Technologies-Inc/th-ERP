<?php
	include('include.php');

	$productid = getParam('productid');
	$locationid = getParam('locationid');
	$salesorderid = getParam('salesorderid');
	$purchaseorderid = getParam('purchaseorderid');
	$movesorderid = getParam('movesorderid');
	$productionorderid = getParam('productionorderid');
	$date = getMonthStepperDate();
	$endtime = addTime($date, TYPE_MONTHS);
	$locationSQL = '';
	if (!isEmpty($locationid)) {
		$locationSQL = " and m.locationid=$locationid ";
	}	
	$sql = "
	select
	    moveid,
		m.productid,
		diff,
		m.narrative,
		unix_timestamp(transtime) as transtime,
		p.model,
		m.transactionid,
		m.salesorderid,
		m.purchaseorderid,
		l.name as location
	from stockmove m
	join product p on p.productid=m.productid	
	join location l on l.locationid=m.locationid
	left outer join transaction t on t.transactionid=m.transactionid
	";
	if (!isEmpty($salesorderid))
		$sql .= "join salesorder so on so.orderid=m.salesorderid and so.orderid=$salesorderid ";
	if (!isEmpty($purchaseorderid))
		$sql .= "join purchaseorder po on po.orderid=m.purchaseorderid and po.orderid=$purchaseorderid ";
	if (!isEmpty($movesorderid))
		$sql .= "join movesorder po on po.orderid=m.movesorderid and po.orderid=$movesorderid ";
	if (!isEmpty($productionorderid))
		$sql .= "join productionorder pro on pro.orderid=m.productionorderid and pro.orderid=$productionorderid ";
	if (!isEmpty($productid)) {
		$sql .= "where p.productid=$productid ";
		$sql .= "and transtime between from_unixtime($date) and from_unixtime($endtime) ";
		
		$startBalance = findValue("select sum(diff)
		                           from stockmove m
		                           join transaction t on t.transactionid=m.transactionid
		                           where productid=$productid and t.transtime < from_unixtime($date) $locationSQL");
		$endBalance = findValue("select sum(diff)
		                           from stockmove m
		                           join transaction t on t.transactionid=m.transactionid
		                           where productid=$productid and t.transtime < from_unixtime($endtime) $locationSQL");
	}
	$sql .= $locationSQL;
	$sql .= "order by moveid desc";
    $rs = query($sql);

	$products = rs2array(query("select productid, model from product"));
	$locations = rs2array(query("select locationid, name from location"));	

?>

<head>
<title>thERP - <?php etr("Stock moves") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php
$title = '';
if (!isEmpty($salesorderid))
	$title = tr("Sales orders") . " > <a href='salesorder.php?orderid=$salesorderid'>$salesorderid</a> > ";
else if (!isEmpty($productid)) {
	$model = findValue("select model from product where productid=$productid");
	$title = tr("Products") . " > <a href='product.php?productid=$productid'>$model</a> > ";
}
$title .= tr("Stock moves");
title($title);
?>

<?php
	echo "<form action='stockmoves.php' method=GET>";
	echo "<div class=border>";
	if (!isEmpty($productid))
		monthStepper($date);
	echo "<center>";

	echo "<table>";

	if (!isEmpty($salesorderid)) {
		echo "<tr><td>" . tr("Sales order") . ":</td><td><a href='salesorder.php?orderid=$salesorderid'>$salesorderid</a></td></tr>";
	} else if (!isEmpty($purchaseorderid)) {
		echo "<tr><td>" . tr("Purchase order") . ":</td><td><a href='purchaseorder.php?orderid=$purchaseorderid'>$purchaseorderid</a></td></tr>";
	} else if (!isEmpty($movesorderid)) {
		echo "<tr><td>" . tr("Stock Move order") . ":</td><td><a href='goodsmove.php?orderid=$movesorderid'>$movesorderid</a></td></tr>";
	} else {
		echo "<tr><td>" . tr("Product") . ":</td>";
		echo "<td>";
		combobox('productid', $products, $productid, true);
		echo "</td>";
		echo "</tr>";
		echo "<tr><td>";
		echo tr("Location") . ":</td>";
		echo "<td>";
		combobox('locationid', $locations, $locationid, true);
		echo "</td></tr>";
		echo "<tr><td align=center colspan=2>";
		searchButton();
		echo "</td></tr>";
	}
	echo "</table>";
	echo "</center>";
	echo "</div>";
	echo "</form>";
?>
&nbsp;

<center>
<form action="stockmoves.php" method=POST>
<?php
if (!isEmpty($productid)) {
	echo "<font>" . tr("Starting quantity") . ": $startBalance</font><br><br>";
}
?>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Narrative") ?></th>
<th><?php etr("Product") ?></th>
<th><?php etr("Diff") ?></th>
<th><?php etr("Location") ?></th>
<th><?php etr("Date") ?></th>
<th><?php etr("Transaction") ?></th>
<?php
	if (!isEmpty($productid)) {
		echo "<th>" . tr("Order") . "</th>";
	}

    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		echo "<td>$row->moveid</td>";
        echo "<td>$row->narrative</td>";
		echo "<td>$row->model</td>";
		echo "<td align=right>$row->diff</td>";
		echo "<td align=right>$row->location</td>";
        echo "<td>";
       	echo formatDate($row->transtime);
        echo "</td>";
        echo "<td align=right><a href='../accounting/transaction.php?transactionid=$row->transactionid'>$row->transactionid</a></td>";
		if (!isEmpty($productid)) {
			$href = '';
			$label = '';
			if ($row->salesorderid != null)  {
				$href = "salesorder.php?orderid=$row->salesorderid";
				$label = tr("Sales order ") . $row->salesorderid;
			} else if ($row->purchaseorderid != null)  {
				$href = "purchaseorder.php?orderid=$row->purchaseorderid";
				$label = tr("Purchase order ") . $row->purchaseorderid;
			}
			echo "<td><a href='$href'>$label</a></td>";
		}
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<?php
if (!isEmpty($productid)) {
	echo "<br><font>" . tr("Final quantity") . ": $endBalance</font><br><br>";
}
?>
<br/>
</form>
</center>
<?php bottom() ?>
</body>
