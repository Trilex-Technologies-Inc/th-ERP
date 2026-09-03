<?php
	include('include.php');

	checkPermission(PERMISSION_ADMINISTRATE_USERS);

	$groupidParam = getParam('groupid');
	$groupid = isEmpty($groupidParam) ? null : (int)$groupidParam;
	$new = true;
	if (isSave()) {
		$description = mysqli_real_escape_string(db_connection(), trim((string)getParam('description')));
		$groupid = (int)$groupid;
		if (isNew()) {
			$sql = "insert into usergroup (groupid, description)  
			        values ($groupid, '$description')";
			sql($sql);
			header("Location: usergroups.php");
			die;
		} else {
            $updateSQL =
    			"update usergroup set
    			    description='$description'
                where groupid='$groupid'";
    		sql($updateSQL);
		}
	}
	$del_permissionid = getParam('del_permissionid');
	if (!isEmpty($del_permissionid)) {
		$groupid = (int)$groupid;
		$del_permissionid = (int)$del_permissionid;
		sql("delete from usergroup_permission where groupid=$groupid and permissionid=$del_permissionid");
	}
	$permissionid_new = getParam('permissionid_new');
	if (!isEmpty($permissionid_new)) {
		$groupid = (int)$groupid;
		$permissionid_new = (int)$permissionid_new;
		sql("insert ignore into usergroup_permission (groupid, permissionid) values ($groupid, $permissionid_new)");
	}

	$permissions = null;
	$rec = new Dummy();
	if (!isEmpty($groupid)) {
	    $selectSQL =
  		"select 
		   groupid,
		   description
		from usergroup
		where groupid='$groupid'
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
			$permissions = query("select g.permissionid, description
			                 from usergroup_permission ag
							 join permission g on g.permissionid=ag.permissionid
							 where ag.groupid='$groupid'");
		}
	}
	
	$languages = rs2array(query("select language, description from language"));
	$permissionFilter = $new ? '' : "where permissionid not in (select permissionid from usergroup_permission where groupid=" . (int)$groupid . ")";
	$allpermissions = rs2array(query("select permissionid, description from permission $permissionFilter order by description"));

?>
<head>
<?php metatag() ?>
<title>thERP - <?php etr("User group") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("security.php") ?>
<?php
$title = htmlspecialchars((string)$rec->description, ENT_QUOTES, 'UTF-8');
if ($new)
	$title = tr("Create user group");
title("<a href='usergroups.php'>" . tr("User groups") . "</a> > $title") 
?>

<main class="container-fluid px-0">
<form action="usergroup.php" method="POST">
<div class="row g-4">
	<div class="col-12 col-xl-7">
		<section class="card border-0 shadow-sm h-100">
			<div class="card-header bg-white py-3 px-4">
				<div class="d-flex align-items-center gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success fs-4 flex-shrink-0" aria-hidden="true">◉</span>
					<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Access control") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php echo $new ? tr("New user group") : htmlspecialchars((string)$rec->description, ENT_QUOTES, 'UTF-8') ?></h2></div>
				</div>
			</div>
			<div class="card-body p-4">
				<div class="row g-4">
					<div class="col-12 col-md-4">
						<label class="form-label fw-semibold" for="groupid"><?php etr("Id") ?></label>
						<?php if ($new) { ?><input class="form-control" type="number" min="1" id="groupid" name="groupid" required><?php } else { ?><div class="form-control bg-light"><?php echo (int)$rec->groupid ?></div><?php hidden('groupid', (int)$rec->groupid); } ?>
						<div class="form-text"><?php etr("Unique group identifier") ?></div>
					</div>
					<div class="col-12 col-md-8">
						<label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label>
						<input class="form-control" type="text" id="description" name="description" maxlength="80" required value="<?php echo htmlspecialchars((string)$rec->description, ENT_QUOTES, 'UTF-8') ?>">
						<div class="form-text"><?php etr("A clear name that describes this group's responsibilities") ?></div>
					</div>
				</div>
			</div>
		</section>
	</div>
<?php
if ($permissions != null) {
	echo "<div class='col-12 col-xl-5'><section class='card border-0 shadow-sm h-100 overflow-hidden'>";
	echo "<div class='card-header bg-white py-3 px-4'><span class='text-secondary small text-uppercase fw-bold'>" . tr("Access") . "</span><h2 class='h5 fw-bold mb-0 mt-1'>" . tr("Permissions") . "</h2></div>";
	echo "<div class='table-responsive'><table class='table table-hover align-middle mb-0'>";
	echo "<thead><tr><th class='text-center' style='width:80px'>" . tr("Delete") . "</th><th>" . tr("Permission") . "</th></tr></thead><tbody>";
	$permissionCount = 0;
	while ($row = fetch($permissions)) {
		echo "<tr><td class='text-center'>";
		deleteIcon("usergroup.php?groupid=$groupid&del_permissionid=$row->permissionid");
		echo "</td>";
		echo "<td class='fw-semibold'>" . htmlspecialchars((string)$row->description, ENT_QUOTES, 'UTF-8') . "</td>";
		echo "</tr>";
		$permissionCount++;
	}
	if ($permissionCount == 0)
		echo "<tr><td colspan='2' class='text-center text-secondary py-4'>" . tr("No permissions assigned") . "</td></tr>";
	if (count($allpermissions) > 0) {
		echo "<tr class='table-light'><td class='text-center text-primary fw-bold'>+</td><td>";
		comboBox("permissionid_new", $allpermissions, null, true);
		echo "<div class='form-text'>" . tr("Select a permission and save the group") . "</div></td></tr>";
	}
	echo "</tbody></table></div><div class='card-footer bg-white px-4 py-3 text-secondary small'>$permissionCount " . tr("permissions assigned") . "</div></section></div>";
}
?>
</div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
	<a class="btn btn-outline-secondary" href="usergroups.php">&#8592; <?php etr("Back to user groups") ?></a>
	<?php saveButton() ?>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>
</body>
