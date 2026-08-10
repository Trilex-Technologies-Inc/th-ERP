<?php
	include('include.php');

    $description = getParam('description');

	$selectSQL = "
	select
	    groupid,
	    description
	from accountgroup
	where description like '$description%'";

?>

<head>
<title>thERP - <?php etr("Account groups") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Account groups")) ?>

<form action="accountgroups.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Description") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="description" value="<?php echo $description ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div></div>

</div>
</div>
</form>
&nbsp;

<form action="accountgroups.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Description") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>$row->groupid</td>";
        echo "<td><a href='accountgroup.php?groupid=$row->groupid'>$row->description</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<br/>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("accountgroup.php") ?></div>
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
</div>
</div>
</form>
</body>
