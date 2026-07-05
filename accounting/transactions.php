<?php
	include('include.php');

	$starttime = parseDate(getParam('starttime'));
	if (isEmpty($starttime))
		$starttime = roundTime(time(), TYPE_MONTHS);
	$endtime = parseDate(getParam('endtime'));
	if (isEmpty($endtime))
		$endtime = addTime($starttime, TYPE_MONTHS);

	$narrative = getParam('narrative');
	$sql = "
	select
	    transactionid,
	    unix_timestamp(transtime) as transtime,
		narrative
	from transaction
	where narrative like '$narrative%'
	and transtime between from_unixtime($starttime) and from_unixtime($endtime)
	and valid = 1
	order by transactionid desc
	";
    $rs = query($sql);


?>

<head>
<title>thERP - <?php etr("Transactions") ?></title>
<?php
styleSheet();
include_datebox();
?>
</head>

<body>

<?php menubar("transactions.php") ?>
<br>
<form action="transactions.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Narrative") ?>:</td><td><?php textbox("narrative", $narrative) ?></td>
<tr>
	<td><?php etr("Interval") ?>:</td>
	<td><?php datebox("starttime", formatDate($starttime)) ?></td>
	<td><?php datebox("endtime", formatDate($endtime)) ?></td>
</tr>
<tr>
<td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td>
</tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="transactions.php" method=POST>
<table width='100%'>
<th><?php etr("Id") ?></th>
<th><?php etr("Narrative") ?></th>
<th><?php etr("Date") ?></th>
<?php
    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td align=right><a href='transaction.php?transactionid=$row->transactionid'>$row->transactionid</a></td>";
        echo "<td><a href='transaction.php?transactionid=$row->transactionid'>$row->narrative</a></td>";
        echo "<td align=center>" . date(DATE_PATTERN, $row->transtime) . "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<br/>
<?php
newButton("register_transaction.php");
echo "&nbsp;&nbsp;";
$params = "starttime=$starttime&endtime=$endtime&narrative=$narrative";
button("Print", "print", "trans_report.php?$params");
?>
</form>
<?php bottom() ?>
</body>
