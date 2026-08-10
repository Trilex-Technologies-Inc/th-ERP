<?php
	include('include.php');

    $description = getParam('description');

	$selectSQL = "
	select
	    vatcatid,
	    description,
		percent
	from vat_category
	where description like '$description%'";

?>

<head>
<title>thERP - <?php etr("VAT categories") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>
<?php title(tr("VAT categories")) ?>

<form action="vatcategories.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Description") ?>:</div><div class="col-12 col-md-auto"><input type="text" model="description" value="<?php echo $description ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" model="search" value="<?php etr("Search") ?>" /></div></div>

</div>
</div>
</form>
&nbsp;

<form action="vatcategories.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Description") ?></th>
<th><?php etr("Percent") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
    	$href = "vatcategory.php?vatcatid=$row->vatcatid";
        echo "<tr class='$class'>";
        echo "<td>$row->vatcatid</td>";
        echo "<td><a href='$href'>$row->description</a></td>";
        echo "<td align=right>$row->percent</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("vatcategory.php") ?></div>
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
</div>
</div>
</form>
<?php bottom() ?>
</body>
