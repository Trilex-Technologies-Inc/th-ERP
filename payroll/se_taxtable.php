<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

	$year = date('Y');
	$tableno = getParam('tableno');
	$periodlength = getParam('periodlength', 30);

	function import($filename, $year = null)
	{
		set_time_limit(200);
		if ($year == null)
			$year = date('Y');
		sql("delete from se_taxtable where year=$year");
		$fh = fopen($filename, 'r');
		while (!feof($fh)) {
			$line = fgets($fh);
			if (strlen(trim($line)) > 0) {
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
				sql("insert into se_taxtable (year, periodlength, tableno, floor, ceiling, type, tax1, tax2, tax3, tax4, tax5)
				     values ($year, $periodlength, $tableno, $floor, $ceiling, '$type', $tax1, $tax2, $tax3, $tax4, $tax5)");
			}
		}
		fclose($fh);		
	}
	
	if (isset($_POST['upload'])) {
		$fileSize = $_FILES['userfile']['size'];
		if ($fileSize > 0) {
			$fileName = $_FILES['userfile']['name'];
			$tmpName  = $_FILES['userfile']['tmp_name'];
			$fileType = $_FILES['userfile']['type'];
			import($tmpName);
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
<table>
<tr>
	<td><?php etr("Table no") ?>:</td>
	<td><?php combobox('tableno', $tables, $tableno, false) ?></td>
</tr>
<tr>
	<td><?php etr("Period length") ?>:</td>
	<td>
		<?php $selected = $periodlength == 14 ? 'checked' : '' ?>
		<input type=radio name=periodlength value='14' <?php echo $selected ?>>14</input>
		<?php $selected = $periodlength == 30 ? 'checked' : '' ?>
		<input type=radio name=periodlength value='30' <?php echo $selected ?>>30</input>
	</td>
</tr>
<tr>
<td colspan=2><?php searchButton('Search', 'search') ?></td>
</tr>
</table>
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
</form>
<?php bottom() ?>

</body>
