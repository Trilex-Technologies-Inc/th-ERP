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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Car compensation product") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("carcompensation_productid", $products, $row->carcompensation_productid, true) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Per diem product") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("perdiem_productid", $products, $row->perdiem_productid, true) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Night allowance product") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("night_productid", $products, $row->night_productid, true) ?></div>
</div>
</div>
<?php saveButton() ?>
</form>
<?php bottom() ?>

</body>
