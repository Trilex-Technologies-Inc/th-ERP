<?php
include('include.php');


$del_rowno = getParam('del_rowno');
if (!isEmpty($del_rowno)) {
	sql("delete from invoice_footer where rowno=$del_rowno");
}

if (isSave()) {
	$invoice_template = getParam("invoice_template");
	sql("update settings set invoice_template='$invoice_template'");
	
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$rowno = getParam("rowno_$i");
		$text = getParam("text_$i");
		sql("
		update invoice_footer set text='$text'
		where rowno=$rowno");
		$i++;
	}
	$text_new = getParam('text_new');
	if (!isEmpty($text_new)) {
		$text_new = prepNull(getParam("text_new"));
		$sql = "
		insert into invoice_footer (text)
		values ('$text_new')";
		sql($sql);
	}
}

$invoice_template = findValue("select invoice_template from settings");

$sql = "
select
  rowno,
  text
from invoice_footer
order by rowno
";

$rs = query($sql);

$templates = array();
$templates[] = array('standard', 'Standard');
$templates[] = array('se', 'Swedish');

?>

<html>
<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Invoice configuration") ?></title>
<?php
styleSheet();
?>
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Invoice configuration"));
?>

<form action="invoice_conf.php" method="POST">

<table>
<tr>
<td><?php etr("Template") ?>:</td>
<td>
<?php combobox('invoice_template', $templates, $invoice_template) ?>
</td>
</tr>
</table>
<br><br>

<table width='100%'>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Footer") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=rowno_$i value='$row->rowno'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	deleteIcon("invoice_conf.php?del_rowno=$row->rowno");
    echo "</td>";
    echo "<td>";
    textbox("text_$i", $row->text, 80);
    echo "</td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
<tr>
<td/>
<td><?php textBox('text_new', '', 80) ?></td>
</tr>
</table>
<br/>
<?php saveButton() ?>
</form>
<?php bottom() ?>
</body>
</html>
