<?php
include('include.php');
include('policy.inc');

$policyid = getParam('policyid');
$periodid = getCurrentPeriod();

$del_attributeid = getParam('del_attributeid');
if (!isEmpty($del_attributeid)) {
	sql("delete from policy_attribute where attributeid=$del_attributeid");
}

if (isSave()) {
	$periodstart = findValue("
	select unix_timestamp(starttime) 
	from payperiod where periodid=$periodid");
	$i = 0;
	$count = getParam("count");
	while ($i < $count) {
		$value = prepNull(getParam("value_$i"));
		$old_value = prepNull(getParam("old_value_$i"));
		$attributeid = getParam("attributeid_$i");
		if ($value != $old_value) {
			sql("insert into policy_attribute_value (policyid, attributeid, fromtime, regtime, value)
				 values ($policyid, $attributeid, from_unixtime($periodstart), now(), $value)");
		}
		$tabid = prepNull(getParam("tabid_$i"));
		$row = prepNull(getParam("row_$i"));
		$col = prepNull(getParam("col_$i"));
		$sql = "
		update policy_attribute
		set tabid=$tabid, row=$row, col=$col
		where policyid=$policyid and attributeid=$attributeid";
		sql($sql);
		$i++;
	}
	$attributeid_new = getParam('attributeid_new');
	if (!isEmpty($attributeid_new)) {
		$value_new = prepNull(getParam('value_new'));
		$count = findValue("
		select count(*) from policy_attribute 
		where policyid=$policyid and attributeid=$attributeid_new");
		if ($count == 0) { 
			$sql = "
			insert into policy_attribute (policyid, attributeid)
			values ($policyid, $attributeid_new)";
			sql($sql);
		}
		$sql = "
		insert into policy_attribute_value (policyid, attributeid, value, fromtime, regtime)
		values ($policyid, $attributeid_new, $value_new,from_unixtime($periodstart), now())";
		sql($sql);
	}
}

$sql = "
select
  a.attributeid,
  description,
  value,
  tabid,
  row,
  col
from policy_attribute_value pa
join attribute a on a.attributeid=pa.attributeid
join policy_attribute p on p.policyid=pa.policyid and p.attributeid=pa.attributeid
where pa.policyid=$policyid
and regtime = (select max(regtime) from policy_attribute_value pa2
                        where pa2.policyid=pa.policyid and pa2.attributeid=pa.attributeid
                        and pa2.fromtime<=now()
                        )
";

$rs = query($sql);

$attrs = rs2array(query("select a.attributeid, description
                         from attribute a"));
$tabs = rs2array(query("select tabid, name from emp_tab"));                         

$description = findValue("select description from policy_description where policyid=$policyid and language='" . getLanguage() . "'");
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
title("<a href='policies.php'>" . tr("Policies") . "</a> > " . htmlspecialchars($description))
?>

	<div id="header">
	<?php buildTabs($policyid, 'attributes') ?>
	</div>
	<div id="main">
		<div id="contents">
			<form action="policy_attributes.php" method="POST">
				<input type="hidden" name="policyid" value="<?php echo htmlspecialchars($policyid) ?>"/>
				<div class="card border-0 shadow-sm overflow-hidden">
					<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
						<div>
							<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($description) ?></h2>
							<p class="text-secondary small mb-0"><?php etr("Attributes") ?></p>
						</div>
						<span class="badge text-bg-light border">#<?php echo htmlspecialchars($policyid) ?></span>
					</div>
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead>
								<tr>
									<th class="text-center" style="width: 90px;"><?php echo tr("Delete") ?></th>
									<th><?php echo tr("Attribute") ?></th>
									<th style="width: 180px;"><?php echo tr("Default value") ?></th>
									<th style="width: 220px;"><?php echo tr("Tab") ?></th>
									<th style="width: 120px;"><?php echo tr("Row") ?></th>
									<th style="width: 120px;"><?php echo tr("Col") ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
							$i = 0;
							while ($row = fetch($rs)) {
								echo "<tr>";
								echo "<td class='text-center'>";
								deleteIcon("policy_attributes.php?del_attributeid=" . htmlspecialchars($row->attributeid) . "&policyid=" . htmlspecialchars($policyid));
								echo "</td>";
								echo "<td>";
								echo "<input type='hidden' name='attributeid_$i' value='" . htmlspecialchars($row->attributeid) . "'/>";
								echo htmlspecialchars(formatCase($row->description));
								echo "</td>";
								echo "<td>";
								numberBox("value_$i", $row->value);
								hidden("old_value_$i", $row->value);
								echo "</td>";
								echo "<td>";
								combobox("tabid_$i", $tabs, $row->tabid, true);
								echo "</td>";
								echo "<td>";
								numberbox("row_$i", $row->row);
								echo "</td>";
								echo "<td>";
								numberbox("col_$i", $row->col);
								echo "</td>";
								echo "</tr>";
								$i++;
							}
							hidden('count', $i);
							?>
								<tr class="table-light">
									<td class="text-center text-secondary fw-semibold">+</td>
									<td><?php comboBox('attributeid_new', $attrs, null, true) ?></td>
									<td><?php numberBox('value_new', '') ?></td>
									<td colspan="3" class="text-secondary small"><?php etr("New attributes use default placement until saved.") ?></td>
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
