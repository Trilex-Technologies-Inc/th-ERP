<?php
	include('include.php');

    $name = getParam('name');

	$selectSQL = "
	select
	    dimid,
	    name
	from dimension
	where name like '$name%'";

?>

<head>
<title>thERP - <?php etr("Dimensions") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Dimensions")) ?>

<form action="dimensions.php" method="GET">
<div class="card border-0 shadow-sm mb-3"><div class="card-body">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Name") ?>:</div>
<div class="col-12 col-md-auto"><?php textbox("name", $name) ?>&nbsp;
<?php searchButton() ?>
</div></div>

</div></div></div>
</form>

<form action="dimensions.php" method=POST>
<div class="card border-0 shadow-sm mb-3">
<div class="card-header bg-body-tertiary"><div class="row fw-semibold"><div class="col-3"><?php etr("Id") ?></div><div class="col-9"><?php etr("Name") ?></div></div></div>
<div class="list-group list-group-flush">
<?php
    $rs = query($selectSQL);
    while ($row = fetch_object($rs)) {
        echo "<div class='list-group-item'><div class='row align-items-center'><div class='col-3'>$row->dimid</div>";
        echo "<div class='col-9'><a href='dimension.php?dimid=$row->dimid'>$row->name</a></div></div></div>";
    }
?>
</div></div>
<br/>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("dimension.php") ?></div>
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
</div>
</div>
</form>
<?php bottom() ?>
</body>
