<?php
include('include.php');
include('pos_shift.inc.php');

checkPermission(PERMISSIONID_SELL);

$action = getParam('action');
$shiftid = (int)getParam('shiftid', 0);
$message = null;
$error = null;
$username = getUser();

if (!pos_shift_schema_ready())
	$error = tr('The POS shift database upgrade has not been installed.');

if (!$error && $action == 'open') {
	if (pos_get_open_shift() != null) {
		$error = tr('You already have an open shift.');
	} else {
		$locationid = (int)getParam('locationid', 1);
		$openingCash = (float)getParam('opening_cash', 0);
		if ($openingCash < 0)
			$error = tr('Opening cash cannot be negative.');
		else {
			$userSql = addslashes($username);
			sql("insert into pos_shift (username, locationid, opened_at, opening_cash)
				values ('$userSql', $locationid, now(), $openingCash)");
			sql("update user set locationid=$locationid where username='$userSql'");
			header('Location: posclient.php?shift_opened=1');
			die;
		}
	}
}

if (!$error && $action == 'close') {
	$openShift = pos_get_open_shift();
	if ($openShift == null || (int)$openShift->shiftid !== $shiftid) {
		$error = tr('This shift is not open.');
	} elseif (!hasPermission(PERMISSIONID_POS_CLOSE_SHIFT)) {
		$error = tr('You do not have permission to close a POS shift.');
	} else {
		$closingCash = (float)getParam('closing_cash', -1);
		if ($closingCash < 0)
			$error = tr('Counted cash cannot be negative.');
		else {
			sql("update pos_shift set closing_cash=$closingCash, closed_at=now()
				where shiftid=$shiftid and closed_at is null");
			header('Location: pos_shift.php?shiftid=' . urlencode($shiftid) . '&closed=1');
			die;
		}
	}
}

$openShift = $error ? null : pos_get_open_shift();
if (!$shiftid && $openShift)
	$shiftid = (int)$openShift->shiftid;
$selectedShift = $shiftid ? pos_get_shift($shiftid) : null;
if ($selectedShift && $selectedShift->username != $username && !hasPermission(PERMISSIONID_POS_CLOSE_SHIFT)) {
	$selectedShift = null;
	$error = tr('You do not have permission to view this shift.');
}
$totals = $selectedShift ? pos_shift_totals($shiftid) : null;
$expectedCash = $selectedShift ? (float)$selectedShift->opening_cash + (float)$totals->cash_sales : 0;
$variance = $selectedShift && $selectedShift->closing_cash !== null ? (float)$selectedShift->closing_cash - $expectedCash : null;
$locations = rs2array(query('select locationid, name from location order by name'));
$userSql = addslashes($username);
$shiftRows = $error ? null : query("select s.*, l.name location_name,
	coalesce(sum(p.amount),0) total_sales, count(distinct p.orderid) sale_count
	from pos_shift s join location l on l.locationid=s.locationid
	left join pos_payment p on p.shiftid=s.shiftid
	where s.username='$userSql' group by s.shiftid order by s.shiftid desc limit 30");
?>
<head>
	<title>thERP - <?php etr('POS shifts') ?></title>
	<?php styleSheet(); include_common(); ?>
</head>
<body>
<?php menubar('pos_shift.php'); title(tr('POS shifts')); ?>
<main class="container-fluid px-0">
	<?php if ($error) { ?><div class="alert alert-danger"><?php echo htmlspecialchars($error) ?></div><?php } ?>
	<?php if (getParam('closed')) { ?><div class="alert alert-success"><?php etr('The shift has been closed and counted.') ?></div><?php } ?>

	<?php if (!$error && !$openShift) { ?>
	<section class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
		<h2 class="h5 fw-bold"><?php etr('Open register') ?></h2>
		<p class="text-secondary"><?php etr('Count the cash float in the drawer before taking the first sale.') ?></p>
		<form method="post" class="row g-3 align-items-end">
			<input type="hidden" name="action" value="open">
			<div class="col-md-5"><label class="form-label"><?php etr('Location') ?></label><?php comboBox('locationid', $locations, findValue("select locationid from user where username='$userSql'", 1), false); ?></div>
			<div class="col-md-4"><label class="form-label"><?php etr('Opening cash') ?></label><input class="form-control" name="opening_cash" type="number" min="0" step="0.01" value="0" required autofocus></div>
			<div class="col-md-3"><button class="btn btn-success w-100" type="submit"><?php etr('Open shift') ?></button></div>
		</form>
	</div></section>
	<?php } elseif ($openShift) { ?>
	<div class="alert alert-success d-flex justify-content-between align-items-center"><span><?php etr('Register open') ?> · #<?php echo (int)$openShift->shiftid ?> · <?php echo htmlspecialchars($openShift->location_name) ?> · <?php echo htmlspecialchars($openShift->opened_at) ?></span><a class="btn btn-primary btn-sm" href="posclient.php"><?php etr('Return to POS') ?></a></div>
	<?php } ?>

	<?php if ($selectedShift && $totals) { ?>
	<section class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
		<div class="d-flex justify-content-between"><div><h2 class="h5 fw-bold mb-1"><?php etr('Shift report') ?> #<?php echo (int)$selectedShift->shiftid ?></h2><p class="text-secondary"><?php echo htmlspecialchars($selectedShift->opened_at) ?> — <?php echo $selectedShift->closed_at ? htmlspecialchars($selectedShift->closed_at) : tr('Open') ?></p></div><span class="badge <?php echo $selectedShift->closed_at ? 'text-bg-secondary' : 'text-bg-success' ?> align-self-start"><?php echo $selectedShift->closed_at ? tr('Closed') : tr('Open') ?></span></div>
		<div class="row g-3 mb-4">
			<div class="col-6 col-lg-3"><div class="border rounded p-3"><small class="text-secondary"><?php etr('Sales') ?></small><div class="fs-4 fw-bold"><?php echo formatMoney($totals->total_sales) ?></div><small><?php echo (int)$totals->sale_count ?> <?php etr('transactions') ?></small></div></div>
			<div class="col-6 col-lg-3"><div class="border rounded p-3"><small class="text-secondary"><?php etr('Cash sales') ?></small><div class="fs-4 fw-bold"><?php echo formatMoney($totals->cash_sales) ?></div><small><?php etr('Opening cash') ?>: <?php echo formatMoney($selectedShift->opening_cash) ?></small></div></div>
			<div class="col-6 col-lg-3"><div class="border rounded p-3"><small class="text-secondary"><?php etr('Expected cash') ?></small><div class="fs-4 fw-bold"><?php echo formatMoney($expectedCash) ?></div><small><?php etr('Cash expected in drawer') ?></small></div></div>
			<div class="col-6 col-lg-3"><div class="border rounded p-3"><small class="text-secondary"><?php echo $variance === null ? tr('Card / bank') : tr('Cash difference') ?></small><div class="fs-4 fw-bold"><?php echo formatMoney($variance === null ? (float)$totals->card_sales + (float)$totals->bank_sales : $variance) ?></div><small><?php etr('Other') ?>: <?php echo formatMoney($totals->other_sales) ?></small></div></div>
		</div>
		<?php if (!$selectedShift->closed_at && (int)$selectedShift->shiftid === (int)$openShift->shiftid && hasPermission(PERMISSIONID_POS_CLOSE_SHIFT)) { ?>
		<form method="post" class="row g-3 align-items-end" onsubmit="return confirm('<?php echo htmlspecialchars(tr('Close this shift? Sales will be locked until a new shift is opened.'), ENT_QUOTES) ?>')">
			<input type="hidden" name="action" value="close"><input type="hidden" name="shiftid" value="<?php echo (int)$selectedShift->shiftid ?>">
			<div class="col-md-6"><label class="form-label"><?php etr('Counted cash in drawer') ?></label><input class="form-control" name="closing_cash" type="number" min="0" step="0.01" required autofocus></div>
			<div class="col-md-3"><button class="btn btn-danger w-100" type="submit"><?php etr('Close shift') ?></button></div>
		</form>
		<?php } ?>
	</div></section>
	<?php } ?>

	<?php if ($shiftRows) { ?><section class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="h5 fw-bold"><?php etr('Recent shifts') ?></h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th><?php etr('Opened') ?></th><th><?php etr('Closed') ?></th><th><?php etr('Location') ?></th><th class="text-end"><?php etr('Transactions') ?></th><th class="text-end"><?php etr('Sales') ?></th></tr></thead><tbody><?php while ($row=fetch($shiftRows)) { ?><tr><td><a href="pos_shift.php?shiftid=<?php echo (int)$row->shiftid ?>">#<?php echo (int)$row->shiftid ?></a></td><td><?php echo htmlspecialchars($row->opened_at) ?></td><td><?php echo $row->closed_at ? htmlspecialchars($row->closed_at) : tr('Open') ?></td><td><?php echo htmlspecialchars($row->location_name) ?></td><td class="text-end"><?php echo (int)$row->sale_count ?></td><td class="text-end"><?php echo formatMoney($row->total_sales) ?></td></tr><?php } ?></tbody></table></div></div></section><?php } ?>
</main>
<?php bottom() ?>
</body>
