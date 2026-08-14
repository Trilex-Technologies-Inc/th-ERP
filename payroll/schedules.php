<?php
	include('include.php');

    $name = getParam('name');
    $description = getParam('description');

    $rowcount = getParam("rowcount");
    $i = 0;
    while ($i < $rowcount) {
        $del = getParam("del_$i");
        if ($del == "on") {
            $scheduleid = getParam("scheduleid_$i");
            $sql = "delete from workshift w where scheduleid exists ";
            $sql .= "(select scheduleid from schedule_shift ss where ss.shiftid=w.shiftid and ss.scheduleid=$scheduleid)";
            //sql($sql);
            $sql = "delete from schedule_shift where scheduleid=$scheduleid";
            sql($sql);
            $sql = "delete from schedule where scheduleid=$scheduleid";
            sql($sql);
        }
        $i++;
    }
?>

<head>
<?php metatag() ?>
<title>Payroll - <?php etr("Schedules") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Schedules")) ?>

<form action="schedules.php" method="GET" class="mb-4">
	<div class="card border-0 shadow-sm">
		<div class="card-body">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h2 class="h6 fw-bold mb-0"><?php etr("Search") ?></h2>
				<span class="text-secondary small"><?php etr("Schedules") ?></span>
			</div>
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-8 col-lg-6">
					<label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label>
					<input class="form-control" id="description" type="text" name="description" value="<?php echo htmlspecialchars($description) ?>"/>
				</div>
				<div class="col-12 col-md-auto">
					<input type="submit" name="search" value="<?php etr("Search") ?>"/>
				</div>
			</div>
		</div>
	</div>
</form>

<form action="schedules.php" method="POST">
	<div class="card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
			<div>
				<h2 class="h5 fw-bold mb-1"><?php etr("Schedules") ?></h2>
				<p class="text-secondary small mb-0"><?php etr("Work schedule definitions") ?></p>
			</div>
			<?php newButton("schedule.php?action=new") ?>
		</div>
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th class="text-center" style="width: 90px;"><?php etr("Delete") ?></th>
						<th class="text-end" style="width: 120px;"><?php etr("Id") ?></th>
						<th><?php etr("Name") ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				    $sql = <<<SQL
    select
      scheduleid,
      description
    from schedule
    where description like '$description%'
SQL;
				    $rs = query($sql);
				    $i = 0;
				    while ($row = fetch_object($rs)) {
				        $scheduleid = htmlspecialchars($row->scheduleid);
				        $descriptionText = htmlspecialchars($row->description);
				        echo "<tr>";
				        echo "<td class='text-center'>";
				        echo "<input type='checkbox' name='del_$i'/>";
				        echo "<input type='hidden' name='scheduleid_$i' value='$scheduleid'/>";
				        echo "</td>";
				        echo "<td class='text-end font-monospace'>$scheduleid</td>";
				        echo "<td><a class='fw-semibold' href='schedule.php?scheduleid=$scheduleid'>$descriptionText</a></td>";
				        echo "</tr>\n";
				        $i++;
				    }
				    if ($i == 0)
				        echo "<tr><td colspan='3' class='text-center text-secondary py-5'>" . tr("No records found") . "</td></tr>";
				    echo "<input type='hidden' name='rowcount' value='$i'/>";
				?>
				</tbody>
			</table>
		</div>
		<div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
			<?php saveButton() ?>
			<span class="text-secondary small"><?php echo $i ?> <?php etr("records") ?></span>
		</div>
	</div>
</form>
<?php bottom() ?>
</body>
