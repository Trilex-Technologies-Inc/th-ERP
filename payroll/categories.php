<?php
	include('include.php');

	if (isSave()) {
		$count = getParam("count");
		$i = 0;
		while ($i <= $count) {
			$delete = getParam("del_" . $i);
			if (!isEmpty($delete)) {
				sql("delete from category where categoryid=$i");
			}
			$description = getParam("description_$i");
			if (!isEmpty($description)) {
				$sql = "update category ";
				$sql .= "set description='$description' ";
				$sql .= "where categoryid=$i ";
				sql($sql);
			}
			$i++;
		}

		$description = getParam("description_new");
		if (!isEmpty($description)) {
			$rec = find("select max(categoryid) as categoryid from category");
			$categoryid = $rec->categoryid + 1;
			$sql = "insert into category ";
			$sql .= "(categoryid, description) ";
			$sql .= "values ($categoryid, '$description') ";
			sql($sql);
		}
	}

?>

<head>
<?php metatag() ?>
<title>Payroll - Categories</title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php title("Configuration > Categories") ?>

<form action="categories.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white py-3">
<h2 class="h5 fw-bold mb-1">Categories</h2>
<p class="text-secondary small mb-0">Payroll categories</p>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr><th class="text-center" style="width: 90px;">Delete</th><th>Description</th></tr></thead>
<tbody>
<?php

$sql = "select ";
$sql .= "categoryid, ";
$sql .= "description ";
$sql .= "from category ";
$sql .= "order by categoryid ";
$q = sql($sql);
$categoryid = 0;
$count = 0;
while ($rec = fetch($q)) {
	$categoryid = $rec->categoryid;
	$count++;
	$description = htmlspecialchars($rec->description);
	echo "<tr>";
	echo "<td class='text-center'><input type='checkbox' name='del_$categoryid'/></td>";
	echo "<td><input class='form-control' type='text' name='description_$categoryid' value='$description'/></td>";
	echo "</tr>\n";
}
?>
<tr class="table-light">
<td class="text-center text-secondary fw-semibold">+</td>
<td><input class="form-control" type="text" name="description_new"/></td>
</tr>
</tbody>
</table>
</div>
<div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<input type="submit" name="save" value="Save"/>
<span class="text-secondary small"><?php echo $count ?> <?php echo tr("records") ?></span>
</div>
</div>
<input type="hidden" name="count" value="<?php echo htmlspecialchars($categoryid) ?>"/>
</form>
<?php bottom() ?>
</body>
