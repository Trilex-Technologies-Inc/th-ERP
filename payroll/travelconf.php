<?php
	include('include.php');

	if (isSave()) {
		$carcompensation_productid = prepStringParam('carcompensation_productid');
		$perdiem_productid = prepStringParam('perdiem_productid');
		$night_productid = prepStringParam('night_productid');
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
	<div class="card border-0 shadow-sm">
		<div class="card-header bg-white py-3">
			<h2 class="h5 fw-bold mb-1"><?php etr("Travel configuration") ?></h2>
			<p class="text-secondary small mb-0"><?php etr("Map travel allowances to products") ?></p>
		</div>
		<div class="card-body p-4">
			<div class="row g-4">
				<div class="col-12 col-lg-4">
					<label class="form-label fw-semibold" for="carcompensation_productid"><?php etr("Car compensation product") ?></label>
					<?php comboBox("carcompensation_productid", $products, $row->carcompensation_productid, true) ?>
				</div>
				<div class="col-12 col-lg-4">
					<label class="form-label fw-semibold" for="perdiem_productid"><?php etr("Per diem product") ?></label>
					<?php comboBox("perdiem_productid", $products, $row->perdiem_productid, true) ?>
				</div>
				<div class="col-12 col-lg-4">
					<label class="form-label fw-semibold" for="night_productid"><?php etr("Night allowance product") ?></label>
					<?php comboBox("night_productid", $products, $row->night_productid, true) ?>
				</div>
			</div>
		</div>
		<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3">
			<?php saveButton() ?>
			<a class="btn btn-outline-secondary" href="configuration.php"><?php etr("Back") ?></a>
		</div>
	</div>
</form>
<?php bottom() ?>

</body>
