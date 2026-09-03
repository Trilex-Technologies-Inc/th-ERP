<?php
	include('include.php');

	$attributeid = getParam('attributeid');
	$new = true;
	if (isSave()) {
		$name = getParam('name');
		$attributeid = prepNull($attributeid);
		if (isNew()) {
			$sql = "insert into attribute (attributeid, name, object)  
			        values ($attributeid, '$name', " . ATTR_OBJECT_PRODUCT . ")";
			sql($sql);
			$attributeid = insert_id();
		} else {
			$updateSQL =
				"update attribute set
					name='$name'
				where attributeid=$attributeid";
			sql($updateSQL);
		}
		$count = getParam("count");
		for ($i = 0; $i < $count; $i++) {
			$optionid = getParam("optionid_$i");
			$description = getParam("description_$i");
			sql("
			update attribute_option
			set description='$description'
			where attributeid=$attributeid
			and optionid=$optionid");
		}
		$description = getParam("description_new");
		if (!isEmpty($description)) {
			$optionid = findValue("
			select max(optionid) from attribute_option
			where attributeid=$attributeid", 0);
			$optionid++;
			sql("
			insert into attribute_option (attributeid, optionid, description)
			values ($attributeid, $optionid, '$description')");
		}
	}
	$del_optionid = getParam("del_optionid");
	if (!isEmpty($del_optionid)) {
		sql("
		delete from attribute_option
		where attributeid=$attributeid and optionid=$del_optionid");
	}

	$rec = new Dummy();
	$options = null;
	if (!isEmpty($attributeid)) {
	    $selectSQL =
  		"select attributeid,
		       name
		from attribute
		where attributeid=$attributeid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
			$options = query("
			select optionid, description 
			from attribute_option
			where attributeid=$attributeid");
		}
	}

?>
<head>
<title>thERP - <?php echo tr("Attribute") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = "<a href='attributes.php'>" . tr("Attributes") . "</a> &gt; " . ($new ? tr("Create") : htmlspecialchars($rec->name));
title($title);
?>

<main class="container-fluid px-0">
<section class="card border-0 shadow-sm mb-4">
	<div class="card-body p-4 p-lg-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
		<div class="d-flex align-items-center gap-3">
			<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success fs-4 flex-shrink-0" aria-hidden="true">◆</span>
			<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Product configuration") ?></span><h1 class="h3 fw-bold mt-1 mb-1"><?php echo $new ? tr("Create attribute") : htmlspecialchars($rec->name) ?></h1><p class="text-secondary mb-0"><?php etr("Define a product characteristic and its available options") ?></p></div>
		</div>
		<span class="badge rounded-pill <?php echo $new ? 'text-bg-primary' : 'text-bg-light border' ?> px-3 py-2"><?php echo $new ? tr("New attribute") : tr("Attribute") . ' #' . htmlspecialchars($attributeid) ?></span>
	</div>
</section>

<form action="attribute.php" method="POST">
<input type="hidden" name="attributeid" value="<?php echo htmlspecialchars($attributeid) ?>"/>
<section class="card border-0 shadow-sm mb-4 overflow-hidden">
	<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Details") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Attribute information") ?></h2></div>
	<div class="card-body p-4"><div class="row g-4">
		<?php if (!$new) { ?><div class="col-12 col-md-3"><label class="form-label fw-semibold"><?php etr("Attribute ID") ?></label><div class="form-control bg-body-tertiary font-monospace">#<?php echo htmlspecialchars($attributeid) ?></div></div><?php } ?>
		<div class="col-12 <?php echo $new ? 'col-md-8' : 'col-md-7' ?>"><label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label><?php textbox('name', $rec->name) ?><div class="form-text"><?php etr("The label shown on product forms and specifications") ?></div></div>
	</div></div>
</section>
<?php
if ($options != null) {
	echo "<section class='card border-0 shadow-sm overflow-hidden'><div class='card-header bg-white d-flex justify-content-between align-items-center px-4 py-3'><div><span class='text-secondary small text-uppercase fw-bold'>" . tr("Choice values") . "</span><h2 class='h5 fw-bold mb-0 mt-1'>" . tr("Attribute options") . "</h2></div><span class='badge text-bg-light border'>" . tr("Optional") . "</span></div>";
	echo "<div class='table-responsive'><table class='table table-hover align-middle mb-0'>";
	echo "<thead class='table-light'><tr><th class='text-center' style='width:90px'>" . tr("Delete") . "</th>";
	echo "<th style='width:130px'>" . tr("Option ID") . "</th><th>" . tr("Description") . "</th></tr></thead><tbody>";
	$i = 0;
	while ($row = fetch($options)) {
		hidden("optionid_$i", $row->optionid);
		echo "<tr>";
		echo "<td class='text-center'>";
		deleteIcon("attribute.php?attributeid=$attributeid&del_optionid=$row->optionid");
		echo "</td>";
		echo "<td class='font-monospace text-secondary'>#" . htmlspecialchars($row->optionid) . "</td><td>";
		textbox("description_$i", $row->description);
		echo "</td>";
		echo "</tr>";
        $i++;
	}
	hidden('count', $i);
	echo "<tr class='table-light'><td class='text-center text-primary fw-bold'>+</td><td><span class='badge text-bg-primary'>" . tr("New") . "</span></td><td>";
	textbox('description_new', '');
	echo "</td>";
	echo "</tr>";
	echo "</tbody></table></div><div class='card-footer bg-white px-4 py-3 text-secondary small'>" . $i . " " . tr("options configured") . "</div></section>";
} else {
	echo "<div class='alert alert-light border text-secondary'>" . tr("Save the attribute first, then add its available options") . ".</div>";
}
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4"><a class="btn btn-outline-secondary" href="attributes.php">&#8592; <?php etr("Back to attributes") ?></a><?php saveButton() ?></div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>

</body>
