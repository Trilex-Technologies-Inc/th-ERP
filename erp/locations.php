<?php
	include('include.php');

    $name = getParam('name');

	$selectSQL = "
	select
	    locationid,
	    name
	from location
	where name like '$name%'";

?>

<head>
<title>thERP - <?php etr("Locations") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>
<?php title(tr("Locations")) ?>

<form action="locations.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Name") ?>:</div>
	<div class="col-12 col-md-auto"><?php textbox('name', $name) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php searchButton() ?></div></div>

</div>
</div>
</form>
&nbsp;

<form action="locations.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
    	$href = "location.php?locationid=$row->locationid";
        echo "<tr class='$class'>";
        echo "<td>$row->locationid</td>";
        echo "<td><a href='$href'>$row->name</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("location.php") ?></div>
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
</div>
</div>
</form>
<?php bottom() ?>
</body>
