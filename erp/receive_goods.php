<?php
	include('include.php');

	$suppliers = rs2array(query("select supplierid, name from supplier"));
?>
<head>
<title>thERP - <?php etr("Receive goods") ?></title>
<LINK REL=StyleSheet HREF="therp.css" TYPE="text/css">
</head>

<body>
<?php include("menubar.php") ?>
<?php title("<a href='transactions.php'>" . tr("Transactions") . "</a>") ?>

<form action="receive_goods.php" method="POST">
<input type=hidden name=transactionid value='<?php echo $transactionid ?>'/>
<table>
<tr><td class=label><?php etr("Id") ?>:</td><td><?php echo $transactionid ?></td>
<tr><td class=label><?php etr("Narrative") ?>:</td>
<td>
<input type=text name='narrative' value='<?php echo $narrative ?>'/>
</td>
<tr><td class=label><?php etr("Time") ?>:</td><td><?php echo formatDate($transtime) ?></td></tr>
</table>
<br/>
<?php
if ($parts != null) {
	echo "<div class=border>";
	echo "<table>";
	echo "<th>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Account") . "</th>";
	echo "<th>" . tr("Amount") . "</th>";
	$class = 'odd';
	$i = 0;
	while ($part = fetch($parts)) {
		echo "<input type=hidden name='accountid_$i' value='$part->accountid'/>";
		echo "<tr class='$class'>";
		echo "<td align=center>";
		echo deleteIcon("register_transaction.php?transactionid=$transactionid&del_accountid=$part->accountid");
		echo "</td>";
		echo "<td>$part->accountid - $part->name</td>";
		echo "<td align=right>";
		echo "<input type=text name=amount_$i value='$part->amount'/>";
		echo "</td>";
		echo "</tr>\n";
	    $class = ($class == "odd" ? "even" : "odd");
		$i++;
	}
	echo "<input type=hidden name=count value='$i'/>";
	echo "<tr class='$class'>";
	echo "<td/>";
	echo "<td>";
	comboBox("accountid_new", $accounts, null, false);
	echo "</td>";
	echo "<td><input type=text name='amount_new'/></td>";
	echo "</tr>";
	echo "</table>";
	echo "</div>";	
}
?>
<br/>
<?php 
$label = $new ? "Next" : "Save";
button($label, "save") 
?>
<input type=hidden name=new value='<?php echo $new ?>'/>
</form>

</body>
