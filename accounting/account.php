<?php
	include('include.php');

	$accountid = getParam('accountid');
	$dimid = getParam('dimid');
	$new = true;
	$name = "";
	if (isSave()) {
		$accountid = getParam('accountid');
		$name = getParam('name');
		if (isNew()) {
			$sql = "
			insert into account (dimid, accountid, name)
			values ($dimid, $accountid, '$name')";
			sql($sql);
			$accountid = insert_id();
			header("Location: accounts.php?dimid=$dimid");
			die;
		} else {
            $updateSQL =
    			"update account set
    			    name='$name'
                where accountid=$accountid and dimid=$dimid";
    		sql($updateSQL);
		}
	}
	$del_groupid = getParam('del_groupid');
	if (!isEmpty($del_groupid)) {
		sql("delete from account_group where dimid=$dimid and accountid=$accountid and groupid=$del_groupid");
	}
	$groupid_new = getParam('groupid_new');
	if (!isEmpty($groupid_new)) {
		sql("insert into account_group (accountid, groupid) values ($accountid, $groupid_new)");
	}

	$groups = null;
	if (!isEmpty($accountid)) {
	    $selectSQL =
  		"select accountid,
		       name
		from account
		where accountid=$accountid and dimid=$dimid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$accountid = $rec->accountid;
			$name = $rec->name;
			$new = false;
			$groups = query("select g.groupid, description
			                 from account_group ag
							 join accountgroup g on g.groupid=ag.groupid
							 where ag.accountid=$accountid and ag.dimid=$dimid");
		}
	}

	$allGroups = rs2array(query("select groupid, description from accountgroup"));
	$dims = rs2array(query("select dimid, name from dimension"));

?>
<head>
<title>thERP - <?php etr("Account") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php
$title = $name;
if ($new)
	$title = tr("Create account");
title("<a href='accounts.php'>" . tr("Accounts") . "</a> > $title")
?>

<form action="account.php" method="POST">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Dimension") ?>:</div>
	<div class="col-12 col-md-auto"><?php combobox('dimid', $dims, $dimid, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Accountno") ?>:</div>
	<div class="col-12 col-md-auto"><?php numberbox('accountid', $accountid) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Name") ?>:</div>
	<div class="col-12 col-md-auto"><?php textbox('name', $name) ?></div>
</div></div>
<?php
if ($groups != null) {
	echo "<br/>";
	echo "<div class='card border-0 shadow-sm'><div class='card-header bg-body-tertiary'><div class='row fw-semibold'><div class='col-3'>" . tr("Delete") . "</div><div class='col-9'>" . tr("Group") . "</div></div></div><div class='list-group list-group-flush'>";
	while ($row = fetch($groups)) {
		echo "<div class='list-group-item'><div class='row align-items-center'><div class='col-3'>";
		deleteIcon("account.php?dimid=$dimid&accountid=$accountid&del_groupid=$row->groupid");
		echo "</div><div class='col-9'>$row->description</div></div></div>";
	}
	echo "<div class='list-group-item'><div class='row align-items-center'><div class='col-3'></div><div class='col-9'>";
	comboBox("groupid_new", $allGroups, null, true);
	echo "</div></div></div></div></div>";
}
?>
<br/>
<?php saveButton() ?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
