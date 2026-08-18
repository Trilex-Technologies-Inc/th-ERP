<?php
	include('include.php');

	$accountid = getParam('accountid');
	$new = true;
	$name = "";
	$type = 0;
	if (isSave()) {
		$account_receivable = getParam('account_receivable');
		$account_payable = getParam('account_payable');
		$finished_goods = getParam('finished_goods');
		$cost_of_sales = getParam('cost_of_sales');
		$goods_received_suspense = getParam('goods_received_suspense');
		$vat_recoverable = getParam('vat_recoverable');
		$vat_payable = getParam('vat_payable');
		$default_sales = getParam('default_sales');
		$default_cash = getParam('default_cash');
		$inventory_adjustment = getParam('inventory_adjustment');
		$raw_material = getParam('raw_material');
		$sql = "update accountconf set
		        account_receivable=$account_receivable,
				account_payable=$account_payable,
				finished_goods=$finished_goods,
				cost_of_sales=$cost_of_sales,
				goods_received_suspense=$goods_received_suspense,
				default_sales=$default_sales,
				default_cash=$default_cash,
				vat_recoverable=$vat_recoverable,
				vat_payable=$vat_payable,
				raw_material=$raw_material,
				inventory_adjustment=$inventory_adjustment
				";
		sql($sql);
	}

	$sql =
	"select account_receivable,
		   account_payable,
		   finished_goods,
		   cost_of_sales,
		   goods_received_suspense,
		   vat_recoverable,
		   vat_payable,
		   default_sales,
		   default_cash,
		   raw_material,
		   inventory_adjustment
	from accountconf
	";
	$row = find($sql);

	$accounts = rs2array(query("select accountid, accountid, name from account"));
	$assets_accounts = rs2array(query("select a.accountid, name
	                                   from account a
									   join account_group g on g.accountid=a.accountid and groupid=" . GROUPID_ASSETS));
	$liabilities_accounts = rs2array(query("select a.accountid, a.accountid, name
	                                   from account a
									   join account_group g on g.accountid=a.accountid and groupid=" . GROUPID_LIABILITIES));
	$expenses_accounts = rs2array(query("select a.accountid, a.accountid, name
	                                   from account a
									   join account_group g on g.accountid=a.accountid and groupid=" . GROUPID_EXPENSES));
	$revenue_accounts = rs2array(query("select a.accountid, a.accountid, name
	                                   from account a
									   join account_group g on g.accountid=a.accountid and groupid=" . GROUPID_REVENUES));

?>
<head>
<title>thERP - <?php etr("Account configuration") ?></title>
<?php
metatag();
styleSheet();
?>
</head>

<body>
<?php include("menubar.php") ?>
<?php
title(tr("Configuration") . " > " . tr("Account configuration"))
?>

<main class="container-fluid px-0">
<section class="card border-0 shadow-sm mb-4">
	<div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
		<div class="d-flex align-items-center gap-3">
			<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning fs-4 flex-shrink-0" aria-hidden="true">≡</span>
			<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Accounting setup") ?></span><h1 class="h3 fw-bold mt-1 mb-1"><?php etr("Account configuration") ?></h1><p class="text-secondary mb-0"><?php etr("Choose the ledger accounts used automatically by transactions and inventory") ?></p></div>
		</div>
		<a class="btn btn-outline-secondary" href="configuration.php">&#8592; <?php etr("Accounting settings") ?></a>
	</div>
</section>

<form action="accountconf.php" method="POST">
<div class="row g-4">
	<div class="col-12 col-xl-6">
		<section class="card border-0 shadow-sm h-100">
			<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Daily transactions") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Default accounts") ?></h2></div>
			<div class="card-body p-4"><div class="row g-4">
				<div class="col-12"><label class="form-label fw-semibold" for="default_cash"><?php etr("Default cash") ?></label><?php comboBox("default_cash", $assets_accounts, $row->default_cash, false) ?><div class="form-text"><?php etr("Cash account used for payments and receipts") ?></div></div>
				<div class="col-12"><label class="form-label fw-semibold" for="default_sales"><?php etr("Default sales") ?></label><?php comboBox("default_sales", $revenue_accounts, $row->default_sales, false) ?><div class="form-text"><?php etr("Revenue account used for standard sales") ?></div></div>
			</div></div>
		</section>
	</div>

	<div class="col-12 col-xl-6">
		<section class="card border-0 shadow-sm h-100">
			<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Customers and suppliers") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Receivables and payables") ?></h2></div>
			<div class="card-body p-4"><div class="row g-4">
				<div class="col-12"><label class="form-label fw-semibold" for="account_receivable"><?php etr("Account receivable") ?></label><?php comboBox("account_receivable", $assets_accounts, $row->account_receivable, false) ?></div>
				<div class="col-12"><label class="form-label fw-semibold" for="account_payable"><?php etr("Account payable") ?></label><?php comboBox("account_payable", $liabilities_accounts, $row->account_payable, false) ?></div>
			</div></div>
		</section>
	</div>

	<div class="col-12 col-xl-6">
		<section class="card border-0 shadow-sm h-100">
			<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Tax accounting") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("VAT accounts") ?></h2></div>
			<div class="card-body p-4"><div class="row g-4">
				<div class="col-12"><label class="form-label fw-semibold" for="vat_payable"><?php etr("VAT payable") ?></label><?php comboBox("vat_payable", $liabilities_accounts, $row->vat_payable, false) ?></div>
				<div class="col-12"><label class="form-label fw-semibold" for="vat_recoverable"><?php etr("VAT recoverable") ?></label><?php comboBox("vat_recoverable", $assets_accounts, $row->vat_recoverable, false) ?></div>
			</div></div>
		</section>
	</div>

	<div class="col-12 col-xl-6">
		<section class="card border-0 shadow-sm h-100">
			<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Stock and production") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Inventory accounts") ?></h2></div>
			<div class="card-body p-4"><div class="row g-4">
				<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="finished_goods"><?php etr("Finished goods inventory") ?></label><?php comboBox("finished_goods", $assets_accounts, $row->finished_goods, false) ?></div>
				<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="raw_material"><?php etr("Raw material inventory") ?></label><?php comboBox("raw_material", $assets_accounts, $row->raw_material, false) ?></div>
				<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="cost_of_sales"><?php etr("Cost of sales") ?></label><?php comboBox("cost_of_sales", $expenses_accounts, $row->cost_of_sales, false) ?></div>
				<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="inventory_adjustment"><?php etr("Inventory adjustment") ?></label><?php comboBox("inventory_adjustment", $expenses_accounts, $row->inventory_adjustment, false) ?></div>
				<div class="col-12"><label class="form-label fw-semibold" for="goods_received_suspense"><?php etr("Goods received suspense") ?></label><?php comboBox("goods_received_suspense", $liabilities_accounts, $row->goods_received_suspense, false) ?></div>
			</div></div>
		</section>
	</div>
</div>

<div class="card border-0 shadow-sm mt-4"><div class="card-body px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3"><span class="text-secondary small"><?php etr("Changes affect future automatic accounting entries") ?></span><?php saveButton() ?></div></div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>
</body>
