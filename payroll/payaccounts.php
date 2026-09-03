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

<form action="payaccounts.php" method="GET" class="mb-4">
	<div class="card border-0 shadow-sm">
		<div class="card-body">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h2 class="h6 fw-bold mb-0"><?php etr("Search") ?></h2>
				<span class="text-secondary small"><?php etr("Pay accounts") ?></span>
			</div>
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-8 col-lg-6">
					<label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label>
					<input class="form-control" id="description" type="text" name="description" value="<?php echo htmlspecialchars($description) ?>"/>
				</div>
				<div class="col-12 col-md-auto">
					<input type="submit" name="search" value="<?php etr("Search") ?>" />
				</div>
			</div>
		</div>
	</div>
</form>

<form action="payaccounts.php" method="POST">
	<div class="card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
			<div>
				<h2 class="h5 fw-bold mb-1"><?php etr("Pay accounts") ?></h2>
				<p class="text-secondary small mb-0"><?php etr("Payroll calculation accounts") ?></p>
			</div>
			<?php newButton("payaccount.php") ?>
		</div>
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th class="text-center" style="width: 90px;"><?php etr("Delete") ?></th>
						<th class="text-end" style="width: 120px;"><?php etr("Id") ?></th>
						<th><?php etr("Description") ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
					$rs = query($selectSQL);
					$count = 0;
					while ($row = fetch_object($rs)) {
						$count++;
						$accountid = htmlspecialchars($row->accountid);
						$descriptionText = htmlspecialchars($row->description);
						echo "<tr>";
						echo "<td class='text-center'>";
						deleteIcon("payaccounts.php?del_accountid=$accountid");
						echo "</td>";
						echo "<td class='text-end font-monospace'>$accountid</td>";
						echo "<td><a class='fw-semibold' href='payaccount.php?accountid=$accountid'>$descriptionText</a></td>";
						echo "</tr>";
					}
					if ($count == 0)
						echo "<tr><td colspan='3' class='text-center text-secondary py-5'>" . tr("No records found") . "</td></tr>";
				?>
				</tbody>
			</table>
		</div>
		<div class="card-footer bg-white d-flex justify-content-end py-3">
			<span class="text-secondary small"><?php echo $count ?> <?php etr("records") ?></span>
		</div>
	</div>
</form>
<?php bottom() ?>
</body>
	
