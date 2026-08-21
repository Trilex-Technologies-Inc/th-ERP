<?php
	include('include.php');

	$errors = array();
	$narrativeParam = getParam('narrative', '');
	$transtimeParam = getParam('transtime', date('Y-m-d'));
	$accountParam = getParam('accountid');
	$amountParam = getParam('amount', '');
	$vatParam = getParam('vat', isSave() ? '' : '0');
	$narrative = is_scalar($narrativeParam) ? trim((string) $narrativeParam) : '';
	$transtimeValue = is_scalar($transtimeParam) ? (string) $transtimeParam : '';
	$accountid = is_scalar($accountParam) ? (string) $accountParam : '';
	$amountValue = is_scalar($amountParam) ? trim((string) $amountParam) : '';
	$vatValue = is_scalar($vatParam) ? trim((string) $vatParam) : '';

	if (isSave()) {
		$amountNormalized = str_replace(',', '.', $amountValue);
		$vatNormalized = str_replace(',', '.', $vatValue);
		$amount = preg_match('/^\d+(?:[.,]\d{1,2})?$/', $amountValue) ? (float) $amountNormalized : null;
		$vat = preg_match('/^\d+(?:[.,]\d{1,2})?$/', $vatValue) ? (float) $vatNormalized : null;
		$transactionDate = DateTime::createFromFormat('!Y-m-d', $transtimeValue);
		$validDate = $transactionDate !== false && $transactionDate->format('Y-m-d') === $transtimeValue;
		$transtime = $validDate ? $transactionDate->getTimestamp() : null;

		if ($narrative === '')
			$errors[] = tr('Narrative is required.');
		else if (strlen($narrative) > 80)
			$errors[] = tr('Narrative cannot exceed 80 characters.');
		if (!$validDate)
			$errors[] = tr('Enter a valid transaction date.');
		if ($amount === null || $amount <= 0)
			$errors[] = tr('Amount must be greater than zero and have at most two decimals.');
		if ($vat === null || $vat < 0)
			$errors[] = tr('VAT must be zero or greater and have at most two decimals.');
		if ($amount !== null && $vat !== null && $vat > $amount)
			$errors[] = tr('VAT cannot be greater than the total amount.');

		$validExpenseAccount = ctype_digit((string) $accountid) && findValue("
			select count(*)
			from account a
			join account_group ag on ag.accountid=a.accountid
			where a.accountid=" . (int) $accountid . "
			and a.dimid=1
			and ag.groupid=" . GROUPID_EXPENSES, 0) > 0;
		if (!$validExpenseAccount)
			$errors[] = tr('Select a valid expense account.');

		$cash_accountid = findValue("select default_cash from accountconf");
		$vat_accountid = findValue("select vat_recoverable from accountconf");
		if (isEmpty($cash_accountid) || isEmpty($vat_accountid))
			$errors[] = tr('Default cash and recoverable VAT accounts must be configured first.');

		if (count($errors) === 0) {
			$userSql = sql_string(getUser());
			$narrativeSql = sql_string($narrative);
			$accountid = (int) $accountid;
			$cash_accountid = (int) $cash_accountid;
			$vat_accountid = (int) $vat_accountid;
			$cash_amount = (-1) * $amount;
			$expense_amount = $amount - $vat;

			begin();
			sql("
			insert into transaction (narrative, transtime, createdby, valid, createdtime)
			values ($narrativeSql, from_unixtime($transtime), $userSql, 1, now())");
			$transid = insert_id();
			sql("insert into transaction_part (transactionid, dimid, accountid, amount)
				 values ($transid, 1, $cash_accountid, $cash_amount)");
			sql("insert into transaction_part (transactionid, dimid, accountid, amount)
				 values ($transid, 1, $vat_accountid, $vat)");
			sql("insert into transaction_part (transactionid, dimid, accountid, amount)
				 values ($transid, 1, $accountid, $expense_amount)");
			commit();
			header("Location: transactions.php");
			exit;
		}
	}

	$accounts = rs2array(query("
	select a.accountid, a.accountid, name
	from account a
	join account_group ag on ag.accountid=a.accountid and groupid=".GROUPID_EXPENSES."
	where a.dimid=1
	"));

?>
<head>
<title>thERP - <?php etr("Register transaction") ?></title>
<?php
styleSheet();
styleSheet('tabs');
?>
</head>

<body>
<?php
menubar("index.php");
$title = tr("Register");
title("<a href='transactions.php'>" . tr("Transactions") . "</a> > $title");
?>

<main class="accounting-transaction-page">
<section class="card border-0 shadow-sm mb-4 overflow-hidden">
<div class="card-body p-4 p-lg-5 d-flex align-items-center gap-3">
<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning-emphasis fs-4" aria-hidden="true">&#8595;</span>
<div><span class="accounting-section-kicker"><?php etr("Quick actions") ?></span><h1 class="h3 fw-bold mt-1 mb-1"><?php etr("Expense transaction") ?></h1><p class="text-secondary mb-0"><?php etr("Record an expense, including its VAT and payment amount.") ?></p></div>
</div>
</section>

<?php if (count($errors) > 0) { ?>
<div class="alert alert-danger" role="alert" aria-live="polite"><strong><?php etr("Please correct the following") ?>:</strong><ul class="mb-0 mt-2"><?php foreach ($errors as $error) { ?><li><?php echo htmlspecialchars($error) ?></li><?php } ?></ul></div>
<?php } ?>

<form action="expense_trans.php" method="POST" class="accounting-transaction-form needs-validation">
<section class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Transaction details") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Expense information") ?></h2></div>
<div class="card-body p-4"><div class="row g-4">
<div class="col-12 col-lg-8"><label class="form-label fw-semibold" for="narrative"><?php etr("Narrative") ?> <span class="text-danger">*</span></label><input class="form-control" id="narrative" name="narrative" type="text" maxlength="80" required value="<?php echo htmlspecialchars($narrative) ?>"><div class="form-text"><?php etr("Briefly describe the purpose of this expense") ?></div></div>
<div class="col-12 col-lg-4"><label class="form-label fw-semibold" for="transtime"><?php etr("Transaction date") ?> <span class="text-danger">*</span></label><input class="form-control" id="transtime" name="transtime" type="date" required value="<?php echo htmlspecialchars($transtimeValue) ?>"></div>
<div class="col-12"><label class="form-label fw-semibold" for="accountid"><?php etr("Expense account") ?> <span class="text-danger">*</span></label><select class="form-select" id="accountid" name="accountid" required><option value=""><?php etr("Select an expense account") ?></option><?php foreach ($accounts as $account) { ?><option value="<?php echo htmlspecialchars($account[0]) ?>" <?php echo (string) $account[0] === (string) $accountid ? 'selected' : '' ?>><?php echo htmlspecialchars($account[1] . ' - ' . $account[2]) ?></option><?php } ?></select></div>
<div class="col-12"><fieldset><legend class="h6 fw-bold mb-3"><?php etr("Amount breakdown") ?></legend><div class="row g-3 align-items-stretch">
<div class="col-12 col-md-4"><div class="h-100 rounded-3 border bg-body-tertiary p-3"><label class="form-label fw-semibold" for="amount"><?php etr("Total paid") ?> <span class="text-danger">*</span></label><input class="form-control form-control-lg text-end fw-semibold" id="amount" name="amount" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00" pattern="[0-9]+([.,][0-9]{1,2})?" required value="<?php echo htmlspecialchars($amountValue) ?>" aria-describedby="amount-help"><div class="form-text" id="amount-help"><?php etr("Amount including VAT") ?></div></div></div>
<div class="col-12 col-md-4"><div class="h-100 rounded-3 border bg-body-tertiary p-3"><label class="form-label fw-semibold" for="vat"><?php etr("VAT included") ?> <span class="text-danger">*</span></label><input class="form-control form-control-lg text-end fw-semibold" id="vat" name="vat" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00" pattern="[0-9]+([.,][0-9]{1,2})?" required value="<?php echo htmlspecialchars($vatValue) ?>" aria-describedby="vat-help"><div class="form-text" id="vat-help"><?php etr("Use 0 when VAT does not apply") ?></div></div></div>
<div class="col-12 col-md-4"><div class="h-100 rounded-3 border border-warning-subtle bg-warning-subtle p-3 d-flex flex-column justify-content-center"><span class="text-secondary small fw-semibold text-uppercase"><?php etr("Expense before VAT") ?></span><strong class="fs-4 mt-1" id="expense-before-vat">0.00</strong><span class="form-text mt-1"><?php etr("Calculated automatically") ?></span></div></div>
</div></fieldset></div>
</div></div>
<div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 px-4 py-3"><span class="text-secondary small"><span class="text-danger">*</span> <?php etr("Required fields") ?></span><div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="index.php"><?php etr("Cancel") ?></a><button class="btn btn-primary" type="submit" name="save" value="1"><?php etr("Save expense") ?></button></div></div>
</section>
</form>
</main>
<script>
(function () {
	var form = document.querySelector('.needs-validation');
	var amount = document.getElementById('amount');
	var vat = document.getElementById('vat');
	var expenseBeforeVat = document.getElementById('expense-before-vat');
	if (!form || !amount || !vat) return;

	function numberValue(input) {
		return Number(input.value.replace(',', '.'));
	}
	function validateAmounts() {
		var total = numberValue(amount);
		var tax = numberValue(vat);
		amount.setCustomValidity(Number.isFinite(total) && total > 0 ? '' : <?php echo json_encode(tr("Amount must be greater than zero.")) ?>);
		vat.setCustomValidity(Number.isFinite(tax) && tax >= 0 && Number.isFinite(total) && tax <= total ? '' : <?php echo json_encode(tr("VAT must be between zero and the total amount.")) ?>);
		expenseBeforeVat.textContent = Number.isFinite(total) && Number.isFinite(tax) && total >= tax ? (total - tax).toFixed(2) : '—';
	}
	amount.addEventListener('input', validateAmounts);
	vat.addEventListener('input', validateAmounts);
	validateAmounts();
	form.addEventListener('submit', function () {
		validateAmounts();
		form.classList.add('was-validated');
	});
}());
</script>
<?php
bottom();
?>
</body>
