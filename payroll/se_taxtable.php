<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

	$year = date('Y');
	$tableno = getParam('tableno');
	$periodlength = getParam('periodlength', 30);
	$uploadError = null;

	function import($filename, $year = null)
	{
		set_time_limit(200);
		if ($year == null)
			$year = date('Y');
		$records = array();
		$lines = file($filename, FILE_IGNORE_NEW_LINES);
		foreach ($lines as $lineNumber => $line) {
			$line = rtrim($line, "\r\n");
			if (strlen(trim($line)) > 0) {
				if (strlen($line) !== 44)
					return "Line " . ($lineNumber + 1) . " must contain exactly 44 characters.";
				$periodlength = substr($line, 0, 2);
				$type = substr($line, 2, 1);
				$tableno = substr($line, 3, 2);
				$floor = substr($line, 5, 7);
				$ceiling = substr($line, 12, 7);
				if (isEmpty($ceiling))
					$ceiling = "null";
				$tax1 = substr($line, 19, 5);
				$tax2 = substr($line, 24, 5);
				$tax3 = substr($line, 29, 5);
				$tax4 = substr($line, 34, 5);
				$tax5 = substr($line, 39, 5);
				foreach (array($periodlength, $tableno, $floor, $tax1, $tax2, $tax3, $tax4, $tax5) as $field) {
					if (!preg_match('/^\\s*\\d+(?:\\.\\d+)?\\s*$/', $field))
						return "Line " . ($lineNumber + 1) . " contains an invalid numeric field.";
				}
				if (!isEmpty($ceiling) && !preg_match('/^\\s*\\d+(?:\\.\\d+)?\\s*$/', $ceiling))
					return "Line " . ($lineNumber + 1) . " contains an invalid ceiling value.";
				if (!in_array((int)$periodlength, array(14, 30), true) || trim($type) === '')
					return "Line " . ($lineNumber + 1) . " has an invalid period length or tax type.";
				$records[] = array($periodlength, $tableno, $floor, $ceiling, $type, $tax1, $tax2, $tax3, $tax4, $tax5);
			}
		}
		if (count($records) == 0)
			return "The file does not contain any tax-table records.";
		sql("delete from se_taxtable where year=$year");
		foreach ($records as $record) {
			list($periodlength, $tableno, $floor, $ceiling, $type, $tax1, $tax2, $tax3, $tax4, $tax5) = $record;
			sql("insert into se_taxtable (year, periodlength, tableno, floor, ceiling, type, tax1, tax2, tax3, tax4, tax5)
			     values ($year, $periodlength, $tableno, $floor, $ceiling, '$type', $tax1, $tax2, $tax3, $tax4, $tax5)");
		}
		return null;
	}
	
	if (isset($_POST['upload'])) {
		$fileSize = $_FILES['userfile']['size'];
		if ($fileSize > 0) {
			$fileName = $_FILES['userfile']['name'];
			$tmpName  = $_FILES['userfile']['tmp_name'];
			$fileType = $_FILES['userfile']['type'];
			$uploadError = import($tmpName);
		}
	}
	
	$rows = null;
	if (!isEmpty($tableno)) {
	    $selectSQL =
  		"select type,
		       floor,
		       ceiling,
		       tax1,
			   tax2,
			   tax3,
			   tax4,
			   tax5
		from se_taxtable
		where tableno=$tableno and periodlength=$periodlength
		";
		$rows = query($selectSQL);
	}
	$tables = rs2array(query("select distinct tableno from se_taxtable"));


?>
<head>
<title>Payroll - <?php etr("Swedish taxtable") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
title(tr("Swedish tax tables"))
?>

<div class=border>
<form action="se_taxtable.php" method="GET">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Table no") ?>:</div>
	<div class="col-12 col-md-auto"><?php combobox('tableno', $tables, $tableno, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Period length") ?>:</div>
	<div class="col-12 col-md-auto">
		<?php $selected = $periodlength == 14 ? 'checked' : '' ?>
		<input type=radio name=periodlength value='14' <?php echo $selected ?>>14</input>
		<?php $selected = $periodlength == 30 ? 'checked' : '' ?>
		<input type=radio name=periodlength value='30' <?php echo $selected ?>>30</input>
	</div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php searchButton('Search', 'search') ?></div>
</div>
</div>
</form>
</div>
<br/>
<?php
if ($rows != null) {
	echo "<table>";
	echo "<th>" . tr("Floor") . "</th>";
	echo "<th>" . tr("Ceiling") . "</th>";
	echo "<th>" . tr("Type") . "</th>";
	echo "<th>" . tr("Column 1") . "</th>";
	echo "<th>" . tr("Column 2") . "</th>";
	echo "<th>" . tr("Column 3") . "</th>";
	echo "<th>" . tr("Column 4") . "</th>";
	echo "<th>" . tr("Column 5") . "</th>";
	$floor = 0;
	$class = 'odd';
	$i = 0;
	while ($row = fetch($rows)) {
		echo "<tr class=$class>";
		echo "<td align=right>$row->floor</td>";
		echo "<td align=right>$row->ceiling</td>";
		echo "<td align=right>$row->type</td>";
		echo "<td align=right>$row->tax1</td>";
		echo "<td align=right>$row->tax2</td>";
		echo "<td align=right>$row->tax3</td>";
		echo "<td align=right>$row->tax4</td>";
		echo "<td align=right>$row->tax5</td>";
		echo "</tr>";
		$class = ($class == "odd" ? "even" : "odd");
		$i++;
	}
	echo "</table>";
}
?>
</form>
<hr/>
<form action="se_taxtable.php" method="POST" enctype="multipart/form-data">
<?php etr("Filename") ?>:  <input name="userfile" type="file"/>
<?php button('Upload', 'upload') ?>
<p class="form-text">Each line must be a 44-character fixed-width record: 2-digit period length, 1 type character, 2-digit table number, 7-digit floor, 7-digit ceiling, and five 5-character tax values. Do not use CSV, tabs, commas, or a header row.</p>
</form>
<?php if ($uploadError != null) { ?><div class="alert alert-danger mt-3" role="alert"><strong><?php etr("Upload failed") ?>:</strong> <?php echo htmlspecialchars($uploadError) ?></div><?php } ?>
<?php bottom() ?>

</body>
