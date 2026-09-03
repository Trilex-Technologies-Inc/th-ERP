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
<div class="card border-0 shadow-sm mb-3"><div class="card-body">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Name") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="name" value="<?php echo $name ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div></div>

</div></div></div>
</form>
<form name=form1 action="bankaccounts.php" method=POST>
<div class="card border-0 shadow-sm mb-3">
<div class="card-header bg-body-tertiary"><div class="row fw-semibold align-items-center"><div class="col-2"><?php etr("Delete") ?></div><div class="col-2"><?php etr("Number") ?></div><div class="col-4"><?php etr("Name") ?></div><div class="col-2 text-center"><?php etr("Default") ?></div><div class="col-2 text-center"><?php etr("Payroll") ?></div></div></div>
<div class="list-group list-group-flush">
<?php
    $rs = query($selectSQL);
    while ($row = fetch_object($rs)) {
        echo "<div class='list-group-item'><div class='row align-items-center'><div class='col-2'>";
		echo deleteIcon("bankaccounts.php?del_number=$row->number");
        echo "</div><div class='col-2'>$row->number</div>";
        echo "<div class='col-4'><a href='bankaccount.php?number=$row->number'>$row->name</a></div>";
		$checked = $default == $row->number ? 'checked' : '';
		echo "<div class='col-2 text-center'><input class='form-check-input' type=radio name=default value='$row->number' $checked onClick='document.form1.submit()'/></div>";
		$checked = $payroll == $row->number ? 'checked' : '';
		echo "<div class='col-2 text-center'><input class='form-check-input' type=radio name=payroll value='$row->number' $checked onClick='document.form1.submit()'/></div>";
        echo "</div></div>";
    }
?>
</div></div>
<br/>
<?php newButton("bankaccount.php") ?>
</form>
</body>
