<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);
	
    $description = getParam('description');

	$del_accountid = getParam('del_accountid');
	if (!isEmpty($del_accountid)) {
		sql("delete from payaccount_group where accountid=$del_accountid");
		sql("delete from payaccount where accountid=$del_accountid");
	}

	$selectSQL = "
	select
	    a.accountid,
	    description
	from payaccount a
	where description like '$description%'";

?>

<head>
<title>Payroll - <?php etr("Pay accounts") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Pay accounts")) ?>

<form action="payaccounts.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $description ?>"/></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="payaccounts.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Description") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		echo "<td align=center>";
		echo deleteIcon("payaccounts.php?del_accountid=$row->accountid");
		echo "</td>";
        echo "<td align=right>$row->accountid</td>";
        echo "<td><a href='payaccount.php?accountid=$row->accountid'>$row->description</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php newButton("payaccount.php") ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
	