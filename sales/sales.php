<?php
	include('include.php');
	include('salesorder.inc.php');

    $customerid = getParam('customerid');
    $unpaid = getParam('unpaid');
	$overdue = getParam('overdue');
	$uninvoiced = getParam('uninvoiced');
	$productid = getParam('productid');
	$credit_orgid = getParam('credit_orgid');

	$starttime = parseDate(getParam('starttime'));
	if (isEmpty($starttime))
		$starttime = roundTime(time(), TYPE_MONTHS);
	$endtime = parseDate(getParam('endtime'));
	if (isEmpty($endtime))
		$endtime = addTime($starttime, TYPE_MONTHS);
		
	$del_orderid = getParam("del_orderid");
	if (!isEmpty($del_orderid)) {
		cancel_order($del_orderid);
	}

	$sql = "
	select 
		so.orderid,
		so.no,
	    unix_timestamp(orderdate) as orderdate,
	    name as customername,
	    invoice_transid,
  	    sum(si.quantity*unitprice+vat) as total,
	    sum(pa.amount) as allocated,
		rso.orderid as recur,
		credit_orgid
	from salesorder so
	join customer c on c.customerid=so.customerid
	join salesorder_item si on si.orderid=so.orderid
	left outer join product p on p.productid=si.productid
	left outer join receipt_allocation pa on pa.orderid=so.orderid
	left outer join recur_salesorder rso on rso.orderid=so.orderid
	where so.customerid like '$customerid%'
	and orderdate between from_unixtime($starttime) and from_unixtime($endtime)
	and cancelled=0
	";
	if ($unpaid)
		$sql .= " and invoice_transid is not null ";
	if ($uninvoiced)
		$sql .= " and invoice_transid is null ";
	if ($overdue)
		$sql .= " and duedate < now() ";
	if (!isEmpty($productid)) {
		$sql .= " and exists (select * from salesorder_item soi2 where soi2.orderid=so.orderid and soi2.productid=$productid) ";
	}
	if (!isEmpty($credit_orgid)) 
		$sql .= " and credit_orgid=$credit_orgid ";
	$sql .= "group by orderid ";
	if ($unpaid)
		$sql .= " having total > 0 and (total > allocated or allocated is null) ";
	$sql .= "order by orderid desc";

    $rs = query($sql);
	$customers = rs2array(query("select customerid, name from customer"));
?>

<head>
<title>thERP - <?php etr("Sales") ?></title>
<?php
styleSheet();
include_datebox();
?>
</head>

<body>

<?php 
menubar('index.php');
?>
<br>
<form action="sales.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Customer") ?>:</td><td><?php comboBox('customerid', $customers, $customerid, true) ?></td>
<tr>
	<td><?php etr("Only show") ?>:</td>
	<td>
		<?php checkbox('uninvoiced', $uninvoiced, 'Not invoiced') ?>
		<?php checkbox('unpaid', $unpaid, 'Unpaid') ?>
		<?php checkbox('overdue', $overdue, 'Overdue') ?>
	</td>
</tr>
<tr>
	<td><?php etr("Interval") ?>:</td>
	<td>
		<?php datebox("starttime", formatDate($starttime)) ?>
		<?php datebox("endtime", formatDate($endtime)) ?>
	</td>
</tr>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>

<form action="sales.php" method=POST>
<table width="100%">
<th><?php etr("Cancel") ?></th>
<th><?php etr("No") ?></th>
<th width='50%'><?php etr("Customer") ?></th>
<th><?php etr("Order date") ?></th>
<th><?php etr("Invoiced") ?></th>
<th><?php etr("Payed") ?></th>
<th><?php etr("Recurring") ?></th>
<?php
    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		deleteColumn("sales.php?del_orderid=$row->orderid");
    	$script = "salesorder.php";
    	if ($row->credit_orgid != null)
    		$script = "credit_salesorder.php";
    	$href = "$script?orderid=$row->orderid";    	
        echo "<td align=right><a href='$href'>";
        if ($row->no == null)
        	printf("(%06d)", $row->orderid);
        else
        	printf("%06d", $row->no);
        echo "</a></td>";
        echo "<td>$row->customername</td>";
        echo "<td align=center>" . date(DATE_PATTERN, $row->orderdate) . "</td>";
        if (isEmpty($row->invoice_transid))
        	echo "<td/>";
        else
        	echo "<td align=center>X</td>";
        if ($row->total > $row->allocated || $row->total == 0)
        	echo "<td/>";
        else
        	echo "<td align=center>X</td>";
		echo "<td align=center>";
		echo $row->recur != null ? 'X' : '';
		echo "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<br/>
<?php newButton("customers.php?mode=createorder") ?>
</form>
<?php bottom() ?>
</body>
