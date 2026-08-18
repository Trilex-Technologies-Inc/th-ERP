<?php
include('include.php');
include('policy.inc');

$policyid = requirePolicyId(getParam('policyid'));
$periodid = getCurrentPeriod();

$del_groupid = getParam('del_groupid');
if (!isEmpty($del_groupid)) {
	$sql = "
	delete from policy_accountgroup
	where policyid=$policyid and groupid=$del_groupid";
	sql($sql);
}

if (isSave()) {
	$groupid_new = getParam('groupid_new');
	if (!isEmpty($groupid_new)) {
		$sql = "
		insert into policy_accountgroup (policyid, groupid)
		values ($policyid, $groupid_new)";
		sql($sql);
	}
}

$sql = "
select
  pa.groupid,
  description
from policy_accountgroup pa
left outer join payaccountgroup_description d on d.groupid=pa.groupid and language='" . getLanguage() . "'
where policyid=$policyid
";

$rs = query($sql);

$attrs = rs2array(query("select a.groupid, description
                         from payaccountgroup a
                         "));

$description = findValue("select description from policy_description where policyid=$policyid and language='" . getLanguage() . "'");
?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Policy") ?></title>
<?php styleSheet() ?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
<script type="text/javascript" src="common.js"></script>
</head>
<body>

<?php
menubar("configuration.php", "policy");
title("<a href='policies.php'>" . tr("Policies") . "</a> > " . htmlspecialchars($description))
?>

	<nav class="policy-tabs" aria-label="<?php echo tr("Policy") ?>">
	<?php buildTabs($policyid, 'selectors') ?>
	</nav>
	<div id="main">
		<div id="contents">
			<form action="policy_groups.php" method="POST">
				<input type="hidden" name="policyid" value="<?php echo htmlspecialchars($policyid) ?>"/>
				<div class="card border-0 shadow-sm overflow-hidden">
					<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
						<div>
							<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($description) ?></h2>
							<p class="text-secondary small mb-0"><?php etr("Selectors") ?></p>
						</div>
						<span class="badge text-bg-light border">#<?php echo htmlspecialchars($policyid) ?></span>
					</div>
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead>
								<tr>
									<th class="text-center" style="width: 90px;"><?php echo tr("Delete") ?></th>
									<th><?php echo tr("Account group") ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$i = 0;
							while ($row = fetch($rs)) {
								echo "<tr>";
								echo "<td class='text-center'>";
								deleteIcon("policy_groups.php?del_groupid=" . htmlspecialchars($row->groupid) . "&policyid=" . htmlspecialchars($policyid));
								echo "</td>";
								echo "<td>";
								echo "<input type='hidden' name='groupid_$i' value='" . htmlspecialchars($row->groupid) . "'/>";
								echo htmlspecialchars(formatCase($row->description));
								echo "</td>";
								echo "</tr>";
								$i++;
							}
							hidden('count', $i);
							?>
								<tr class="table-light">
									<td class="text-center text-secondary fw-semibold">+</td>
									<td><?php comboBox('groupid_new', $attrs, null, true) ?></td>
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
