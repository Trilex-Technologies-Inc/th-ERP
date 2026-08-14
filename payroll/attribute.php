<?php
	include('include.php');

	$attributeid = getParam('attributeid');
	$new = true;
	if (isSave()) {
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
		$optionid = getParam("optionid_new");
		if (!isEmpty($optionid)) {
			$description = getParam("description_new");
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
	if (!isEmpty($attributeid)) {
	    $selectSQL =
  		"select attributeid, description
		from attribute_description
		where attributeid=$attributeid
		and language='" . getLanguage() . "'
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
<?php head("Attribute") ?>

<body>
<?php 
$title = "<a href='attributes.php'>" . tr("Attributes") . "</a> > " . htmlspecialchars($rec->description);
top("configuration.php", "Attribute", $title); 
?>

<form action="attribute.php" method="POST">
<input type="hidden" name="attributeid" value="<?php echo htmlspecialchars($attributeid) ?>"/>
<?php
if ($options != null) {
	echo "<div class='card border-0 shadow-sm overflow-hidden'>";
	echo "<div class='card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3'>";
	echo "<div><h2 class='h5 fw-bold mb-1'>" . htmlspecialchars($rec->description) . "</h2><p class='text-secondary small mb-0'>" . tr("Choices") . "</p></div>";
	echo "<span class='badge text-bg-light border'>#" . htmlspecialchars($attributeid) . "</span></div>";
	echo "<div class='table-responsive'><table class='table table-hover align-middle mb-0'>";
	echo "<thead><tr><th class='text-center' style='width: 90px;'>" . tr("Delete") . "</th>";
	echo "<th class='text-end' style='width: 120px;'>" . tr("Id") . "</th>";
	echo "<th>" . tr("Option") . "</th></tr></thead><tbody>";
	$i = 0;
	while ($row = fetch($options)) {
		$optionid = htmlspecialchars($row->optionid);
		echo "<tr>";
		echo "<td class='text-center'>";
		deleteIcon("attribute.php?attributeid=" . htmlspecialchars($attributeid) . "&del_optionid=$optionid");
		echo "</td>";
		echo "<td class='text-end font-monospace'>$optionid";
		hidden("optionid_$i", $row->optionid);
		echo "</td>";
		echo "<td>";
		textbox("description_$i", $row->description);
		echo "</td>";
		echo "</tr>";
        $i++;
	}
	hidden('count', $i);
	echo "<tr class='table-light'>";
	echo "<td class='text-center text-secondary fw-semibold'>+</td>";
	echo "<td>";
	textbox('optionid_new', '');
	echo "</td>";
	echo "<td>";
	textbox('description_new', '');
	echo "</td>";
	echo "</tr>";
	echo "</tbody></table></div>";
	echo "<div class='card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3'>";
}
?>
<?php saveButton() ?>
<a class="btn btn-outline-secondary" href="attributes.php"><?php etr("Back") ?></a>
<?php if ($options != null) echo "<span class='text-secondary small'>$i " . tr("records") . "</span></div></div>"; ?>
<?php if ($new) { ?><input type="hidden" name="new" value="1"/><?php } ?>
</form>
<?php bottom() ?>

</body>
