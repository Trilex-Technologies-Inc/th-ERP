<?php
	include('include.php');

	checkPermission(PERMISSION_ADMINISTRATE_USERS);

	$uname = getParam('uname');
	$new = true;
	if (isSave()) {
		$full_name = getParam('full_name');
		$language = getParam('language');
		$password0 = getParam('password0');
		if (isNew()) {
			$sql = "insert into user (username, full_name, language, password)
			        values ('$uname', '$full_name', '$language', '$password0')";
			sql($sql);
			header("Location: users.php");
			die;
		} else {
            $updateSQL =
    			"update user set
    			    full_name='$full_name',
					language='$language'
                where username='$uname'";
    		sql($updateSQL);
			if (!isEmpty($password0)) {
				sql("update user set password='$password0' where username='$uname'");
			}
		}
	}
	$del_groupid = getParam('del_groupid');
	if (!isEmpty($del_groupid)) {
		sql("delete from user_group where username='$uname' and groupid=$del_groupid");
	}
	$groupid_new = getParam('groupid_new');
	if (!isEmpty($groupid_new)) {
		sql("insert into user_group (username, groupid) values ('$uname', $groupid_new)");
	}

	$roles = null;
	$rec = new Dummy();
	if (!isEmpty($uname)) {
	    $selectSQL =
  		"select
		   username,
		   full_name,
		   language
		from user
		where username='$uname'
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
			$roles = query("select g.groupid, description
			                 from user_group ag
							 join usergroup g on g.groupid=ag.groupid
							 where ag.username='$uname'");
		}
	}

	$languages = rs2array(query("select language, description from language"));
	$allroles = rs2array(query("select groupid, description from usergroup"));

?>
<head>
<title>thERP - <?php etr("User") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("security.php") ?>
<?php
$title = $rec->full_name;
if ($new)
	$title = tr("Create user");
title("<a href='users.php'>" . tr("Users") . "</a> > $title")
?>

<main class="container-fluid px-0">
<form action="user.php" method="POST">
<div class="row g-4">
	<div class="col-12 col-xl-7">
		<section class="card border-0 shadow-sm h-100">
			<div class="card-header bg-white py-3 px-4">
				<div class="d-flex align-items-center gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary fs-4 flex-shrink-0" aria-hidden="true">◎</span>
					<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Account") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php echo $new ? tr("New user") : htmlspecialchars($rec->full_name) ?></h2></div>
				</div>
			</div>
			<div class="card-body p-4">
				<div class="row g-4">
					<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="uname"><?php etr("Username") ?></label><?php textbox('uname', $rec->username) ?><div class="form-text"><?php etr("Used to sign in") ?></div></div>
					<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="full_name"><?php etr("Name") ?></label><?php textbox("full_name", $rec->full_name) ?></div>
					<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="password0"><?php etr("Password") ?></label><input id="password0" type="password" name="password0" autocomplete="new-password"/><?php if (!$new) { ?><div class="form-text"><?php etr("Leave blank to keep the current password") ?></div><?php } ?></div>
					<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="language"><?php etr("Language") ?></label><?php combobox('language', $languages, $rec->language, false) ?></div>
				</div>
			</div>
		</section>
	</div>
<?php
if ($roles != null) {
	echo "<div class='col-12 col-xl-5'><section class='card border-0 shadow-sm h-100 overflow-hidden'>";
	echo "<div class='card-header bg-white py-3 px-4'><span class='text-secondary small text-uppercase fw-bold'>" . tr("Access") . "</span><h2 class='h5 fw-bold mb-0 mt-1'>" . tr("User groups") . "</h2></div>";
	echo "<div class='table-responsive'><table class='table table-hover align-middle mb-0'>";
	echo "<thead><tr><th class='text-center' style='width:80px'>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Group") . "</th></tr></thead><tbody>";
	$roleCount = 0;
	while ($row = fetch($roles)) {
		echo "<tr>";
		echo "<td class='text-center'>";
		deleteIcon("user.php?uname=$uname&del_groupid=$row->groupid");
		echo "</td>";
		echo "<td class='fw-semibold'>" . htmlspecialchars($row->description) . "</td>";
		echo "</tr>";
		$roleCount++;
	}
	if ($roleCount == 0)
		echo "<tr><td colspan='2' class='text-center text-secondary py-4'>" . tr("No groups assigned") . "</td></tr>";
	echo "<tr class='table-light'>";
	echo "<td class='text-center text-primary fw-bold'>+</td>";
	echo "<td>";
	comboBox("groupid_new", $allroles, null, true);
	echo "</td>";
	echo "</tr>";
	echo "</tbody></table></div>";
	echo "<div class='card-footer bg-white px-4 py-3 text-secondary small'>" . $roleCount . " " . tr("groups assigned") . "</div>";
	echo "</section></div>";
}
?>
</div>
<div class="d-flex justify-content-between align-items-center mt-4">
	<a class="btn btn-outline-secondary" href="users.php">&#8592; <?php etr("Back to users") ?></a>
	<?php saveButton() ?>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>
</body>
