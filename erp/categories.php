<?php
	include('include.php');

    $description = getParam('description');

	$selectSQL = "
	select
	    categoryid,
	    description
	from category
	where description like '$description%'";

?>

<head>
<title>thERP - <?php etr("Categories") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>
<?php title(tr("Categories")) ?>

<form action="categories.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Description") ?>:</div>
	<div class="col-12 col-md-auto"><?php textbox('description', $description) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php searchButton() ?></div></div>

</div>
</div>
</form>
&nbsp;

<form action="categories.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Description") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
    	$href = "category.php?categoryid=$row->categoryid";
        echo "<tr class='$class'>";
        echo "<td>$row->categoryid</td>";
        echo "<td><a href='$href'>$row->description</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("category.php") ?></div>
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
</div>
</div>
</form>
<?php bottom() ?>
</body>
