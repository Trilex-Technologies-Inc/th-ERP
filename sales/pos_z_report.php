<?php
include('include.php');
include('pos_shift.inc.php');

checkPermission(PERMISSIONID_SELL);

$shiftid = (int)getParam('shiftid', 0);
$shift = $shiftid ? pos_get_shift($shiftid) : null;
$username = getUser();
if ($shift == null || $shift->closed_at == null || ($shift->username != $username && !hasPermission(PERMISSIONID_POS_CLOSE_SHIFT))) {
	header('Location: pos_shift.php');
	die;
}

$totals = pos_shift_totals($shiftid);
$expectedCash = (float)$shift->opening_cash + (float)$totals->cash_sales;
$cashDifference = (float)$shift->closing_cash - $expectedCash;
?>
<!doctype html>
<html>
<head>
	<title>thERP - <?php etr('Z report') ?></title>
	<?php styleSheet(); ?>
	<style>
		.z-report { max-width: 640px; margin: 24px auto; color: #1f2937 }
		.z-report-header { display: flex; justify-content: space-between; gap: 24px; padding-bottom: 18px; border-bottom: 2px solid #1f2937 }
		.z-report h1 { margin: 0; font-size: 1.45rem }.z-report p { margin: 4px 0; color: #566070 }
		.z-report table { width: 100%; margin: 24px 0; border-collapse: collapse }.z-report th, .z-report td { padding: 10px 0; border-bottom: 1px solid #d8dde5 }.z-report th { color: #566070; font-weight: 600; text-align: left }.z-report td { text-align: right; font-weight: 600 }
		.z-report .total th, .z-report .total td { padding-top: 16px; color: #111827; border-bottom: 2px solid #1f2937; font-size: 1.1rem }.z-report-footer { color: #566070; font-size: .85rem }.z-report-actions { margin-top: 24px }
		@media print { .z-report { margin: 0; max-width: none }.z-report-actions { display: none } }
	</style>
</head>
<body>
	<main class="z-report">
		<header class="z-report-header"><div><h1><?php etr('Z report') ?></h1><p><?php etr('Final POS shift closeout') ?></p></div><div class="text-end"><strong><?php echo htmlspecialchars($shift->location_name) ?></strong><p><?php echo htmlspecialchars($shift->closed_at) ?></p></div></header>
		<p class="mt-3"><strong><?php etr('Shift') ?> #<?php echo (int)$shift->shiftid ?></strong> · <?php etr('Cashier') ?>: <?php echo htmlspecialchars($shift->username) ?><br><?php etr('Opened') ?>: <?php echo htmlspecialchars($shift->opened_at) ?><br><?php etr('Closed') ?>: <?php echo htmlspecialchars($shift->closed_at) ?></p>
		<table>
			<tbody>
				<tr><th><?php etr('Transactions') ?></th><td><?php echo (int)$totals->sale_count ?></td></tr>
				<tr><th><?php etr('Cash sales') ?></th><td><?php echo formatMoney($totals->cash_sales) ?></td></tr>
				<tr><th><?php etr('Card sales') ?></th><td><?php echo formatMoney($totals->card_sales) ?></td></tr>
				<tr><th><?php etr('Bank transfer') ?></th><td><?php echo formatMoney($totals->bank_sales) ?></td></tr>
				<tr><th><?php etr('Other payments') ?></th><td><?php echo formatMoney($totals->other_sales) ?></td></tr>
				<tr class="total"><th><?php etr('Total sales') ?></th><td><?php echo formatMoney($totals->total_sales) ?></td></tr>
				<tr><th><?php etr('Opening cash') ?></th><td><?php echo formatMoney($shift->opening_cash) ?></td></tr>
				<tr><th><?php etr('Expected cash in drawer') ?></th><td><?php echo formatMoney($expectedCash) ?></td></tr>
				<tr><th><?php etr('Counted cash in drawer') ?></th><td><?php echo formatMoney($shift->closing_cash) ?></td></tr>
				<tr class="total"><th><?php etr('Cash difference') ?></th><td><?php echo formatMoney($cashDifference) ?></td></tr>
			</tbody>
		</table>
		<footer class="z-report-footer"><?php etr('This is the final report for a closed POS shift.') ?></footer>
		<div class="z-report-actions"><button type="button" class="btn btn-primary" onclick="window.print()"><?php etr('Print') ?></button><a class="btn btn-outline-secondary ms-2" href="pos_shift.php?shiftid=<?php echo (int)$shift->shiftid ?>"><?php etr('Back to shift') ?></a></div>
	</main>
</body>
</html>
