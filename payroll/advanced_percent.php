<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

	$apid = getParam('apid');
	$new = true;
	if (isSave()) {
		$apid = getParam('apid');
		$description = getParam('description');
		$name = getParam('name');
		if (isNew()) {
			$sql = "insert into advanced_percent (apid, description, name)
			        values ($apid, '$description', '$name')";
			sql($sql);
			$apid = insert_id();
			header("Location: advanced_percents.php");
			die;
		} else {
            $updateSQL =
    			"update advanced_percent set
    			    description='$description',
    			    name='$name'
                where apid=$apid";
    		sql($updateSQL);
		}
		$count = getParam("count");
		$i = 0;
		while ($i < $count) {
			$bracketid = getParam("bracketid_$i");
			$percent = getParam("percent_$i");
			sql("update ap_bracket set percent=$percent where apid=$apid and bracketid=$bracketid");
			$i++;
		}
		$ceiling_new = getParam("ceiling_new");
		if (!isEmpty($ceiling_new)) {
			$percent_new = getParam("percent_new");
			$bracketid = findValue("select max(bracketid) from ap_bracket where apid=$apid", 0) + 1;
			sql("insert into ap_bracket (apid, bracketid, ceiling, percent)
			     values ($apid, $bracketid, $ceiling_new, $percent_new)");
		}
	}
	$del_bracketid = getParam("del_bracketid");
	if (!isEmpty($del_bracketid)) {
		sql("delete from ap_bracket where apid=$apid and bracketid=$del_bracketid");
	}

	$rec = new Dummy();
	$bracketids = null;
	if (!isEmpty($apid)) {
	    $selectSQL =
  		"select apid,
		       description,
		       name
		from advanced_percent
		where apid=$apid
		";
		$rec = find($selectSQL, true);
		$new = false;
		$bracketids = query("select bracketid, ceiling, percent from ap_bracket where apid=$apid order by ceiling");
	}


?>
<head>
<title>Payroll - <?php etr("Advanced percent") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = $rec->name;
if ($new)
	$title = tr("Create");
title("<a href='advanced_percents.php'>" . tr("Advanced percent") . "</a> > " . htmlspecialchars($title))
?>

<form action="advanced_percent.php" method="POST">
<div class="card border-0 shadow-sm mb-4">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<div><h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($title) ?></h2><p class="text-secondary small mb-0"><?php etr("Advanced percent") ?></p></div>
<span class="badge text-bg-light border"><?php echo $new ? tr("New") : '#' . htmlspecialchars($apid) ?></span>
</div>
<div class="card-body p-4"><div class="row g-4">
<div class="col-12 col-lg-3"><label class="form-label fw-semibold" for="apid"><?php etr("Id") ?></label><input class="form-control" id="apid" type="text" name="apid" value="<?php echo htmlspecialchars($apid) ?>"/></div>
<div class="col-12 col-lg-9"><label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label><input class="form-control" id="name" type="text" name="name" value="<?php echo htmlspecialchars($rec->name) ?>" required /></div>
<div class="col-12"><label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label><input class="form-control" id="description" type="text" name="description" value="<?php echo htmlspecialchars($rec->description) ?>" required /></div>
</div></div>
</div>
<?php
if ($bracketids != null) {
	echo "<div class='card border-0 shadow-sm overflow-hidden'>";
	echo "<div class='card-header bg-white py-3'><h2 class='h5 fw-bold mb-1'>" . tr("Interval") . "</h2><p class='text-secondary small mb-0'>" . tr("Percent") . "</p></div>";
	echo "<div class='table-responsive'><table class='table table-hover align-middle mb-0'>";
	echo "<thead><tr><th class='text-center' style='width: 90px;'>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Interval") . "</th>";
	echo "<th style='width: 180px;'>" . tr("Percent") . "</th></tr></thead><tbody>";
	$floor = 0;
	$i = 0;
	while ($row = fetch($bracketids)) {
		$bracketid = htmlspecialchars($row->bracketid);
		$ceiling = htmlspecialchars($row->ceiling);
		$percent = htmlspecialchars($row->percent);
		echo "<tr>";
		echo "<td class='text-center'>";
		deleteIcon("advanced_percent.php?apid=" . htmlspecialchars($apid) . "&del_bracketid=$bracketid");
		echo "</td>";
		echo "<td><input type='hidden' name='bracketid_$i' value='$bracketid'/>" . htmlspecialchars($floor) . " - $ceiling</td>";
		echo "<td><input class='form-control' type='text' name='percent_$i' value='$percent' size='5'/></td>";
		echo "</tr>";
		$floor = $row->ceiling;
		$i++;
	}
	echo "<tr class='table-light'>";
	echo "<td class='text-center text-secondary fw-semibold'>+</td>";
	echo "<td><input class='form-control' type='text' name='ceiling_new' /></td>";
	echo "<td><input class='form-control' type='text' name='percent_new' size='5' /></td>";
	echo "</tr>";
	echo "</tbody></table></div>";
	echo "<input type='hidden' name='count' value='$i'/>";
	echo "</div>";
}
?>
<div class="advanced-percent-actions" role="group" aria-label="<?php etr("Form actions") ?>">
	<a class="advanced-percent-back" href="advanced_percents.php">
		<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
		<span><?php etr("Back") ?></span>
	</a>
	<button class="advanced-percent-save" type="submit" name="save" value="Save">
		<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 3h11l3 3v15H5zM8 3v6h8V3M8 21v-7h8v7"/></svg>
		<span><?php etr("Save") ?></span>
	</button>
</div>
<?php if ($new) { ?><input type="hidden" name="new" value="1"/><?php } ?>
</form>
<?php bottom() ?>
</body>
