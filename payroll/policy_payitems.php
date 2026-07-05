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
title("<a href='policies.php'>Policies</a> > $policyname")
?>

	<div id="header">
	<?php buildTabs($policyid, 'payitems') ?>
	</div>
	<div id="main">
		<div id="contents">
<form action="policy_payitems.php" method="POST">
<input type=hidden name=policyid value='<?php echo $policyid ?>'/>
<input type=hidden name=accounttype value='<?php echo $accounttype ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Account") ?></th>
<th><?php echo tr("Amount") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=no_$i value='$row->no'/>";
    echo "<tr class='$class'>";
	deleteColumn("policy_payitems.php?del_no=$row->no&policyid=$policyid");
	echo "<td>";
    comboBox("accountid_$i", $accounts, $row->accountid, false);
    echo "</td>";
    echo "<td>";
	moneybox("amount_$i", $row->amount);
	echo "</td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
<tr>
<td/>
<td><?php comboBox('accountid_new', $accounts, null, true) ?></td>
<td><?php moneybox('amount_new', null) ?></td>
</tr>
</table>
<br/>
<?php saveButton() ?>
</form>
		</div>
	</div>
</body>
</html>
