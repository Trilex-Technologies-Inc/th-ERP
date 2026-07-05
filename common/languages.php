<?php
include('include.php');

$del_language = getParam('del_language');
if (!isEmpty($del_language)) {
	$sql = "
	delete from language
	where language=$del_language";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$language = getParam("language_$i");
		$description = getParam("description_$i");
		sql("update language set description='$description' where language='$language'");
		$i++;
	}
	$language_new = getParam('language_new');
	$description_new = getParam('description_new');
	if (!isEmpty($description_new)) {
		$sql = "
		insert into language (language, description)
		values ('$language_new', '$description_new')";
		sql($sql);
	}
}

$sql = "
select
  language,
  description
from language
";

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Languages") ?></title>
<?php styleSheet() ?>
</head>
<body>

<?php
include("menubar.php");
title(tr("Languages"))
?>

<form action="languages.php" method="POST">
<input type=hidden name=policyid value='<?php echo $policyid ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Code") ?></th>
<th><?php echo tr("Description") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=language_$i value='$row->language'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	deleteIcon("languages.php?del_language=$row->language");
    echo "</td>";
    echo "<td>$row->language</td>";
    echo "<td>";
    echo "<input type=text name='description_$i' value='$row->description'/>";
    echo "</td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
<tr>
<td/>
<td><input type=text name=language_new size='5'/></td>
<td><input type=text name=description_new /></td>
</tr>
</table>
<br/>
<?php saveButton() ?>
</form>
</body>
</html>
