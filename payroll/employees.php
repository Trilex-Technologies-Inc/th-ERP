<?php
	include('include.php');

    $surname = getParam('surname');
    $givenname = getParam('givenname');

    $deleteSQL = "update employee set active=0 where employeeid=:a0";

	$inactive = getParam("inactive") == "on" ? 1 : 0;

	$selectSQL = <<<SQL
	select
	    employeeid,
	    givenname,
	    surname,
	    active
	from employee
	where surname like '$surname%' and givenname like '$givenname%'
SQL;
    if (!$inactive)
        $selectSQL .= " and active=1";

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Employees") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("employees.php") ?>
<?php title(tr("Employees")) ?>

<form action="employees.php" method="GET" class="mb-4">
<div class="card border-0 shadow-sm">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<h2 class="h6 fw-bold mb-0"><?php echo tr("Search") ?></h2>
			<span class="text-secondary small"><?php echo tr("Employees") ?></span>
		</div>
		<div class="row g-3 align-items-end">
			<div class="col-12 col-md-5">
				<label for="givenname" class="form-label fw-semibold"><?php echo tr("Given name") ?></label>
				<input class="form-control" id="givenname" type="text" name="givenname" value="<?php echo htmlspecialchars($givenname) ?>"/>
			</div>
			<div class="col-12 col-md-5">
				<label for="surname" class="form-label fw-semibold"><?php echo tr("Surname") ?></label>
				<input class="form-control" id="surname" type="text" name="surname" value="<?php echo htmlspecialchars($surname) ?>"/>
			</div>
			<div class="col-12 col-md-2">
				<div class="form-check mb-2">
					<input class="form-check-input" id="inactive" type="checkbox" name="inactive" <?php echo $inactive ? "checked" : "" ?>/>
					<label class="form-check-label" for="inactive"><?php echo tr("Show inactive") ?></label>
				</div>
			</div>
		</div>
		<div class="mt-3 pt-3 border-top">
			<?php searchButton() ?>
		</div>
	</div>
</div>
</form>

<form action="employees.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
	<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
		<h2 class="h6 fw-bold mb-0"><?php echo tr("Employees") ?></h2>
		<?php newButton("employee_detail.php") ?>
	</div>
	<div class="list-group list-group-flush">
<?php
	    $rs = query($selectSQL);
	    $employeeCount = 0;
	    while ($row = fetch_object($rs)) {
			$employeeCount++;
			$employeeid = htmlspecialchars($row->employeeid);
			$name = htmlspecialchars(trim($row->givenname . ' ' . $row->surname));
			$initial = htmlspecialchars(strtoupper(substr(trim($row->givenname), 0, 1)));
			echo "<div class='list-group-item px-3 py-3'>";
			echo "<div class='row g-3 align-items-center'>";
			echo "<div class='col-auto'><span class='user-avatar d-inline-grid'>$initial</span></div>";
			echo "<div class='col'>";
			echo "<a class='fw-bold' href='employee_detail.php?employeeid=$employeeid'>$name</a>";
			echo "<div class='small text-secondary'>" . tr("Id") . ": $employeeid</div>";
			echo "</div>";
			if (!$row->active)
				echo "<div class='col-auto'><span class='badge text-bg-secondary'>" . tr("Inactive") . "</span></div>";
	        echo "<div class='col-auto'><a class='btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2' href='calendar.php?employeeid=$employeeid'>";
	        image("calendar.png");
	        echo "<span>" . tr("Calendar") . "</span></a></div>";
			echo "</div>";
			echo "</div>";
	    }
		if ($employeeCount == 0)
			echo "<div class='text-center text-secondary py-5'>" . tr("No employees found") . "</div>";
?>
	</div>
</div>
</form>
<?php bottom() ?>	
</body>
