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
<div class="card border-0 shadow-sm mb-3"><div class="card-body">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Dimension") ?>:</div>
	<div class="col-12 col-md-auto"><?php combobox("dimid", $dims, $dimid, false) ?></div>
	<div class="col-12 col-md-auto">
	</div><div class="col-12 col-md-auto"><?php etr("Group") ?>:</div>
	<div class="col-12 col-md-auto"><?php combobox('groupid', $groups, $groupid, true) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Name") ?>:</div>
	<div class="col-12 col-md-auto"><?php textbox("name", $name) ?></div>
	<div class="col-12 col-md-auto">
	</div><div class="col-12 col-md-auto"><?php etr("Accountno") ?>:</div>
	<div class="col-12 col-md-auto"><?php textbox("accountid", $accountid) ?></div>
</div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php searchButton() ?></div></div>

</div></div></div>
</form>

<form action="accounts.php" method=POST>
<div class="card border-0 shadow-sm mb-3">
<div class="card-header bg-body-tertiary"><div class="row fw-semibold align-items-center"><div class="col-2"><?php etr("Delete") ?></div><div class="col-3"><?php etr("Accountno") ?></div><div class="col-7"><?php etr("Name") ?></div></div></div>
<div class="list-group list-group-flush">
<?php
    $rs = query($selectSQL);
    while ($row = fetch_object($rs)) {
        echo "<div class='list-group-item'><div class='row align-items-center'><div class='col-2'>";
        deleteIcon("accounts.php?del_accountid=$row->accountid");
        echo "</div><div class='col-3'>$row->accountid</div>";
        echo "<div class='col-7'><a href='account.php?dimid=$dimid&accountid=$row->accountid'>$row->name</a></div></div></div>";
    }
?>
</div></div>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("account.php?dimid=$dimid") ?></div>
</div>
</div>
</form>
</body>
