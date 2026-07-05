<?
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
<? include("menubar.php") ?>
<? title("Configuration > Categories") ?>

<form action="categories.php" method="POST">
<table>
<?

$sql = "select ";
$sql .= "categoryid, ";
$sql .= "description ";
$sql .= "from category ";
$sql .= "order by categoryid ";
$q = sql($sql);
echo "<th>Delete</th>";
echo "<th>Description</th>\n";
$class = "odd";
$runningno = 0;
while ($rec = fetch($q)) {
	$categoryid = $rec->categoryid;
	echo "<tr class='$class'>";
	echo "<td align='center'><input type='checkbox' name='del_$categoryid'/></td>";
	echo "<td><input type='text' name='description_$categoryid' value='$rec->description'/></td>";
	echo "</tr>\n";
	$class = ($class == "odd" ? "even" : "odd");
}
?>
<tr>
<td></td>
<td><input type="text" name="description_new"/></td>
</tr>
<tr>
<td><input type="submit" name="save" value="Save"/>
</tr>

</table>
<input type="hidden" name="count" value="<?= $categoryid ?>"/>
</form>
</body>
