<?php
	include('include.php');

    $supplierid = getParam('supplierid');
	
	$sql = "
	select
	    paymentid,
	    unix_timestamp(transtime) as paymentdate,
	    name as suppliername,
	    p.transactionid,
		amount
	from payment p
	join supplier c on c.supplierid=p.supplierid
	join transaction t on t.transactionid=p.transactionid
	where c.supplierid like '$supplierid%'";
	$sql .= " order by paymentid desc";

    $rs = query($sql);
	$suppliers = rs2array(query("select supplierid, name from supplier"));
?>

<head>
<title>thERP - <?php etr("Payments") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("purchase.php") ?>
<?php title(tr("Payments")) ?>

<form action="payments.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Supplier") ?>:</td><td><?php comboBox('supplierid', $suppliers, $supplierid, true) ?></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="payments.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Supplier") ?></th>
<th><?php etr("Date") ?></th>
<th><?php etr("Amount") ?></th>
<?php
    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
    	echo "<td align=center><input type=checkbox name='del_$i' value=1/></td>";
        echo "<td><a href='payment.php?paymentid=$row->paymentid'>$row->paymentid</a></td>";
        echo "<td>$row->suppliername</td>";
        echo "<td>" . date(DATE_PATTERN, $row->paymentdate) . "</td>";
		echo "<td>" . formatMoney($row->amount) . "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<table>
<tr>
<td><?php newButton("suppliers.php?mode=payment") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
</body>
