<?php
	include('include.php');

    $customerid = getParam('customerid');
	
	$sql = "
	select
	    receiptid,
	    unix_timestamp(transtime) as receiptdate,
	    name as customername,
	    p.transactionid,
		amount
	from receipt p
	join customer c on c.customerid=p.customerid
	join transaction t on t.transactionid=p.transactionid
	where c.customerid like '$customerid%'";
	$sql .= " order by receiptid desc";

    $rs = query($sql);
	$customers = rs2array(query("select customerid, name from customer"));
?>

<head>
<title>thERP - <?php etr("Receipts") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("index.php") ?>
<?php title(tr("Receipts")) ?>

<form action="receipts.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Customer") ?>:</td><td><?php comboBox('customerid', $customers, $customerid, true) ?></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="receipts.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Customer") ?></th>
<th><?php etr("Date") ?></th>
<th><?php etr("Amount") ?></th>
<?php
    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
    	echo "<td align=center><input type=checkbox name='del_$i' value=1/></td>";
        echo "<td><a href='receipt.php?receiptid=$row->receiptid'>$row->receiptid</a></td>";
        echo "<td>$row->customername</td>";
        echo "<td>" . date(DATE_PATTERN, $row->receiptdate) . "</td>";
		echo "<td align=right>" . formatMoney($row->amount) . "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<table>
<tr>
<td><?php newButton("customers.php?mode=receipt") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
