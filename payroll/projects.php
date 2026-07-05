<?
	include('include.php');

	if (isSave()) {
		$count = getParam("rowcount");
		$i = 0;
		while ($i < $count) {
			$delete = getParam("del_$i");
			$projectid = getParam("projectid_$i");
			if ($delete == "on") {
				sql("delete from project where projectid=$projectid");
			}
			$description = getParam("description_$i");
			$sql = "update project ";
			$sql .= "set description='$description' ";
			$sql .= "where projectid=$projectid ";
			sql($sql);

			$categoryid = getParam("categoryid_$i");
			if ($categoryid != "null") {
			    $sql = "insert into cat_project ";
			    $sql .= "(projectid, categoryid) ";
			    $sql .= "values ($projectid, $categoryid)";
			    sql($sql);
			}

			$i++;
		}

		$description = getParam("description_new");
		if (!isEmpty($description)) {
			$sql = "insert into project ";
			$sql .= "(description) ";
			$sql .= "values ('$description') ";
			sql($sql);
		}
	}

	if (getParam("delcat") == "true") {
	    $projectid = getParam("del_projectid");
	    $categoryid = getParam("del_categoryid");
	    $sql = "delete from cat_project ";
	    $sql .= "where projectid=$projectid and categoryid=$categoryid";
	    sql($sql);
	}

	$description = getParam("description");
	$categoryid = getParam("categoryid");

?>

<head>
<?php metatag() ?>
<title>Payroll - Projects</title>
<?php styleSheet() ?>
</head>

<body>
<? include("menubar.php") ?>
<? title("Configuration > Projects") ?>

<form action="projects.php" method="GET" class="border">
<table>
<tr>
    <td>Description:</td>
    <td><? textbox("description") ?></td>
</tr>
<tr>
    <td>Category:</td>
    <td>
    <select name='categoryid'>
        <option value='null'></option>
        <?
        $sql = "select categoryid, description from category";
        $q = sql($sql);
        while ($cat = fetch($q)) {
            $selected = $cat->categoryid == $categoryid ? "selected" : "";
        	echo "<option value='$cat->categoryid' $selected>$cat->description</option>\n";
        }
        ?>
    </select>
    </td>
    <td><? button("Search", "search") ?></td>
</tr>
</table>
</form>

<form action="projects.php" method="POST">
<table>
<th>Delete</th>
<th>Description</th>
<th>Categories</td>
<?

$sql = "select ";
$sql .= "projectid, ";
$sql .= "description ";
$sql .= "from project p ";
$sql .= "where description like '$description%' ";
if (!isEmpty($categoryid)) {
    $sql .= " and exists (select * from cat_project cp where cp.projectid=p.projectid and cp.categoryid=$categoryid)";
}
$sql .= "order by projectid ";
$q = sql($sql);
$class = "odd";
$i = 0;
while ($rec = fetch($q)) {
	$projectid = $rec->projectid;
	echo "\n<input type=hidden name=projectid_$i value='$rec->projectid'/>";
	echo "<tr class='$class'>";
	echo "<td align='center'><input type='checkbox' name='del_$i'/></td>";
	echo "<td><input type='text' name='description_$i' value='$rec->description'/></td>";
	echo "<td>";
	$sql = "select c.categoryid, description ";
	$sql .= "from cat_project cp, category c ";
	$sql .= "where cp.categoryid = c.categoryid ";
	$sql .= "  and cp.projectid=$projectid";
	$q2 = sql($sql);
	$first = true;
	while ($rec = fetch($q2)) {
		echo $rec->description . "(<a href='projects.php?del_projectid=$projectid&delcat=true&del_categoryid=$rec->categoryid'>Del</a>), ";
		$first = false;
	}
	if (!$first)
	    echo "&nbsp;";
    echo "<select name='categoryid_$i'>";
    echo "<option value='null'>-- Add category --</option>";
    $sql = "select categoryid, description from category";
    $q2 = sql($sql);
    while ($cat = fetch($q2)) {
    	echo "<option value='$cat->categoryid'>$cat->description</option>\n";
    }
    echo "</select>";
    echo "</td>";
	echo "</tr>\n";
	$class = ($class == "odd" ? "even" : "odd");
	$i++;
}
echo "<input type=hidden name=rowcount value='$i'/>";
?>
<tr>
<td></td>
<td><input type="text" name="description_new"/></td>
</tr>
<tr>
<td><input type="submit" name="save" value="Save"/>
</tr>

</table>
</form>
</body>
