<?php
include('include.php');
include('salesorder.inc.php');

checkPermission(PERMISSIONID_SELL);

$orderid = getParam('orderid');
$action = getParam('action');
$mess = null;

function pos_create_order($locationid)
{
	$user = getUser();
	sql("insert into salesorder (orderdate, customerid, createdby, locationid)
		 values (now(), " . CUSTOMERID_CASH . ", '$user', $locationid)");
	return insert_id();
}

if ($action == 'new') {
	$locationid = getParam('locationid', 1);
	$orderid = pos_create_order($locationid);
	header('Location: posclient.php?orderid=' . urlencode($orderid));
	die;
}

// Like the desktop client, the first product can start a sale immediately.
if (isEmpty($orderid) && $action == 'add')
	$orderid = pos_create_order(getParam('locationid', 1));

if (!isEmpty($orderid)) {
	$invoiceTransid = findValue("select invoice_transid from salesorder where orderid=$orderid");
	$cancelled = findValue("select cancelled from salesorder where orderid=$orderid", 0);
	$editable = isEmpty($invoiceTransid) && !$cancelled;

	if ($editable && ($action == 'save' || $action == 'pay')) {
		$locationid = getParam('locationid');
		sql("update salesorder set locationid=$locationid where orderid=$orderid");
		sql("update user set locationid=$locationid where username='" . getUser() . "'");
		$count = getParam('count', 0);
		for ($i = 0; $i < $count; $i++) {
			$no = getParam("no_$i");
			$quantity = getParam("quantity_$i");
			$unitprice = getParam("unitprice_$i");
			sql("update salesorder_item set quantity=$quantity, unitprice=$unitprice where orderid=$orderid and no=$no");
		}
	}

	if ($editable && $action == 'add') {
		$productid = getParam('productid');
		if (!isEmpty($productid)) {
			$result = add_orderitem($orderid, $productid, getParam('quantity_new', 1), null, '');
			$mess = getError($result);
		}
	}

	$delNo = getParam('line');
	if ($editable && $action == 'delete' && !isEmpty($delNo))
		sql("delete from salesorder_item where orderid=$orderid and no=$delNo");

	if ($editable && $action == 'pay') {
		$total = getSalesOrderTotalIncVat($orderid);
		if ($total > 0) {
			$received = getParam('received', $total);
			$paymentMethod = getParam('payment_method', 'cash');
			if ($received < $total) {
				$mess = tr('The received amount is less than the amount due.');
			} else {
				tx('finish_cashorder', array($orderid, $received));
				if (findValue("show tables like 'pos_payment'", null) != null) {
					$paymentMethod = addslashes($paymentMethod);
					sql("insert into pos_payment (orderid, methodid, amount, createdby)
					     values ($orderid, '$paymentMethod', $total, '" . getUser() . "')");
				}
				header('Location: posclient.php?orderid=' . urlencode($orderid) . '&completed=1');
				die;
			}
		} else {
			$mess = tr('Add at least one product before taking payment.');
		}
	}
}

$locations = rs2array(query('select locationid, name from location'));
$products = query("select productid, model, description, barcode, quantity from product where active=1 order by model");
$lowStockCount = findValue("select count(*) from product where active=1 and quantity <= 5", 0);
$todaySales = find("select count(distinct so.orderid) as sale_count,
                   coalesce(sum(si.quantity * si.unitprice * (1 + si.vat / 100)), 0) as sale_total
                   from salesorder so
                   left join salesorder_item si on si.orderid=so.orderid
                   where so.customerid=" . CUSTOMERID_CASH . "
                   and so.invoice_transid is not null and so.cancelled=0
                   and so.orderdate >= curdate()
                   and so.orderdate < date_add(curdate(), interval 1 day)");
$todaySaleRows = query("select so.orderid, so.invoice_transid, so.orderdate,
                        coalesce(sum(si.quantity * si.unitprice * (1 + si.vat / 100)), 0) as sale_total
                        from salesorder so
                        left join salesorder_item si on si.orderid=so.orderid
                        where so.customerid=" . CUSTOMERID_CASH . "
                        and so.invoice_transid is not null and so.cancelled=0
                        and so.orderdate >= curdate()
                        and so.orderdate < date_add(curdate(), interval 1 day)
                        group by so.orderid, so.orderdate
                        order by so.orderid desc");
$locationid = findValue("select locationid from user where username='" . getUser() . "'", 1);
$items = null;
$total = 0;
$paid = false;
$editable = true;
$orderdate = time();
if (!isEmpty($orderid)) {
	$order = find("select unix_timestamp(orderdate) orderdate, locationid, invoice_transid, cancelled from salesorder where orderid=$orderid and customerid=" . CUSTOMERID_CASH);
	if ($order == null) {
		$orderid = null;
		$mess = tr('Sale not found.');
	} else {
		$orderdate = $order->orderdate;
		$locationid = $order->locationid;
		$editable = isEmpty($order->invoice_transid) && !$order->cancelled;
		$total = getSalesOrderTotalIncVat($orderid);
		$paid = findValue("select sum(amount) from receipt_allocation where orderid=$orderid", 0) >= $total && $total > 0;
		$items = query("select si.no, si.productid, p.model, si.quantity, si.unitprice, si.vat
			from salesorder_item si join product p on p.productid=si.productid
			where si.orderid=$orderid and si.productid != " . PRODUCTID_ROUNDING . " order by si.no");
	}
}
?>

<head>
	<title>thERP - <?php etr('Point of sale') ?></title>
	<?php styleSheet();
	include_common(); ?>
</head>

<body>
	<?php menubar('index.php');
	title(tr('Point of sale')); ?>

	<style>
		.erp-pos-sales-detail { margin: 0 auto 14px; overflow: hidden; background: #fff; border: 1px solid #dfe3e8; border-radius: 12px; box-shadow: 0 5px 18px rgba(28,39,60,.06) }
		.erp-pos-sales-detail-head { display: flex; align-items: center; justify-content: space-between; padding: 10px 18px; color: #344054; background: #f8f9fb; border-bottom: 1px solid #e8ebef; font-size: .8rem }
		.erp-pos-sales-detail-head span { color: #87909d; font-size: .72rem }
		.erp-pos-sale-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .75rem; padding: 10px 18px; color: #344054; border-bottom: 1px solid #edf0f3; font-size: .78rem; text-decoration: none }
		.erp-pos-sale-row:last-child { border-bottom: 0 }.erp-pos-sale-row:hover { color: #4455bc; background: #f8f9ff }.erp-pos-sale-row time { color: #87909d }.erp-pos-sale-row strong { color: #1b8e5a; text-align: right }.erp-pos-sales-empty { padding: 12px 18px; color: #87909d; font-size: .78rem }
		.erp-pos {
			max-width: 1440px;
			margin: auto
		}

		.erp-pos-shell {
			display: grid;
			grid-template-columns: minmax(0, 1.55fr) minmax(380px, .8fr);
			min-height: 720px;
			overflow: hidden;
			background: #f5f7fa;
			border: 1px solid #d7dce3;
			border-radius: 16px;
			box-shadow: 0 15px 45px rgba(28, 39, 60, .12)
		}

		.erp-pos-catalog {
			display: flex;
			min-width: 0;
			flex-direction: column;
			padding: 22px
		}

		.erp-pos-top {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 18px
		}

		.erp-pos-top h1 {
			margin: 0;
			font-size: 1.45rem !important
		}

		.erp-pos-top p {
			margin: 2px 0 0;
			color: #718096;
			font-size: .78rem
		}

		.erp-pos-search {
			position: relative;
			margin-left: auto;
			width: min(380px, 45%)
		}

		.erp-pos-search input {
			width: 100%;
			height: 44px;
			padding: 0 16px 0 42px;
			border: 1px solid #d7dce3;
			border-radius: 10px
		}

		.erp-pos-search span {
			position: absolute;
			left: 15px;
			top: 11px;
			color: #87909d
		}

		.erp-product-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 11px;
			overflow: auto;
			padding: 2px
		}

		.erp-product {
			min-height: 112px;
			padding: 14px;
			color: #243047;
			background: #fff;
			border: 1px solid #dfe3e8;
			border-radius: 12px;
			text-align: left;
			transition: .15s
		}

		.erp-product:hover {
			border-color: #667eea;
			box-shadow: 0 7px 18px rgba(66, 82, 160, .13);
			transform: translateY(-2px)
		}

		.erp-product strong,
		.erp-product small {
			display: block
		}

		.erp-product strong {
			overflow: hidden;
			font-size: .86rem;
			text-overflow: ellipsis
		}

		.erp-product small {
			margin-top: 6px;
			color: #7b8492;
			font-size: .7rem
		}

		.erp-product em {
			display: block;
			margin-top: 12px;
			color: #4e5fc7;
			font-size: .75rem;
			font-style: normal;
			font-weight: 800
		}

		.erp-empty {
			grid-column: 1/-1;
			padding: 60px;
			text-align: center;
			color: #89919c
		}

		.erp-cart {
			display: flex;
			min-width: 0;
			flex-direction: column;
			background: #fff;
			border-left: 1px solid #dfe3e8
		}

		.erp-cart-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 19px 20px;
			border-bottom: 1px solid #e5e8ec
		}

		.erp-cart-head h2 {
			margin: 0;
			font-size: 1.1rem !important
		}

		.erp-cart-head small {
			display: block;
			color: #87909d
		}

		.erp-cart-head select {
			max-width: 145px
		}

		.erp-cart-lines {
			flex: 1;
			overflow: auto;
			padding: 10px 18px
		}

		.erp-cart-empty {
			display: grid;
			height: 100%;
			min-height: 250px;
			place-items: center;
			color: #9098a4;
			text-align: center
		}

		.erp-cart-line {
			display: grid;
			grid-template-columns: 1fr 72px 82px 30px;
			gap: 8px;
			align-items: center;
			padding: 13px 2px;
			border-bottom: 1px solid #edf0f3
		}

		.erp-cart-line strong,
		.erp-cart-line small {
			display: block
		}

		.erp-cart-line small {
			color: #87909d;
			font-size: .7rem
		}

		.erp-cart-line input {
			width: 100%;
			height: 34px;
			padding: 5px;
			text-align: center
		}

		.erp-cart-line b {
			text-align: right;
			font-size: .8rem
		}

		.erp-cart-line a {
			color: #c43e4f;
			font-size: 1.25rem;
			text-align: center;
			text-decoration: none
		}

		.erp-cart-summary {
			padding: 18px 20px;
			background: #f8f9fb;
			border-top: 1px solid #e1e5ea
		}

		.erp-total {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 14px
		}

		.erp-total span {
			font-weight: 700
		}

		.erp-total strong {
			font-size: 1.65rem
		}

		.erp-pos-actions {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 9px
		}

		.erp-pos-actions button,
		.erp-pos-actions a {
			display: grid;
			min-height: 45px;
			place-items: center;
			border-radius: 9px;
			font-weight: 750;
			text-decoration: none
		}

		.erp-pay {
			grid-column: 1/-1;
			min-height: 58px !important;
			color: #fff;
			background: #18a66a;
			border: 0;
			font-size: 1rem
		}

		.erp-pay:disabled {
			background: #aab4b0
		}

		.erp-secondary {
			color: #344054;
			background: #fff;
			border: 1px solid #ccd2da
		}

		.erp-new {
			color: #fff;
			background: #4455bc;
			border: 1px solid #4455bc
		}

		.erp-pay-dialog {
			width: min(420px, 92vw);
			padding: 0;
			border: 0;
			border-radius: 14px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, .3)
		}

		.erp-pay-dialog::backdrop {
			background: rgba(21, 28, 40, .55)
		}

		.erp-pay-dialog form {
			padding: 25px
		}

		.erp-pay-dialog h2 {
			margin: 0 0 6px
		}

		.erp-pay-dialog .due {
			display: flex;
			justify-content: space-between;
			margin: 18px 0;
			padding: 15px;
			background: #f3f5f8;
			border-radius: 9px
		}

		.erp-pay-dialog input {
			width: 100%;
			height: 48px;
			margin: 7px 0 16px;
			font-size: 1.15rem
		}

		.erp-dialog-actions {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 9px
		}

		.erp-dialog-actions button {
			min-height: 45px;
			border-radius: 8px
		}

		.erp-dialog-actions .confirm {
			color: #fff;
			background: #18a66a;
			border: 0
		}

		@media(max-width:1100px) {
			.erp-pos-shell {
				grid-template-columns: 1fr
			}

			.erp-cart {
				border-top: 1px solid #dfe3e8;
				border-left: 0
			}

			.erp-product-grid {
				grid-template-columns: repeat(3, 1fr)
			}
		}

		@media(max-width:600px) {
			.erp-pos-catalog {
				padding: 14px
			}

			.erp-pos-top {
				align-items: stretch;
				flex-direction: column
			}

			.erp-pos-search {
				width: 100%;
				margin: 0
			}

			.erp-product-grid {
				grid-template-columns: repeat(2, 1fr)
			}

			.erp-cart-line {
				grid-template-columns: 1fr 62px 70px 26px
			}
		}
	</style>
	<main class="erp-pos">
		<?php if ($mess) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($mess) ?></div><?php } ?>
		<?php if (getParam('completed')) { ?><div class="alert alert-success d-flex justify-content-between align-items-center"><span><?php etr('The sale is complete. The receipt is ready to print.') ?></span><a class="btn btn-primary btn-sm" href="invoice_pdf.php?orderid=<?php echo urlencode($orderid) ?>&type=receipt" onclick="return thERPPrintDocument(this.href)"><?php etr('Print receipt') ?></a></div><?php } ?>

		<div class="erp-pos-today">
			<div><span><?php etr("Today's sales") ?></span><strong><?php echo formatMoney($todaySales->sale_total) ?></strong></div><small><?php echo (int)$todaySales->sale_count ?> <?php etr('completed sales') ?> · <?php echo (int)$lowStockCount ?> <?php etr('low-stock items') ?></small>
		</div>
		<div class="erp-pos-sales-detail">
			<div class="erp-pos-sales-detail-head"><strong><?php etr("Today's sale details") ?></strong><span><?php echo date(DATE_PATTERN) ?></span></div><?php $todayDetailCount = 0;
																																							while ($todaySale = fetch($todaySaleRows)) {
																																								$todayDetailCount++; ?><a href="../accounting/transaction.php?transactionid=<?php echo urlencode($todaySale->invoice_transid) ?>" class="erp-pos-sale-row"><span>#<?php echo htmlspecialchars($todaySale->orderid) ?></span><time><?php echo date('H:i', strtotime($todaySale->orderdate)) ?></time><strong><?php echo formatMoney($todaySale->sale_total) ?></strong></a><?php }
																																																																																																																																																			if (!$todayDetailCount) { ?><div class="erp-pos-sales-empty"><?php etr('No completed sales today') ?></div><?php } ?>
		</div>
		<nav class="erp-pos-toolbar" aria-label="<?php etr('POS quick actions') ?>">
			<a href="posclient.php?action=new"><span aria-hidden="true">＋</span><strong><?php etr('New sale') ?></strong><small><?php etr('Start an empty cart') ?></small></a>
			<a href="sales.php?starttime=<?php echo urlencode(strtotime('today')) ?>"><span aria-hidden="true">▤</span><strong><?php etr('Sales history') ?></strong><small><?php etr('Review completed orders') ?></small></a>
			<a href="receipts.php"><span aria-hidden="true">✓</span><strong><?php etr('Receipts') ?></strong><small><?php etr('Payments and receipts') ?></small></a>
			<a href="../erp/products.php"><span aria-hidden="true">□</span><strong><?php etr('Inventory') ?></strong><small><?php echo (int)$lowStockCount ?> <?php etr('low-stock items') ?></small></a>
		</nav>
		<form method="post" action="posclient.php<?php if ($orderid) echo '?orderid=' . urlencode($orderid); ?>" class="erp-pos-shell" id="pos-form">
			<section class="erp-pos-catalog">
				<header class="erp-pos-top">
					<div>
						<h1><?php etr('Point of sale') ?></h1>
						<p><?php echo date(DATE_PATTERN, $orderdate) ?> · <?php etr('Select a product to add it to the sale') ?></p>
					</div><label class="erp-pos-search"><span>⌕</span><input id="product-search" placeholder="<?php etr('Search products or scan barcode') ?>" autocomplete="off"></label>
				</header>
				<div class="erp-product-grid" id="product-grid"><?php $productCount = 0;
																while ($product = fetch($products)) {
																	$productCount++; ?><button class="erp-product" type="submit" name="productid" value="<?php echo htmlspecialchars($product->productid) ?>" data-search="<?php echo htmlspecialchars(strtolower($product->productid . ' ' . $product->model . ' ' . $product->barcode . ' ' . $product->description)) ?>" onclick="setAction('add')"><strong><?php echo htmlspecialchars($product->model) ?></strong><small><?php echo htmlspecialchars($product->description) ?></small><em>#<?php echo htmlspecialchars($product->productid) ?></em></button><?php } ?><?php if (!$productCount) { ?><div class="erp-empty"><?php etr('No products found') ?></div><?php } ?></div>
			</section>
			<aside class="erp-cart">
				<header class="erp-cart-head">
					<div>
						<h2><?php etr('Current sale') ?> #<?php echo $orderid ? htmlspecialchars($orderid) : '—' ?></h2><small><?php echo $paid ? tr('Paid') : tr('In progress') ?></small>
					</div><?php comboBox('locationid', $locations, $locationid, false); ?>
				</header>
				<div class="erp-cart-lines"><?php $i = 0;
											if ($items) while ($row = fetch($items)) {
												$amount = $row->quantity * $row->unitprice * (1 + $row->vat / 100); ?><div class="erp-cart-line">
							<div><strong><?php echo htmlspecialchars($row->model) ?></strong><small><?php echo formatMoney($row->unitprice) ?> × <?php echo htmlspecialchars($row->quantity) ?></small></div><input type="number" step="any" min="0" name="quantity_<?php echo $i ?>" value="<?php echo htmlspecialchars($row->quantity) ?>" <?php if (!$editable) echo 'disabled'; ?>><b><?php echo formatMoney($amount) ?></b><?php if ($editable) { echo deleteLink("posclient.php?orderid=" . urlencode($orderid) . "&action=delete&line=" . urlencode($row->no), deleteIconImage()); } ?><input type="hidden" name="no_<?php echo $i ?>" value="<?php echo htmlspecialchars($row->no) ?>"><input type="hidden" name="unitprice_<?php echo $i ?>" value="<?php echo htmlspecialchars($row->unitprice) ?>">
						</div><?php $i++;
											} ?><?php if (!$i) { ?><div class="erp-cart-empty">
							<div><strong><?php etr('Cart is empty') ?></strong><br><small><?php etr('Choose a product to begin') ?></small></div>
						</div><?php } ?></div>
				<footer class="erp-cart-summary">
					<div class="erp-total"><span><?php etr('Total') ?></span><strong><?php echo formatMoney($total) ?></strong></div>
					<div class="erp-pos-actions"><a class="erp-new" href="posclient.php?action=new"><?php etr('New sale') ?></a><button class="erp-secondary" type="submit" onclick="setAction('save')" <?php if (!$orderid || !$editable) echo 'disabled'; ?>><?php etr('Save') ?></button><?php if ($paid) { ?><a class="erp-pay" href="invoice_pdf.php?orderid=<?php echo urlencode($orderid) ?>&type=receipt"><?php etr('Print receipt') ?></a><?php } else { ?><button class="erp-pay" type="button" onclick="openPayment()" <?php if (!$orderid || !$total) echo 'disabled'; ?>><?php etr('Pay now') ?> · <?php echo formatMoney($total) ?></button><?php } ?></div>
				</footer>
			</aside>
			<input type="hidden" name="action" id="pos-action"><input type="hidden" name="quantity_new" value="1"><input type="hidden" name="received" id="pos-received"><input type="hidden" name="payment_method" id="pos-payment-method" value="cash"><input type="hidden" name="count" value="<?php echo $i ?>">
		</form>
	</main>
	<dialog id="payment-dialog" class="erp-pay-dialog">
		<form method="dialog">
			<h2><?php etr('Take payment') ?></h2>
			<p><?php etr('Enter the amount received from the customer.') ?></p>
			<div class="due"><span><?php etr('Amount due') ?></span><strong><?php echo formatMoney($total) ?></strong></div>
			<label><?php etr('Payment method') ?><select id="payment-method"><option value="cash"><?php etr('Cash') ?></option><option value="card"><?php etr('Card') ?></option><option value="bank"><?php etr('Bank transfer') ?></option><option value="gift"><?php etr('Gift card') ?></option><option value="store_credit"><?php etr('Store credit') ?></option></select></label>
			<label><?php etr('Amount received') ?><input id="received" type="number" min="<?php echo htmlspecialchars($total) ?>" step="any" value="<?php echo htmlspecialchars($total) ?>"></label>
			<div class="erp-dialog-actions"><button value="cancel"><?php etr('Cancel') ?></button><button type="button" class="confirm" onclick="completePayment()"><?php etr('Complete sale') ?></button></div>
		</form>
	</dialog>
	<script>
		function setAction(action) {
			document.getElementById('pos-action').value = action
		}

		function openPayment() {
			document.getElementById('payment-dialog').showModal();
			setTimeout(function() {
				document.getElementById('received').select()
			}, 0)
		}

		function completePayment() {
			document.getElementById('pos-received').value = document.getElementById('received').value;
			document.getElementById('pos-payment-method').value = document.getElementById('payment-method').value;
			setAction('pay');
			document.getElementById('pos-form').submit()
		}
		document.getElementById('product-search').addEventListener('input', function() {
			var term = this.value.toLowerCase();
			document.querySelectorAll('.erp-product').forEach(function(product) {
				product.hidden = product.dataset.search.indexOf(term) === -1
			})
		});
	</script>
	<?php bottom() ?>
</body>
