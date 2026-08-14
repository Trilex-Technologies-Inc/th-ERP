<?php
include('include.php');
include('policy.inc');

$policyid = getParam('policyid');
$periodid = getCurrentPeriod();

$del_no = getParam('del_no');
if (!isEmpty($del_no)) {
	$fromperiodid = getParam('fromperiodid');
	$sql = "
	update policy_payitem
	set toperiodid=$periodid
	where policyid=$policyid and no=$del_no";
	sql($sql);
	sql("delete from policy_payitem where fromperiodid=toperiodid");
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$no = getParam("no_$i");
		$accountid = getParam("accountid_$i");
		$amount = prepNull(getParam("amount_$i"));
		sql("update policy_payitem set accountid=$accountid, amount=$amount where policyid=$policyid and no=$no");
		$i++;
	}
	$accountid_new = getParam('accountid_new');
	if (!isEmpty($accountid_new)) {
		$amount_new = prepNull(getParam('amount_new'));
		$no = findValue("select max(no) from policy_payitem where policyid=$policyid", 0) + 1;
		$sql = "
		insert into policy_payitem (policyid, no, fromperiodid, toperiodid, accountid, amount)
		values ($policyid, $no, $periodid, null, $accountid_new, $amount_new)";
		sql($sql);
	}
}

$sql = "
select
  pp.no,
  amount,
  a.accountid
from policy_payitem pp
join payaccount a on a.accountid=pp.accountid
where policyid=$policyid and pp.fromperiodid<=$periodid and (pp.toperiodid>$periodid or pp.toperiodid is null)
order by calcseq
";

$rs = query($sql);

$accounts = rs2array(query("select a.accountid, description 
							from payaccount a"));
							
$policyname = findValue("
select description 
from policy_description
where policyid=$policyid and language='" . getLanguage() . "'");
                       
?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Policy") ?></title>
<?php
styleSheet();
include_common();
?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
</head>
<body>

<?php
menubar("configuration.php", "policy");
title("<a href='policies.php'>" . tr("Policies") . "</a> > " . htmlspecialchars($policyname))
?>

	<div id="header">
	<?php buildTabs($policyid, 'payitems') ?>
	</div>
	<div id="main">
		<div id="contents">
			<form action="policy_payitems.php" method="POST">
				<input type="hidden" name="policyid" value="<?php echo htmlspecialchars($policyid) ?>"/>
				<div class="card border-0 shadow-sm overflow-hidden">
					<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
						<div>
							<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($policyname) ?></h2>
							<p class="text-secondary small mb-0"><?php etr("Pay items") ?></p>
						</div>
						<span class="badge text-bg-light border">#<?php echo htmlspecialchars($policyid) ?></span>
					</div>
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead>
								<tr>
									<th class="text-center" style="width: 90px;"><?php echo tr("Delete") ?></th>
									<th><?php echo tr("Account") ?></th>
									<th style="width: 220px;"><?php echo tr("Amount") ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$i = 0;
							while ($row = fetch($rs)) {
								echo "<tr>";
								echo "<td class='text-center'>";
								deleteIcon("policy_payitems.php?del_no=" . htmlspecialchars($row->no) . "&policyid=" . htmlspecialchars($policyid));
								echo "</td>";
								echo "<td>";
								echo "<input type='hidden' name='no_$i' value='" . htmlspecialchars($row->no) . "'/>";
								comboBox("accountid_$i", $accounts, $row->accountid, false);
								echo "</td>";
								echo "<td>";
								moneybox("amount_$i", $row->amount);
								echo "</td>";
								echo "</tr>";
								$i++;
							}
							hidden('count', $i);
							?>
								<tr class="table-light">
									<td class="text-center text-secondary fw-semibold">+</td>
									<td><?php comboBox('accountid_new', $accounts, null, true) ?></td>
									<td><?php moneybox('amount_new', null) ?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3">
						<?php saveButton() ?>
						<a class="btn btn-outline-secondary" href="policy.php?policyid=<?php echo htmlspecialchars($policyid) ?>"><?php etr("Back") ?></a>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php bottom() ?>
</body>
</html>
