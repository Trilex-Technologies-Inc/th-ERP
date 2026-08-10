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
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Name") ?>:</div>
<div class="col-12 col-md-auto"><?php textbox("name", $name) ?>&nbsp;
<?php searchButton() ?>
</div></div>

</div>
</div>
</form>

<form action="dimensions.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>$row->dimid</td>";
        echo "<td><a href='dimension.php?dimid=$row->dimid'>$row->name</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
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
