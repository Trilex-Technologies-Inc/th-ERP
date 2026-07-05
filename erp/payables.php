<?php
	include('include.php');

    $supplierid = getParam('supplierid');
	$unpaid = getParam('unpaid');
	$overdue = getParam('overdue');

	$sql = "
	select
	    p.payableid,
	    unix_timestamp(transtime) as payabledate,
	    name as suppliername,
	    p.transactionid,
		p.amount,
		vat,
		sum(pa.amount) as payed,
		unix_timestamp(duedate) as duedate
	from payable p
	join supplier c on c.supplierid=p.supplierid
	join transaction t on t.transactionid=p.transactionid
	left outer join payment_allocation pa on pa.payableid=p.payableid
	where c.supplierid like '$supplierid%'";
	if ($overdue)
		$sql .= " and duedate < now() ";
	$sql .= "
	group by payableid, payabledate, suppliername, p.transactionid, p.amount, vat
	";
	if ($unpaid)
		$sql .= " having payed < p.amount + vat or payed is null ";
	$sql .= " order by payableid desc";

    $rs = query($sql);
	$suppliers = rs2array(query("select supplierid, name from supplier"));
?>

<head>
<title>thERP - <?php etr("Payables") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('purchase.php') ?>
<?php title(tr("Payables")) ?>

<form action="payables.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Supplier") ?>:</td><td><?php comboBox('supplierid', $suppliers, $supplierid, true) ?></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="payables.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("supplier") ?></th>
<th><?php etr("Registered") ?></th>
<th><?php etr("Due date") ?></th>
<th><?php etr("Amount") ?></th>
<th><?php etr("VAT") ?></th>
<th><?php etr("Payed") ?></th>
<?php
    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
    	echo "<td align=center><input type=checkbox name='del_$i' value=1/></td>";
        echo "<td><a href='payable.php?payableid=$row->payableid'>$row->payableid</a></td>";
        echo "<td>$row->suppliername</td>";
        echo "<td>" . formatDate($row->payabledate) . "</td>";
        echo "<td>" . formatDate($row->duedate) . "</td>";
        echo "<td align=right>" . formatMoney($row->amount) . "</td>";
        echo "<td align=right>" . formatMoney($row->vat) . "</td>";
        echo "<td align=right>" . formatMoney($row->payed) . "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<br/>
<?php newButton("suppliers.php?mode=payable") ?>
&nbsp;
<?php saveButton() ?>
</form>
<?php bottom() ?>
</body>
