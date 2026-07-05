<?php
	include('include.php');

    $name = getParam('name');
    $accountid = getParam('accountid');
    $groupid = getParam('groupid');
    $dimid = getParam('dimid', 1);

	$del_accountid = getParam('del_accountid');
	if (!isEmpty($del_accountid)) {
		sql("delete from account_group where accountid=$del_accountid");
		sql("delete from account where accountid=$del_accountid");
	}

	$selectSQL = "
	select
	    a.accountid,
	    name
	from account a ";
	if (!isEmpty($groupid))
		$selectSQL .= " join account_group ag on ag.accountid=a.accountid ";
	$selectSQL .= " where name like '$name%' and a.dimid=$dimid 
	                and a.accountid like '$accountid%'";
	if (!isEmpty($groupid))
		$selectSQL .= " and groupid=$groupid ";

	$groups = rs2array(query("select groupid, description from accountgroup"));
	$dims = rs2array(query("select dimid, name from dimension"));

?>

<head>
<title>thERP - <?php etr("Accounts") ?></title>
<?php 
metatag();
styleSheet();
?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Accounts")) ?>

<form action="accounts.php" method="GET">
<div class="border">
<table>
<tr>
	<td><?php etr("Dimension") ?>:</td>
	<td><?php combobox("dimid", $dims, $dimid, false) ?></td>
	<td width=20/>
	<td><?php etr("Group") ?>:</td>
	<td><?php combobox('groupid', $groups, $groupid, true) ?></td>
</tr>
<tr>
	<td><?php etr("Name") ?>:</td>
	<td><?php textbox("name", $name) ?></td>
	<td width=20/>
	<td><?php etr("Accountno") ?>:</td>
	<td><?php textbox("accountid", $accountid) ?></td>
</tr>
<tr><td><?php searchButton() ?></td></tr>
</tr>
</table>
</div>
</form>

<form action="accounts.php" method=POST>
<table width='100%'>
<th width="10%"><?php etr("Delete") ?></th>
<th width="10%"><?php etr("Accountno") ?></th>
<th width="80%"><?php etr("Name") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		deleteColumn("accounts.php?del_accountid=$row->accountid");
        echo "<td align=right>$row->accountid</td>";
        echo "<td><a href='account.php?dimid=$dimid&accountid=$row->accountid'>$row->name</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php newButton("account.php?dimid=$dimid") ?></td>
</tr>
</table>
</form>
</body>
