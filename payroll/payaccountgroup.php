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
title("<a href='payaccountgroups.php'>" . tr("Pay account groups") . "</a> > $title")
?>

<form action="payaccountgroup.php" method="POST">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Id") ?>:</div>
	<div class="col-12 col-md-auto"><?php numberbox('groupid', $groupid) ?></div>
</div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Name") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="name" value="<?php echo $rec->name ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Description") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="description" value="<?php echo $rec->description ?>"/></div>
<?php hidden('old_description', $rec->description) ?>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Show in report") ?>:</div><div class="col-12 col-md-auto"><?php checkBox('report', $rec->report) ?></div>
</div><div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
<?php saveButton() ?>
&nbsp;
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
