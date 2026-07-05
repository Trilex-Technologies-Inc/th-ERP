<?php
	include('include.php');

	$starttime = parseDate(getParam('starttime'));
	if (isEmpty($starttime))
		$starttime = roundTime(time(), TYPE_MONTHS);
	$endtime = parseDate(getParam('endtime'));
	if (isEmpty($endtime))
		$endtime = addTime($starttime, TYPE_MONTHS);

	$sql = "
	select so.orderid,
	    unix_timestamp(createdtime) as createdtime,
	    transactionid
	from productionorder so
	left outer join productionorder_item si on si.orderid=so.orderid
	left outer join product p on p.productid=si.productid
	where createdtime between from_unixtime($starttime) and from_unixtime($endtime)
	";
	$sql .= "order by orderid desc";

    $rs = query($sql);
?>

<head>
<title>thERP - <?php etr("Production orders") ?></title>
<?php
styleSheet();
include_datebox();
?>
</head>

<body>

<?php menubar("productionorders.php") ?>
<?php title(tr("Production orders")) ?>

<form action="productionorders.php" method="GET">
<div class="border">
<table>
<tr>
	<td><?php etr("Interval") ?>:</td>
	<td><?php datebox("starttime", formatDate($starttime)) ?></td>
	<td><?php datebox("endtime", formatDate($endtime)) ?></td>
</tr>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="productionorders.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Date") ?></th>
<th><?php etr("Finished") ?></th>
<?php
    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
    	echo "<td align=center><input type=checkbox name='del_$i' value=1/></td>";
        echo "<td><a href='productionorder.php?orderid=$row->orderid'>$row->orderid</a></td>";
        echo "<td>" . date(DATE_PATTERN, $row->createdtime) . "</td>";
        if (isEmpty($row->transactionid))
        	echo "<td/>";
        else
        	echo "<td align=center>X</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<br/>
<?php newButton("productionorder.php?action=create") ?>
&nbsp;
<?php saveButton() ?>
</form>
<?php bottom() ?>
</body>
