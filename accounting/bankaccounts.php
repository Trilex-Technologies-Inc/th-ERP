<?php
	include('include.php');

    $name = getParam('name');
	
	$del_number = getParam('del_number');
	if (!isEmpty($del_number)) {
		sql("delete from bankaccount where number='$del_number'");
	}
	$default = findValue("select default_bankaccount from settings");
	$newDefault = getParam('default', $default);
	if ($default != $newDefault) {
		sql("update settings set default_bankaccount='$newDefault'");
		$default = $newDefault;
	}
	$payroll = findValue("select payroll_bankaccount from settings");
	$newPayroll = getParam('payroll', $payroll);
	if ($payroll != $newPayroll) {
		sql("update settings set payroll_bankaccount='$newPayroll'");
		$payroll = $newPayroll;
	}

	$selectSQL = "
	select
	    number,
	    name
	from bankaccount
	where name like '$name%'";

?>

<head>
<title>thERP - <?php etr("Bank accounts") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Bank accounts")) ?>

<form action="bankaccounts.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Name") ?>:</td><td><input type="text" name="name" value="<?php echo $name ?>"/></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form name=form1 action="bankaccounts.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Number") ?></th>
<th><?php etr("Name") ?></th>
<th><?php etr("Default") ?></th>
<th><?php etr("Payroll") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		echo "<td align=center>";
		echo deleteIcon("bankaccounts.php?del_number=$row->number");
		echo "</td>";
        echo "<td>$row->number</td>";
        echo "<td><a href='bankaccount.php?number=$row->number'>$row->name</a></td>";
		$checked = $default == $row->number ? 'checked' : '';
		echo "<td align=center><input type=radio name=default value='$row->number' $checked onClick='document.form1.submit()'/></td>";
		$checked = $payroll == $row->number ? 'checked' : '';
		echo "<td align=center><input type=radio name=payroll value='$row->number' $checked onClick='document.form1.submit()'/></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<br/>
<?php newButton("bankaccount.php") ?>
</form>
</body>
