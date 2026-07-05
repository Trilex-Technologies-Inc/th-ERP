<?php
	include('include.php');

	if (isSave()) {
		$carcompensation_productid = prepNull(getParam('carcompensation_productid'));
		$perdiem_productid = prepNull(getParam('perdiem_productid'));
		$night_productid = prepNull(getParam('night_productid'));
		$sql = "update travelconf set
		        carcompensation_productid=$carcompensation_productid,
		        perdiem_productid=$perdiem_productid,
		        night_productid=$night_productid
		        ";
		sql($sql);
	}

	$sql = "
	select 
		carcompensation_productid,
		perdiem_productid,		
		night_productid		
	from travelconf
	";
	$row = find($sql);

	$products = rs2array(query("select productid, model from product"));

?>
<?php head("Travel configuration") ?>

<body>
<?php top("configuration.php", "Travel configration") ?>

<form action="travelconf.php" method="POST">
<table>
<tr>
	<td><?php etr("Car compensation product") ?>:</td>
	<td><?php comboBox("carcompensation_productid", $products, $row->carcompensation_productid, true) ?></td>
</tr>
<tr>
	<td><?php etr("Per diem product") ?>:</td>
	<td><?php comboBox("perdiem_productid", $products, $row->perdiem_productid, true) ?></td>
</tr>
<tr>
	<td><?php etr("Night allowance product") ?>:</td>
	<td><?php comboBox("night_productid", $products, $row->night_productid, true) ?></td>
</tr>
</table>
<?php saveButton() ?>
</form>
<?php bottom() ?>

</body>
