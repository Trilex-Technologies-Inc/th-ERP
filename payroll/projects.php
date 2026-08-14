<?php
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
<?php include("menubar.php") ?>
<?php title("Configuration > Projects") ?>

<form action="projects.php" method="GET" class="mb-4">
<div class="card border-0 shadow-sm"><div class="card-body">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h2 class="h6 fw-bold mb-0">Search</h2><span class="text-secondary small">Projects</span></div>
<div class="row g-3 align-items-end">
    <div class="col-12 col-lg-5"><label class="form-label fw-semibold" for="description">Description</label><?php textbox("description") ?></div>
    <div class="col-12 col-lg-5"><label class="form-label fw-semibold" for="categoryid">Category</label>
    <select id="categoryid" name="categoryid">
        <option value='null'></option>
        <?php
        $sql = "select categoryid, description from category";
        $q = sql($sql);
        while ($cat = fetch($q)) {
            $selected = $cat->categoryid == $categoryid ? "selected" : "";
            echo "<option value='" . htmlspecialchars($cat->categoryid) . "' $selected>" . htmlspecialchars($cat->description) . "</option>\n";
        }
        ?>
    </select>
    </div>
    <div class="col-12 col-lg-auto"><?php button("Search", "search") ?></div>
</div>
</div></div>
</form>

<form action="projects.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white py-3"><h2 class="h5 fw-bold mb-1">Projects</h2><p class="text-secondary small mb-0">Project categories</p></div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr><th class="text-center" style="width: 90px;">Delete</th><th>Description</th><th>Categories</th></tr></thead>
<tbody>
<?php

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
$i = 0;
while ($rec = fetch($q)) {
	$projectid = $rec->projectid;
	$projectDescription = htmlspecialchars($rec->description);
	echo "<tr>";
	echo "<td class='text-center'><input type='checkbox' name='del_$i'/><input type='hidden' name='projectid_$i' value='" . htmlspecialchars($projectid) . "'/></td>";
	echo "<td><input class='form-control' type='text' name='description_$i' value='$projectDescription'/></td>";
	echo "<td>";
	$sql = "select c.categoryid, description ";
	$sql .= "from cat_project cp, category c ";
	$sql .= "where cp.categoryid = c.categoryid ";
	$sql .= "  and cp.projectid=$projectid";
	$q2 = sql($sql);
	$first = true;
	while ($rec = fetch($q2)) {
		echo "<span class='badge text-bg-light border me-1'>" . htmlspecialchars($rec->description) . " <a href='projects.php?del_projectid=$projectid&delcat=true&del_categoryid=$rec->categoryid'>Del</a></span>";
		$first = false;
	}
	if (!$first)
	    echo "&nbsp;";
    echo "<select name='categoryid_$i'>";
    echo "<option value='null'>-- Add category --</option>";
    $sql = "select categoryid, description from category";
    $q2 = sql($sql);
    while ($cat = fetch($q2)) {
        echo "<option value='" . htmlspecialchars($cat->categoryid) . "'>" . htmlspecialchars($cat->description) . "</option>\n";
    }
    echo "</select>";
    echo "</td>";
	echo "</tr>\n";
	$i++;
}
echo "<input type='hidden' name='rowcount' value='$i'/>";
?>
<tr class="table-light">
<td class="text-center text-secondary fw-semibold">+</td>
<td><input class="form-control" type="text" name="description_new"/></td>
<td></td>
</tr>
</tbody>
</table>
</div>
<div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3"><input type="submit" name="save" value="Save"/><span class="text-secondary small"><?php echo $i ?> <?php echo tr("records") ?></span></div>
</div>
</form>
<?php bottom() ?>
</body>
