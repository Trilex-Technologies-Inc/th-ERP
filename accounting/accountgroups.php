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
<div class="card border-0 shadow-sm mb-3"><div class="card-body">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Description") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="description" value="<?php echo $description ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div></div>

</div></div></div>
</form>
<form action="accountgroups.php" method=POST>
<div class="card border-0 shadow-sm mb-3">
<div class="card-header bg-body-tertiary"><div class="row fw-semibold"><div class="col-3"><?php etr("Id") ?></div><div class="col-9"><?php etr("Description") ?></div></div></div>
<div class="list-group list-group-flush">
<?php
    $rs = query($selectSQL);
    while ($row = fetch_object($rs)) {
        echo "<div class='list-group-item'><div class='row align-items-center'><div class='col-3'>$row->groupid</div>";
        echo "<div class='col-9'><a href='accountgroup.php?groupid=$row->groupid'>$row->description</a></div></div></div>";
    }
?>
</div></div>
<br/>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("accountgroup.php") ?></div>
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
</div>
</div>
</form>
</body>
