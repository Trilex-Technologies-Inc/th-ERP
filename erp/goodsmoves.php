<?php
	include('include.php');

    $locationid = getParam('locationid');
    $toid = getParam('toid');
	$mode = getParam('mode');

	$del_orderid = getParam('del_orderid');
	if (!isEmpty($del_orderid)) {
		sql("delete from movesorder_item where orderid=$del_orderid");
		sql("delete from movesorder where orderid=$del_orderid");
	}
	
	$sql = "
	select so.orderid,
	    unix_timestamp(so.orderdate) as orderdate,
		so.cancelled,
		so.sent,
		so.received,
	    c.name as locationname,
	    t.name as descname
	from movesorder so
	join location c on c.locationid=so.locationid
	join location t on t.locationid=so.toid
	where so.locationid like '$locationid%' and so.toid like '$toid%'
	group by orderid	
	order by orderid desc
	";

    $rs = query($sql);
	$locations = rs2array(query("select locationid, name from location"));
?>

<head>
<title>thERP - <?php etr("Stock move order") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('purchase.php') ?>
<?php 
$title = "Stock move order";
if ($mode == 'select')
	$title = "Select Stock move order";
title(tr($title)) 
?>

<form action="goodsmoves.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("From Location") ?>:</div><div class="col-12 col-md-auto"><?php comboBox('locationid', $locations, $locationid, true) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("To Location") ?>:</div><div class="col-12 col-md-auto"><?php comboBox('toid', $locations, $toid, true) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div></div>

</div>
</div>
</form>
&nbsp;

<form action="goodsmove.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("From Location") ?></th>
<th><?php etr("To Location") ?></th>
<th><?php etr("Order date") ?></th>
<th><?php etr("Sent") ?></th>
<th><?php etr("Received") ?></th>
<th><?php etr("Cancelled") ?></th>
<?php
    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		deleteColumn("goodsmoves.php?del_orderid=$row->orderid");
        echo "<td><a href='goodsmove.php?orderid=$row->orderid'>$row->orderid</a></td>";
        echo "<td>$row->locationname</td>";
        echo "<td>$row->descname</td>";
        echo "<td>" . date(DATE_PATTERN, $row->orderdate) . "</td>";
		echo "<td align=center>" .($row->sent == '1' ? 'X' : ''). "</td>";
		echo "<td align=center>" .($row->received == '1' ? 'X' : ''). "</td>";
		echo "<td align=center>" .($row->cancelled == '1' ? 'X' : ''). "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<br/>
<?php newButton("goodsmove.php?toid=&action=create") ?>
&nbsp;
</form>
<?php bottom() ?>	
</body>
