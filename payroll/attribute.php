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
$title = "<a href='attributes.php'>" . tr("Attributes") . "</a> > $rec->description ";
top("configuration.php", "Attribute", $title); 
?>

<form action="attribute.php" method="POST">
<input type=hidden name=attributeid value='<?php echo $attributeid ?>'/>
<table>
<tr><td><?php echo tr("Name") ?>:</td><td><?php echo $rec->description ?></td>
</table>
<?php
if ($options != null) {
	echo "<br/>";
	echo "<div class=border>";
	echo "<table>";
	echo "<th>" . tr("Delete") . "</th>";	
	echo "<th>" . tr("Id") . "</th>";
	echo "<th>" . tr("Option") . "</th>";
	$class = 'odd';
	$i = 0;
	while ($row = fetch($options)) {
		hidden("optionid_$i", $row->optionid);
		echo "<tr class=$class>";
		echo "<td align=center>";
		deleteIcon("attribute.php?attributeid=$attributeid&del_optionid=$row->optionid");
		echo "</td>";
		echo "<td>$row->optionid</td>";
		echo "<td>";
		textbox("description_$i", $row->description);
		echo "</td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
	}
	hidden('count', $i);
	echo "<tr class=$class/>";
	echo "<td/>";
	echo "<td>";
	textbox('optionid_new', '');
	echo "</td>";
	echo "<td>";
	textbox('description_new', '');
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</div>";
}
?>

<br>
<?php saveButton() ?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>

</body>
