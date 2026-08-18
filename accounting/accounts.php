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

<main class="container-fluid px-0">
<section class="card border-0 shadow-sm mb-4">
	<div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
		<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Chart of accounts") ?></span><h1 class="h3 fw-bold mt-1 mb-1"><?php etr("Accounts") ?></h1><p class="text-secondary mb-0"><?php etr("Search and manage general ledger accounts") ?></p></div>
		<a class="btn btn-primary" href="account.php?dimid=<?php echo urlencode($dimid) ?>">+ <?php etr("New account") ?></a>
	</div>
</section>

<form action="accounts.php" method="GET">
<section class="card border-0 shadow-sm mb-4">
	<div class="card-header bg-white px-4 py-3"><h2 class="h5 fw-bold mb-1"><?php etr("Filter accounts") ?></h2><p class="text-secondary small mb-0"><?php etr("Narrow the chart by dimension, group, name, or number") ?></p></div>
	<div class="card-body p-4">
		<div class="row g-3 align-items-end">
			<div class="col-12 col-md-6 col-xl-3"><label class="form-label fw-semibold" for="dimid"><?php etr("Dimension") ?></label><?php combobox("dimid", $dims, $dimid, false) ?></div>
			<div class="col-12 col-md-6 col-xl-3"><label class="form-label fw-semibold" for="groupid"><?php etr("Group") ?></label><?php combobox('groupid', $groups, $groupid, true) ?></div>
			<div class="col-12 col-md-6 col-xl-3"><label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label><?php textbox("name", $name) ?></div>
			<div class="col-12 col-md-6 col-xl-3"><label class="form-label fw-semibold" for="accountid"><?php etr("Account number") ?></label><?php textbox("accountid", $accountid) ?></div>
		</div>
	</div>
	<div class="card-footer bg-white d-flex justify-content-end gap-2 px-4 py-3"><a class="btn btn-outline-secondary" href="accounts.php?dimid=<?php echo urlencode($dimid) ?>"><?php etr("Clear") ?></a><?php searchButton() ?></div>
</section>
</form>

<section class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3"><div><span class="text-secondary small text-uppercase fw-bold"><?php etr("General ledger") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Account list") ?></h2></div></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-light"><tr><th class="text-center" style="width:90px"><?php etr("Delete") ?></th><th style="width:180px"><?php etr("Account number") ?></th><th><?php etr("Name") ?></th><th class="text-end" style="width:90px"><span class="visually-hidden"><?php etr("Open") ?></span></th></tr></thead><tbody>
<?php
    $rs = query($selectSQL);
	$count = 0;
    while ($row = fetch_object($rs)) {
		$count++;
		$accountHref = "account.php?dimid=" . urlencode($dimid) . "&accountid=" . urlencode($row->accountid);
		echo "<tr><td class='text-center'>";
        deleteIcon("accounts.php?del_accountid=$row->accountid");
		echo "</td><td class='font-monospace text-secondary'>" . htmlspecialchars($row->accountid) . "</td>";
		echo "<td><a class='fw-semibold text-decoration-none' href='" . htmlspecialchars($accountHref) . "'>" . htmlspecialchars($row->name) . "</a></td><td class='text-end'><a class='btn btn-sm btn-outline-primary' href='" . htmlspecialchars($accountHref) . "' aria-label='" . htmlspecialchars(tr("Open") . " " . $row->name) . "'>&#8594;</a></td></tr>";
    }
	if ($count == 0)
		echo "<tr><td colspan='4' class='text-center py-5'><div class='text-secondary mb-2'>" . tr("No accounts match the selected filters") . ".</div><a href='accounts.php?dimid=" . urlencode($dimid) . "'>" . tr("Clear filters") . "</a></td></tr>";
?>
</tbody></table></div>
<div class="card-footer bg-white d-flex justify-content-between align-items-center px-4 py-3"><span class="text-secondary small"><?php echo $count ?> <?php etr("accounts") ?></span><a class="btn btn-primary" href="account.php?dimid=<?php echo urlencode($dimid) ?>">+ <?php etr("New account") ?></a></div>
</section>
</main>
<?php bottom() ?>
</body>
