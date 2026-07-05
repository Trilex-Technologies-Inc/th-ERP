<?php
include('include.php');

$del_policyid = getParam("del_policyid");
if (!isEmpty($del_policyid)) {
	sql("delete from policy_description where policyid=$del_policyid");
	sql("delete from policy_attribute where policyid=$del_policyid");
	sql("delete from policy_payitem where policyid=$del_policyid");
	sql("delete from policy where policyid=$del_policyid");
}

?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Policies") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php", "policy") ?>
<?php title(tr("Policies")) ?>

<form action="policies.php" method="POST">
<table>
<?php

$sql = "select
          p.policyid,
          description
        from policy p
        left outer join policy_description d on d.policyid=p.policyid and language='" . getLanguage() . "'
        order by policyid
        ";
$q = sql($sql);
echo "<th>" .tr("Delete") . "</th>";
echo "<th>" .tr("Id") . "</th>";
echo "<th>" . tr("Description") . "</th>\n";
$class = "odd";
$runningno = 0;
while ($rec = fetch($q)) {
	echo "<tr class='$class'>";
	echo "<td align=center>";
	deleteIcon("policies.php?del_policyid=$rec->policyid");
	echo "</td>";
	echo "<td>$rec->policyid</td>";
	echo "<td><a href='policy.php?policyid=$rec->policyid'>$rec->description</a></td>";
	echo "</tr>\n";
	$class = ($class == "odd" ? "even" : "odd");
}
?>
<tr height="10"/>
<tr>
<td>
<?php button("Add", "add", "policy.php") ?>
</td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
</html>
