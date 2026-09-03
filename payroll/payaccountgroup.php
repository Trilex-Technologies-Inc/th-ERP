<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);
	
	$groupid = getParam('groupid');
	$new = true;
	if (isSave()) {
		$groupid = getParam('groupid');
		$description = getParam('description');
		$name = getParam('name');
		$report = prepNull(getParam('report'));
		if (isNew()) {
			$sql = "insert into payaccountgroup (groupid, name, report, description)
			        values ($groupid, '$name', $report, '$description')";
			sql($sql);
			header("Location: payaccountgroups.php");
			die;
		} else {
            $updateSQL =
    			"update payaccountgroup set
    			    name='$name',
					report=$report,
					description='$description'
                where groupid=$groupid";
    		sql($updateSQL);
		}
	}

	$rec = new Dummy();
	if (!isEmpty($groupid)) {
	    $selectSQL =
  		"select g.groupid,
		       description,
		       name,
			   report
		from payaccountgroup g
		where g.groupid=$groupid
		";
		$rec = find($selectSQL, true);
		$new = false;
	}

?>
<head>
<title>Payroll - <?php etr("Pay account group") ?></title>
<?php
include_common();
styleSheet();
?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = $rec->description;
if ($new)
	$title = tr("Create payaccount group");
title("<a href='payaccountgroups.php'>" . tr("Pay account groups") . "</a> > " . htmlspecialchars($title))
?>

<form action="payaccountgroup.php" method="POST">
<div class="card border-0 shadow-sm">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<div><h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($title) ?></h2><p class="text-secondary small mb-0"><?php etr("Pay account group") ?></p></div>
<span class="badge text-bg-light border"><?php echo $new ? tr("New") : '#' . htmlspecialchars($groupid) ?></span>
</div>
<div class="card-body p-4">
<div class="row g-4">
<div class="col-12 col-lg-3"><label class="form-label fw-semibold" for="groupid"><?php etr("Id") ?></label><?php numberbox('groupid', $groupid) ?></div>
<div class="col-12 col-lg-9"><label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label><input class="form-control" id="name" type="text" name="name" value="<?php echo htmlspecialchars($rec->name) ?>" required /></div>
<div class="col-12 col-lg-9"><label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label><input class="form-control" id="description" type="text" name="description" value="<?php echo htmlspecialchars($rec->description) ?>" required /><?php hidden('old_description', $rec->description) ?></div>
<div class="col-12 col-lg-3 d-flex align-items-end"><div class="form-check mb-2"><?php checkBox('report', $rec->report) ?><label class="form-check-label fw-semibold" for="report"><?php etr("Show in report") ?></label></div></div>
</div>
</div>
<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3"><?php saveButton() ?><a class="btn btn-outline-secondary" href="payaccountgroups.php"><?php etr("Back") ?></a></div>
</div>
<?php if ($new) { ?><input type="hidden" name="new" value="1"/><?php } ?>
</form>
<?php bottom() ?>
</body>
