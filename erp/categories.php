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
<table>
<tr>
	<td><?php etr("Description") ?>:</td>
	<td><?php textbox('description', $description) ?></td>
<tr><td><?php searchButton() ?></td></tr>
</tr>
</table>
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
<table>
<tr>
<td><?php newButton("category.php") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
