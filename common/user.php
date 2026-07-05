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

<form action="user.php" method="POST">
<table>
<tr>
	<td><?php etr("Username") ?>:</td>
	<td><?php textbox('uname', $rec->username) ?></td>
</tr>
<tr><td><?php etr("Name") ?>:</td><td><?php textbox("full_name", $rec->full_name) ?></td>
<tr>
	<td><?php etr("Password") ?>:</td>
	<td><input type=password name='password0'/></td>
</tr>
<tr>
	<td><?php etr("Language") ?>:</td>
	<td><?php combobox('language', $languages, $rec->language, false) ?></td>
</tr>
</table>
<?php
if ($roles != null) {
	echo "<br/>";
	echo "<div class=border>";
	echo "<table>";
	echo "<th>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Groups") . "</th>";
	$class = 'odd';
	while ($row = fetch($roles)) {
		echo "<tr class=$class>";
		echo "<td align=center>";
		deleteIcon("user.php?uname=$uname&del_groupid=$row->groupid");
		echo "</td>";
		echo "<td>$row->description</td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
	}
	echo "<tr class=$class/>";
	echo "<td/>";
	echo "<td>";
	comboBox("groupid_new", $allroles, null, true);
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</div>";
}
?>
<br/>
<?php saveButton() ?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
