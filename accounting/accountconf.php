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
<table>
<tr>
	<td><?php etr("Default cash") ?>:</td>
	<td><?php comboBox("default_cash", $assets_accounts, $row->default_cash, false) ?></td>
</tr>
<tr>
	<td><?php etr("Default sales") ?>:</td>
	<td><?php comboBox("default_sales", $revenue_accounts, $row->default_sales, false) ?></td>
</tr>
<tr>
	<td><?php etr("Account receivable") ?>:</td>
	<td><?php comboBox("account_receivable", $assets_accounts, $row->account_receivable, false) ?></td>
</tr>
<tr>
	<td><?php etr("Account payable") ?>:</td>
	<td><?php comboBox("account_payable", $liabilities_accounts, $row->account_payable, false) ?></td>
</tr>
<tr>
	<td><?php etr("Finished goods inventory") ?>:</td>
	<td><?php comboBox("finished_goods", $assets_accounts, $row->finished_goods, false) ?></td>
</tr>
<tr>
	<td><?php etr("Raw material inventory") ?>:</td>
	<td><?php comboBox("raw_material", $assets_accounts, $row->raw_material, false) ?></td>
</tr>
<tr>
	<td><?php etr("Cost of sales") ?>:</td>
	<td><?php comboBox("cost_of_sales", $expenses_accounts, $row->cost_of_sales, false) ?></td>
</tr>
<tr>
	<td><?php etr("Goods received suspense") ?>:</td>
	<td><?php comboBox("goods_received_suspense", $liabilities_accounts, $row->goods_received_suspense, false) ?></td>
</tr>
<tr>
	<td><?php etr("VAT payable") ?>:</td>
	<td><?php comboBox("vat_payable", $liabilities_accounts, $row->vat_payable, false) ?></td>
</tr>
<tr>
	<td><?php etr("VAT recoverable") ?>:</td>
	<td><?php comboBox("vat_recoverable", $assets_accounts, $row->vat_recoverable, false) ?></td>
</tr>
<tr>
	<td><?php etr("Inventory adjustment") ?>:</td>
	<td><?php comboBox("inventory_adjustment", $expenses_accounts, $row->inventory_adjustment, false) ?></td>
</tr>
<tr>
<td colspan=2>
<input type="submit" name="save" value="Save"/>
&nbsp;
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
