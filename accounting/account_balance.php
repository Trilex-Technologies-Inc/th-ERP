<?php
	include('include.php');

	$accountid = getParam('accountid');
	$year = getParam('year');
	$month = getParam('month');
	if (isEmpty($month)) {
		$start = mktime(0,0,0, 1, 1, $year);
		$end = strtotime("next year", $start);
	} else {
		$start = mktime(0,0,0, $month, 1, $year);
		$end = strtotime("next month", $start);
	}


	$selectSQL =
	"select accountid,
		   name
	from account
	where accountid=$accountid
	";
	$account = find($selectSQL);

	$sql = "
	select t.transactionid, narrative, amount, unix_timestamp(transtime) as transtime
	from transaction_part tp
	join transaction t on t.transactionid=tp.transactionid
	where accountid=$accountid
	and valid = 1
	and transtime between from_unixtime($start) and from_unixtime($end)
	";
	$parts = query($sql);
?>

<head>
<title>thERP - <?php etr("Account balance") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php title("Balance > $account->name") ?>

<table>
<tr>
	<td class=label><?php etr("Accout") ?>:</td>
	<td><?php echo "$accountid - $account->name" ?></td>
</tr>
</table>
<br/>
<div class=border>
<table>
<th><?php etr("Transaction") ?></th>
<th><?php etr("Amount") ?></th>
<th><?php etr("Date") ?></th>
<?php
$class = 'odd';
$sum = 0;
while ($part = fetch($parts)) {
	echo "<tr class='$class'>";
	echo "<td><a href='transaction.php?transactionid=$part->transactionid'>$part->transactionid</a> - $part->narrative</td>";
	echo "<td align=right>";
	printf('%9.2f', $part->amount);
	echo "</td>";
	echo "<td>" . formatDate($part->transtime) . "</td>";
	echo "</tr>\n";
	$sum += $part->amount;
    $class = ($class == "odd" ? "even" : "odd");
}
?>
<tr>
<td><b><?php etr("Total") ?></b></td>
<td align=right><?php echo formatMoney($sum) ?></td>
</tr>
</table>
</div>

</body>
