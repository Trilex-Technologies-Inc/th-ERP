<?php
include('include.php');
include('pos_shift.inc.php');

checkPermission(PERMISSIONID_SELL);

$shift = pos_get_open_shift();
if ($shift == null) {
	header('Location: pos_shift.php');
	die;
}

$totals = pos_shift_totals((int)$shift->shiftid);
$expectedCash = (float)$shift->opening_cash + (float)$totals->cash_sales;
?>
<!doctype html>
<html>
<head>
	<title>thERP - <?php etr('X report') ?></title>
	<?php styleSheet(); ?>
	<style>
		.x-report { max-width: 640px; margin: 24px auto; color: #1f2937 }
		.x-report-header { display: flex; justify-content: space-between; gap: 24px; padding-bottom: 18px; border-bottom: 2px solid #1f2937 }
		.x-report h1 { margin: 0; font-size: 1.45rem }.x-report p { margin: 4px 0; color: #566070 }
		.x-report table { width: 100%; margin: 24px 0; border-collapse: collapse }.x-report th, .x-report td { padding: 10px 0; border-bottom: 1px solid #d8dde5 }.x-report th { color: #566070; font-weight: 600; text-align: left }.x-report td { text-align: right; font-weight: 600 }
		.x-report .total th, .x-report .total td { padding-top: 16px; color: #111827; border-bottom: 2px solid #1f2937; font-size: 1.1rem }.x-report-footer { color: #566070; font-size: .85rem }.x-report-actions { margin-top: 24px }
		@media print { .x-report { margin: 0; max-width: none }.x-report-actions { display: none } }
	</style>
</head>
<body>
	<main class="x-report">
		<header class="x-report-header"><div><h1><?php etr('X report') ?></h1><p><?php etr('Mid-day POS report — shift remains open') ?></p></div><div class="text-end"><strong><?php echo htmlspecialchars($shift->location_name) ?></strong><p><?php echo htmlspecialchars(date(DATE_PATTERN . ' H:i:s')) ?></p></div></header>
		<p class="mt-3"><strong><?php etr('Shift') ?> #<?php echo (int)$shift->shiftid ?></strong> · <?php etr('Cashier') ?>: <?php echo htmlspecialchars($shift->username) ?><br><?php etr('Opened') ?>: <?php echo htmlspecialchars($shift->opened_at) ?></p>
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
			</tbody>
		</table>
		<footer class="x-report-footer"><?php etr('This is an interim report. It does not close or reset the POS shift.') ?></footer>
		<div class="x-report-actions"><button type="button" class="btn btn-primary" onclick="window.print()"><?php etr('Print') ?></button><a class="btn btn-outline-secondary ms-2" href="pos_shift.php?shiftid=<?php echo (int)$shift->shiftid ?>"><?php etr('Back to shift') ?></a></div>
	</main>
</body>
</html>
