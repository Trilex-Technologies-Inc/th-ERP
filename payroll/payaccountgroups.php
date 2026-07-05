<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

    $description = getParam('description');

	$del_groupid = getParam('del_groupid');
	if (!isEmpty($del_groupid)) {
		sql("delete from payaccountgroup where groupid=$del_groupid");
	}

	$selectSQL = "
	select
	    g.groupid,
	    name,
	    description
	from payaccountgroup g
	where description like '$description%'";

?>

<head>
<title>Payroll - <?php etr("Pay account groups") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Pay account groups")) ?>

<form action="payaccountgroups.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $description ?>"/></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="payaccountgroups.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<th><?php etr("Description") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		echo "<td align=center>";
		deleteIcon("payaccountgroups.php?del_groupid=$row->groupid");
		echo "</td>";
        echo "<td>$row->groupid</td>";
        echo "<td><a href='payaccountgroup.php?groupid=$row->groupid'>$row->name</a></td>";
        echo "<td>$row->description</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<br/>
<table>
<tr>
<td><?php newButton("payaccountgroup.php") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
