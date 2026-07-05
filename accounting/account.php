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
<table>
<tr>
	<td><?php etr("Dimension") ?>:</td>
	<td><?php combobox('dimid', $dims, $dimid, false) ?></td>
</tr>
<tr>
	<td><?php etr("Accountno") ?>:</td>
	<td><?php numberbox('accountid', $accountid) ?></td>
</tr>
<tr>
	<td><?php etr("Name") ?>:</td>
	<td><?php textbox('name', $name) ?></td>
</table>
<?php
if ($groups != null) {
	echo "<br/>";
	echo "<div class=border>";
	echo "<table>";
	echo "<th>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Group") . "</th>";
	$class = 'odd';
	while ($row = fetch($groups)) {
		echo "<tr class=$class>";
		echo "<td align=center>";
		deleteIcon("account.php?dimid=$dimid&accountid=$accountid&del_groupid=$row->groupid");
		echo "</td>";
		echo "<td>$row->description</td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
	}
	echo "<tr class=$class/>";
	echo "<td/>";
	echo "<td>";
	comboBox("groupid_new", $allGroups, null, true);
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

</body>
