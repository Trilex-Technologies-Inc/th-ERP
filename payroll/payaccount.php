<?php
	include('include.php');
	
	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

	$accountid = getParam('accountid');
	$periodid = getCurrentPeriod();
	$new = true;
	if (isSave()) {
		$accountid = getParam('accountid');
		$description = getParam('description');
		$formula = getParam('formula');
		$calcseq = getParam('calcseq', 100);
		$inputtype = prepNull(getParam('inputtype'));
		$glaccountid = prepNull(getParam('glaccountid'));
		if (isNew()) {
			$sql = "insert into payaccount (accountid, formula, calcseq, inputtype, glaccountid, description)
			        values ($accountid, '$formula', $calcseq, $inputtype, $glaccountid, '$description')";
			sql($sql);
			header("Location: payaccounts.php");
			die;
		} else {
            $updateSQL =
    			"update payaccount set
    			    formula='$formula',
    			    calcseq=$calcseq,
    			    glaccountid=$glaccountid,
					inputtype=$inputtype,
					description='$description'
                where accountid=$accountid";
    		sql($updateSQL);
		}
	}
	$del_groupid = getParam('del_groupid');
	if (!isEmpty($del_groupid)) {
		sql("delete from payaccount_group where groupid=$del_groupid and accountid=$accountid");
	}
	$groupid_new = getParam('groupid_new');
	if (!isEmpty($groupid_new)) {
		sql("insert into payaccount_group (accountid, groupid) values ($accountid, $groupid_new)");
	}

	$groups = null;
	$row = new Dummy();
	if (!isEmpty($accountid)) {
	    $selectSQL =
  		"select a.accountid,
		       description,
		       formula,
		       calcseq,
			   inputtype,
			   glaccountid
		from payaccount a
		where a.accountid=$accountid
		";
		$row = find($selectSQL, true);
		if ($row != null) {
			$new = false;
			$groups = query("select g.groupid, g.description
			                 from payaccount_group ag
							 join payaccountgroup g on g.groupid=ag.groupid
							 where ag.accountid=$accountid
							 ");
		}
	}

	$allGroups = rs2array(query("select g.groupid, description
	                             from payaccountgroup g"));
	$glaccounts = rs2array(query("select a.accountid, a.accountid, name
							      from account a
								  join account_group g on g.accountid=a.accountid"));
	$quantities = getInputtypeDescriptionList();

?>
<head>
<title>Payroll - <?php etr("Pay account") ?></title>
<?php styleSheet() ?>
<?php include_common() ?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = $row->description;
if ($new)
	$title = tr("Create account");
title("<a href='payaccounts.php'>" . tr("Pay accounts") . "</a> > " . htmlspecialchars($title))
?>

<form action="payaccount.php" method="POST">
<div class="card border-0 shadow-sm mb-4">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<div><h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($title) ?></h2><p class="text-secondary small mb-0"><?php etr("Pay account") ?></p></div>
<span class="badge text-bg-light border"><?php echo $new ? tr("New") : '#' . htmlspecialchars($accountid) ?></span>
</div>
<div class="card-body p-4"><div class="row g-4">
<div class="col-12 col-lg-3"><label class="form-label fw-semibold" for="accountid"><?php etr("Id") ?></label><?php numberbox('accountid', $accountid, 5) ?></div>
<div class="col-12 col-lg-9"><label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label><input class="form-control" id="description" type="text" name="description" value="<?php echo htmlspecialchars($row->description) ?>" required /><?php hidden('old_description', $row->description) ?></div>
<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="inputtype"><?php etr("Input type") ?></label><?php comboBox("inputtype", $quantities, $row->inputtype, true) ?></div>
<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="calcseq"><?php etr("Calculation sequence") ?></label><?php numberbox("calcseq", $row->calcseq, 5) ?></div>
<div class="col-12"><label class="form-label fw-semibold" for="formula"><?php etr("Formula") ?></label><textarea class="form-control font-monospace" id="formula" name="formula" rows="6"><?php echo htmlspecialchars($row->formula) ?></textarea></div>
<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="glaccountid"><?php etr("General ledger account") ?></label><?php comboBox("glaccountid", $glaccounts, $row->glaccountid, true) ?></div>
</div></div>
</div>
<?php
if ($groups != null) {
	echo "<div class='card border-0 shadow-sm overflow-hidden mb-4'>";
	echo "<div class='card-header bg-white py-3'><h2 class='h5 fw-bold mb-1'>" . tr("Group") . "</h2><p class='text-secondary small mb-0'>" . tr("Pay account groups") . "</p></div>";
	echo "<div class='table-responsive'><table class='table table-hover align-middle mb-0'>";
	echo "<thead><tr><th class='text-center' style='width: 90px;'>" . tr("Delete") . "</th><th>" . tr("Group") . "</th></tr></thead><tbody>";
	$groupCount = 0;
	while ($groupRow = fetch($groups)) {
		$groupCount++;
		$groupid = htmlspecialchars($groupRow->groupid);
		echo "<tr>";
		echo "<td class='text-center'>";
		deleteIcon("payaccount.php?accountid=" . htmlspecialchars($accountid) . "&del_groupid=$groupid");
		echo "</td>";
		echo "<td>" . htmlspecialchars($groupRow->description) . "</td>";
		echo "</tr>";
	}
	echo "<tr class='table-light'>";
	echo "<td class='text-center text-secondary fw-semibold'>+</td>";
	echo "<td>";
	comboBox("groupid_new", $allGroups, null, true);
	echo "</td>";
	echo "</tr>";
	echo "</tbody></table></div>";
	echo "<div class='card-footer bg-white d-flex justify-content-end py-3'><span class='text-secondary small'>$groupCount " . tr("records") . "</span></div>";
	echo "</div>";
}
?>
<div class="card border-0 shadow-sm"><div class="card-body d-flex flex-wrap gap-2"><?php saveButton() ?><a class="btn btn-outline-secondary" href="payaccounts.php"><?php etr("Back") ?></a></div></div>
<?php if ($new) { ?><input type="hidden" name="new" value="1"/><?php } ?>
</form>
<?php bottom() ?>
</body>
