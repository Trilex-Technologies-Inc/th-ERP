<?php
include('include.php');
include('pos_shift.inc.php');

checkPermission(PERMISSIONID_SELL);

if (!hasPermission(PERMISSIONID_POS_REFUND_SALE)) {
	header('Location: posclient.php');
	die;
}

$message = null;
$error = null;
$orderid = (int)getParam('orderid', 0);
$openShift = pos_get_open_shift();

if (!pos_return_schema_ready())
	$error = tr('The POS returns database upgrade has not been installed.');

function pos_return_order($orderid)
{
	return find("select so.orderid, so.locationid, so.invoice_transid, so.cancelled, so.orderdate
		from salesorder so where so.orderid=$orderid and so.customerid=" . CUSTOMERID_CASH);
}

function pos_return_lines($orderid)
{
	return query("select si.no, si.productid, p.model, p.description, si.quantity sold_quantity,
		coalesce(sum(pri.quantity), 0) returned_quantity, si.unitprice, si.vat,
		c.revenue_accountid, p.purchase_price, c.stock
		from salesorder_item si
		join product p on p.productid=si.productid
		join category c on c.categoryid=p.categoryid
		left join pos_return_item pri on pri.order_line=si.no
			and pri.returnid in (select returnid from pos_return where orderid=$orderid)
		where si.orderid=$orderid and si.productid != " . sql_string(PRODUCTID_ROUNDING) . "
		group by si.no, si.productid, p.model, p.description, si.quantity, si.unitprice, si.vat,
		c.revenue_accountid, p.purchase_price, c.stock order by si.no");
}

if (!$error && getParam('action') == 'refund') {
	if ($openShift == null) {
		$error = tr('Open a POS shift before processing a refund.');
	} else {
		$order = pos_return_order($orderid);
		$reason = trim(getParam('reason'));
		$refundMethod = getParam('refund_method', 'cash');
		$validMethods = array('cash', 'card', 'bank', 'gift', 'store_credit');
		if ($order == null || isEmpty($order->invoice_transid) || $order->cancelled) {
			$error = tr('Select a completed cash sale to return.');
		} elseif ($reason === '') {
			$error = tr('Enter a reason for the return.');
		} elseif (!in_array($refundMethod, $validMethods)) {
			$error = tr('Select a valid refund method.');
		} else {
			$selectedLines = array();
			$lines = pos_return_lines($orderid);
			while ($line = fetch($lines)) {
				$quantity = (float)getParam('return_quantity_' . $line->no, 0);
				$available = (float)$line->sold_quantity - (float)$line->returned_quantity;
				if ($quantity < 0 || $quantity > $available + 0.00001) {
					$error = tr('A return quantity is greater than the quantity sold.');
					break;
				}
				if ($quantity > 0) {
					$line->return_quantity = $quantity;
					$line->gross_amount = round($quantity * $line->unitprice * (1 + $line->vat / 100), 2);
					$selectedLines[] = $line;
				}
			}
			if (!$error && !count($selectedLines))
				$error = tr('Select at least one item to return.');
			if (!$error) {
				$total = 0;
				foreach ($selectedLines as $line) $total += $line->gross_amount;
				$total = round($total, 2);
				$reasonSql = addslashes($reason);
				$userSql = addslashes(getUser());
				$locationid = (int)$order->locationid;
				begin();
				sql("insert into pos_return (orderid, reason, refund_method, total, createdby)
					values ($orderid, '$reasonSql', '$refundMethod', $total, '$userSql')");
				$returnid = insert_id();
				sql("insert into transaction (transtime, narrative, createdby, createdtime)
					values (now(), 'POS return #$returnid for order #$orderid', '$userSql', now())");
				$transid = insert_id();
				$accountMap = array();
				$vatTotal = 0;
				$standardCost = 0;
				foreach ($selectedLines as $line) {
					$quantity = $line->return_quantity;
					$amount = $quantity * $line->unitprice;
					$vatTotal += $amount * $line->vat / 100;
					if (!array_key_exists($line->revenue_accountid, $accountMap)) $accountMap[$line->revenue_accountid] = 0;
					$accountMap[$line->revenue_accountid] += $amount;
					sql("insert into pos_return_item (returnid, order_line, productid, quantity, amount)
						values ($returnid, " . (int)$line->no . ", " . sql_string($line->productid) . ", $quantity, " . $line->gross_amount . ")");
					sql("update product set quantity=quantity+$quantity where productid=" . sql_string($line->productid));
					if ($line->stock) {
						$standardCost += $quantity * $line->purchase_price;
						sql("insert into stockmove (productid, diff, narrative, transactionid, salesorderid, no, createdby, locationid)
							values (" . sql_string($line->productid) . ", $quantity, 'POS return #$returnid', $transid, $orderid, " . (int)$line->no . ", '$userSql', $locationid)");
					}
				}
				if ($standardCost != 0) {
					$finishedGoods = findValue('select finished_goods from accountconf');
					$costOfSales = findValue('select cost_of_sales from accountconf');
					sql("insert into transaction_part (transactionid, accountid, amount) values ($transid, $finishedGoods, $standardCost)");
					sql("insert into transaction_part (transactionid, accountid, amount) values ($transid, $costOfSales, " . (-1 * $standardCost) . ")");
				}
				foreach ($accountMap as $accountid => $amount)
					sql("insert into transaction_part (transactionid, accountid, amount) values ($transid, $accountid, $amount)");
				$vatPayable = findValue('select vat_payable from accountconf');
				sql("insert into transaction_part (transactionid, accountid, amount) values ($transid, $vatPayable, $vatTotal)");
				$cashAccount = findValue('select default_cash from accountconf');
				sql("insert into transaction_part (transactionid, accountid, amount) values ($transid, $cashAccount, " . (-1 * $total) . ")");
				$shiftid = (int)$openShift->shiftid;
				sql("insert into pos_payment (orderid, shiftid, methodid, amount, reference, createdby)
					values ($orderid, $shiftid, '$refundMethod', " . (-1 * $total) . ", 'Return #$returnid', '$userSql')");
				commit();
				header('Location: pos_return_receipt.php?returnid=' . urlencode($returnid));
				die;
			}
		}
	}
}

$order = $orderid ? pos_return_order($orderid) : null;
if (!$error && $orderid && ($order == null || isEmpty($order->invoice_transid) || $order->cancelled))
	$error = tr('Select a completed cash sale to return.');
$lines = (!$error && $order) ? pos_return_lines($orderid) : null;
?>
<head><title>thERP - <?php etr('POS returns') ?></title><?php styleSheet(); include_common(); ?></head>
<body>
<?php menubar('pos_returns.php'); title(tr('POS returns')); ?>
<main class="container-fluid px-0">
	<?php if ($error) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error) ?></div><?php } ?>
	<section class="card border-0 shadow-sm mb-4"><div class="card-body p-4"><h2 class="h5 fw-bold"><?php etr('Find completed sale') ?></h2><form method="get" class="row g-3 align-items-end"><div class="col-md-5"><label class="form-label"><?php etr('Sale number') ?></label><input class="form-control" type="number" min="1" name="orderid" value="<?php echo $orderid ?: '' ?>" required></div><div class="col-md-3"><button class="btn btn-primary w-100" type="submit"><?php etr('Find sale') ?></button></div></form></div></section>
	<?php if ($lines) { ?><section class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="h5 fw-bold"><?php etr('Return items from sale') ?> #<?php echo $orderid ?></h2><p class="text-secondary"><?php etr('Manager approval is required. Select the returned quantities and refund method.') ?></p><form method="post"><input type="hidden" name="action" value="refund"><input type="hidden" name="orderid" value="<?php echo $orderid ?>"><div class="table-responsive"><table class="table align-middle"><thead><tr><th><?php etr('Product') ?></th><th class="text-end"><?php etr('Sold') ?></th><th class="text-end"><?php etr('Previously returned') ?></th><th class="text-end"><?php etr('Return quantity') ?></th><th class="text-end"><?php etr('Refund') ?></th></tr></thead><tbody><?php while ($line = fetch($lines)) { $available = (float)$line->sold_quantity - (float)$line->returned_quantity; ?><tr><td><strong><?php echo htmlspecialchars($line->model) ?></strong><br><small class="text-secondary"><?php echo htmlspecialchars($line->description) ?></small></td><td class="text-end"><?php echo htmlspecialchars($line->sold_quantity) ?></td><td class="text-end"><?php echo htmlspecialchars($line->returned_quantity) ?></td><td class="text-end"><input class="form-control text-end ms-auto" style="max-width:120px" type="number" min="0" max="<?php echo htmlspecialchars($available) ?>" step="any" name="return_quantity_<?php echo (int)$line->no ?>" value="0" data-price="<?php echo htmlspecialchars($line->unitprice * (1 + $line->vat / 100)) ?>"></td><td class="text-end" data-refund-line><?php echo formatMoney(0) ?></td></tr><?php } ?></tbody></table></div><div class="row g-3 align-items-end"><div class="col-md-6"><label class="form-label"><?php etr('Reason for return') ?></label><input class="form-control" name="reason" maxlength="255" required></div><div class="col-md-3"><label class="form-label"><?php etr('Refund method') ?></label><select class="form-select" name="refund_method"><option value="cash"><?php etr('Cash') ?></option><option value="card"><?php etr('Card') ?></option><option value="bank"><?php etr('Bank transfer') ?></option><option value="gift"><?php etr('Gift card') ?></option><option value="store_credit"><?php etr('Store credit') ?></option></select></div><div class="col-md-3"><button class="btn btn-danger w-100" type="submit" onclick="return confirm('<?php echo htmlspecialchars(tr('Process this refund?'), ENT_QUOTES) ?>')"><?php etr('Complete refund') ?></button></div></div><div class="text-end mt-3"><strong><?php etr('Refund total') ?>: <span id="refund-total"><?php echo formatMoney(0) ?></span></strong></div></form></div></section><?php } ?>
</main>
<script>document.querySelectorAll('[data-price]').forEach(function(input) { input.addEventListener('input', function() { var total = 0; document.querySelectorAll('[data-price]').forEach(function(field) { var amount = Number(field.value) * Number(field.dataset.price); field.closest('tr').querySelector('[data-refund-line]').textContent = amount.toFixed(2); total += amount; }); document.getElementById('refund-total').textContent = total.toFixed(2); }); });</script>
<?php bottom() ?>
</body>
