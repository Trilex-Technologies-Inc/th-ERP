<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

	$apid = getParam('apid');
	$new = true;
	if (isSave()) {
		$apid = getParam('apid');
		$description = getParam('description');
		$name = getParam('name');
		if (isNew()) {
			$sql = "insert into advanced_percent (apid, description, name)
			        values ($apid, '$description', '$name')";
			sql($sql);
			$apid = insert_id();
			header("Location: advanced_percents.php");
			die;
		} else {
            $updateSQL =
    			"update advanced_percent set
    			    description='$description',
    			    name='$name'
                where apid=$apid";
    		sql($updateSQL);
		}
		$count = getParam("count");
		$i = 0;
		while ($i < $count) {
			$bracketid = getParam("bracketid_$i");
			$percent = getParam("percent_$i");
			sql("update ap_bracket set percent=$percent where apid=$apid and bracketid=$bracketid");
			$i++;
		}
		$ceiling_new = getParam("ceiling_new");
		if (!isEmpty($ceiling_new)) {
			$percent_new = getParam("percent_new");
			$bracketid = findValue("select max(bracketid) from ap_bracket where apid=$apid", 0) + 1;
			sql("insert into ap_bracket (apid, bracketid, ceiling, percent)
			     values ($apid, $bracketid, $ceiling_new, $percent_new)");
		}
	}
	$del_bracketid = getParam("del_bracketid");
	if (!isEmpty($del_bracketid)) {
		sql("delete from ap_bracket where apid=$apid and bracketid=$del_bracketid");
	}

	$rec = new Dummy();
	$bracketids = null;
	if (!isEmpty($apid)) {
	    $selectSQL =
  		"select apid,
		       description,
		       name
		from advanced_percent
		where apid=$apid
		";
		$rec = find($selectSQL, true);
		$new = false;
		$bracketids = query("select bracketid, ceiling, percent from ap_bracket where apid=$apid order by ceiling");
	}


?>
<head>
<title>Payroll - <?php etr("Advanced percent") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = $rec->name;
if ($new)
	$title = tr("Create");
title("<a href='advanced_percents.php'>" . tr("Advanced percent") . "</a> > $title")
?>

<form action="advanced_percent.php" method="POST">
<table>
<tr>
	<td><?php etr("Id") ?>:</td>
	<td><input type=text name='apid' value='<?php echo $apid ?>'/></td>
</tr>
<tr><td><?php etr("Name") ?>:</td><td><input type="text" name="name" value="<?php echo $rec->name ?>"/></td>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $rec->description ?>"/></td>
</table>
<br/>
<?php
if ($bracketids != null) {
	echo "<div class=border>";
	echo "<table>";
	echo "<th>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Interval") . "</th>";
	echo "<th>" . tr("Percent") . "</th>";
	$floor = 0;
	$class = 'odd';
	$i = 0;
	while ($row = fetch($bracketids)) {
		echo "<input type=hidden name='bracketid_$i' value='$row->bracketid'/>";
		echo "<tr class=$class>";
		echo "<td align=center>";
		deleteIcon("advanced_percent.php?apid=$apid&del_bracketid=$row->bracketid");
		echo "</td>";
		echo "<td>$floor - $row->ceiling</td>";
		echo "<td align=right><input type=text name='percent_$i' value='$row->percent' size=5/></td>";
		echo "</tr>";
		$floor = $row->ceiling;
		$class = ($class == "odd" ? "even" : "odd");
		$i++;
	}
	echo "<tr class=$class>";
	echo "<td/>";
	echo "<td><input type=text name='ceiling_new' /></td>";
	echo "<td><input type=text name='percent_new' size=5 /></td>";
	echo "</tr>";
	echo "<input type=hidden name=count value='$i'/>";
}
?>
</table>
</div>
<br/>
<?php saveButton() ?>

<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
