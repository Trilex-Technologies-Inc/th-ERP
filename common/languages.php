<?php
include('include.php');

checkPermission(PERMISSION_ADMINISTRATE_USERS);

$del_language = getParam('del_language');
if (!isEmpty($del_language)) {
	$del_language = mysqli_real_escape_string(db_connection(), (string)$del_language);
	$sql = "
	delete from language
	where language='$del_language'";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$language = mysqli_real_escape_string(db_connection(), (string)getParam("language_$i"));
		$description = mysqli_real_escape_string(db_connection(), (string)getParam("description_$i"));
		sql("update language set description='$description' where language='$language'");
		$i++;
	}
	$language_new = getParam('language_new');
	$description_new = getParam('description_new');
	if (!isEmpty($description_new)) {
		$language_new = mysqli_real_escape_string(db_connection(), (string)$language_new);
		$description_new = mysqli_real_escape_string(db_connection(), (string)$description_new);
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
	$languageCode = htmlspecialchars((string)$row->language, ENT_QUOTES, 'UTF-8');
	$languageDescription = htmlspecialchars((string)$row->description, ENT_QUOTES, 'UTF-8');
	echo "<input type=hidden name=language_$i value='$languageCode'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	deleteIcon("languages.php?del_language=" . rawurlencode((string)$row->language));
    echo "</td>";
    echo "<td>$languageCode</td>";
    echo "<td>";
    echo "<input type=text name='description_$i' value='$languageDescription'/>";
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
