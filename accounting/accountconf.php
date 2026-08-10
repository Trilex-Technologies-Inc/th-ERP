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

<form action="accountconf.php" method="POST">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Default cash") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("default_cash", $assets_accounts, $row->default_cash, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Default sales") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("default_sales", $revenue_accounts, $row->default_sales, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Account receivable") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("account_receivable", $assets_accounts, $row->account_receivable, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Account payable") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("account_payable", $liabilities_accounts, $row->account_payable, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Finished goods inventory") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("finished_goods", $assets_accounts, $row->finished_goods, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Raw material inventory") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("raw_material", $assets_accounts, $row->raw_material, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Cost of sales") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("cost_of_sales", $expenses_accounts, $row->cost_of_sales, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Goods received suspense") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("goods_received_suspense", $liabilities_accounts, $row->goods_received_suspense, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("VAT payable") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("vat_payable", $liabilities_accounts, $row->vat_payable, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("VAT recoverable") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("vat_recoverable", $assets_accounts, $row->vat_recoverable, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Inventory adjustment") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox("inventory_adjustment", $expenses_accounts, $row->inventory_adjustment, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
<input type="submit" name="save" value="Save"/>
&nbsp;
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
