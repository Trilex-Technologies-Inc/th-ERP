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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Description") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="description" value="<?php echo $description ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div></div>

</div>
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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("payaccountgroup.php") ?></div>
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
</div>
</div>
</form>
<?php bottom() ?>
</body>
