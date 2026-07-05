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
title("<a href='payaccounts.php'>" . tr("Pay accounts") . "</a> > $title")
?>

<form action="payaccount.php" method="POST">
<table>
<tr>
	<td class=label><?php etr("Id") ?>:</td>
	<td><?php numberbox('accountid', $accountid, 5) ?></td>
</tr>
<tr><td class=label><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $row->description ?>" size='40' /></td>
<?php hidden('old_description', $row->description) ?>
<tr>
	<td class=label><?php etr("Input type") ?>:</td>
	<td><?php comboBox("inputtype", $quantities, $row->inputtype, true) ?></td>
</tr>
<tr><td class=label valign=top><?php etr("Formula") ?>:</td><td><textarea name='formula' cols=60 rows=5><?php echo $row->formula ?></textarea></td>
<tr><td class=label><?php etr("Calculation sequence") ?>:</td><td><?php numberbox("calcseq", $row->calcseq, 5) ?></td>
<tr>
	<td class=label><?php etr("General ledger account") ?>:</td>
	<td><?php comboBox("glaccountid", $glaccounts, $row->glaccountid, true) ?></td>
</tr>
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
		deleteIcon("payaccount.php?accountid=$accountid&del_groupid=$row->groupid");
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
<?php bottom() ?>
</body>