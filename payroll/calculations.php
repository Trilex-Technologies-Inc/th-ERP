<?php
include('se_tax.inc.php');

function calculateIfNeeded($employeeid, $periodid)
{
	$regtime = findValue("select max(unix_timestamp(regtime)) from payevent where employeeid=$employeeid");
	$calctime = findValue("select unix_timestamp(calctime) from employee where employeeid=$employeeid");
	if ($regtime > $calctime || $calctime == null) {
		calculate($employeeid, $periodid);
		return tr("Recalculation needed");
	}
}

function calculate($employeeid, $periodid)
{
	$policyid = getPolicy($employeeid, $periodid);
	initCalc($employeeid, $periodid);
	createPayeventsFromPolicy($employeeid, $periodid, $policyid);
	calculatePayevents($employeeid, $periodid);
	sql("
	delete from payevent_debit where payeventid in
	(select payeventid from payevent where amount=0 and employeeid=$employeeid and periodid=$periodid)");
	sql("delete from payevent where amount=0 and employeeid=$employeeid and periodid=$periodid");
	sql("update employee set calctime=now() where employeeid=$employeeid");
}

function createPayeventsFromPolicy($employeeid, $periodid, $policyid)
{
	sql("
	delete from payevent_debit where exists 
	(select * from payevent where derived=1 and employeeid=$employeeid and periodid=$periodid)");
	sql("delete from payevent where derived=1 and employeeid=$employeeid and periodid=$periodid");
	$sql = "
	select
		pp.accountid,
		amount,
		description
	from policy_payitem pp
	join payaccount a on a.accountid=pp.accountid
	where policyid=$policyid
	and pp.fromperiodid<=$periodid and (pp.toperiodid>$periodid or pp.toperiodid is null)
	union
	select
		pp.accountid,
		value,
		description
	from emp_payitem pp
	join payaccount a on a.accountid=pp.accountid
	where employeeid=$employeeid
	and pp.fromperiodid<=$periodid and (pp.toperiodid>$periodid or pp.toperiodid is null)
	";
	$rs = query($sql);
	while ($row = fetch($rs)) {
		$value = prepNull($row->amount);
		sql("insert into payevent (employeeid, periodid, accountid, derived, value, regtime, narrative)
		     values ($employeeid, $periodid, $row->accountid, 1, $value, now(), '$row->description')");
	}
}

function calculatePayevents($employeeid, $periodid)
{
	$sql = "
	select
		pe.accountid,
		formula,
		value,
		calcseq,
		inputtype,
		payeventid,
		unix_timestamp(starttime) as starttime,
		unix_timestamp(endtime) as endtime,
		correction,
		narrative
	from payevent pe
	join payaccount a on a.accountid=pe.accountid
	where pe.employeeid=$employeeid and pe.periodid=$periodid
	order by calcseq, starttime
	";
	$rs = query($sql);
	while ($row = fetch($rs)) {
		calculatePayevent($row->payeventid, $row->value, $row->accountid, $row->formula, 
		                  $row->inputtype, $row->correction, $row->starttime, $row->narrative);
	}
}

function calculatePayevent($payeventid, $value = null, $accountid = null,
                           $formula = null, $inputtype = null, $correction = 0, 
						   $starttime = null, $narrative = null)
{
	if ($accountid == null) {
		$row = find("select value, a.accountid, formula, inputtype, 
		               unix_timestamp(starttime) as starttime,
		               narrative
					 from payevent e
					 join payaccount a on a.accountid=e.accountid
					 where payeventid=$payeventid");
		$value = $row->value;
		$accountid = $row->accountid;
		$formula = $row->formula;
		$inputtype = $row->inputtype;
		$starttime = $row->starttime;
		$narrative = $row->narrative;
	}
	if (isEmpty($starttime))
		$starttime = $_REQUEST['period_starttime'];
	$_REQUEST['starttime'] = $starttime;
	$amount = $value;
	$unit_price = null;
	$quantity = null;
	if ($inputtype == INPUT_TYPE_AMOUNT) {
		if ($value != null)
			$amount = $value;
		else {
			$amount = evalFormula($formula);
		}
	} else if ($inputtype == INPUT_TYPE_MINUTES) {
		$quantity = $value;
		$hours = minutes2hours($quantity);
		$unit_price = evalFormula($formula, $payeventid);
		$amount = $hours * $unit_price;
	} else if ($inputtype == INPUT_TYPE_DAYS || $inputtype == INPUT_TYPE_UNITS) {
		$quantity = $value;
		$unit_price = evalFormula($formula, $payeventid);
		$amount = $value * $unit_price;
	}
	$unit_price = prepNull($unit_price);
	$quantity = prepNull($quantity);
	if ($correction) {
		$quantity = (-1) * $quantity;
		$amount = (-1) * $amount;
	}
	if (isEmpty($amount))
		$amount = 0;
		
	$narrative = replaceNarrative($narrative, $payeventid);	
	sql("update payevent set amount=$amount,
				unit_price=$unit_price,
				quantity=$quantity,
				narrative='$narrative'
		where payeventid=$payeventid");

	$groupid = GROUPID_GENERAL_LEDGER;
	$count = findValue("
	select count(*)  		
	from payaccount a 
	join payaccount_group ag on ag.accountid=a.accountid and ag.groupid=$groupid 
	where a.accountid=$accountid", 0);
	if ($count > 0) {
		$glaccountid = findValue("select glaccountid from payaccount where accountid=$accountid");
		if ($glaccountid == null) {
			$glaccountid = findValue("
			select glaccountid
			from policy p
			join employee e on e.policyid=p.policyid
			join payevent pe on pe.employeeid=e.employeeid");
		}
		if ($glaccountid != null) {
			sql("delete from payevent_debit where payeventid=$payeventid");
			sql("
			insert into payevent_debit (payeventid, dimid, glaccountid, share, transactionid)
			values ($payeventid, 1, $glaccountid, 1, null)");
		}
	}
	
}

function replaceNarrative($description, $payeventid)
{
	ereg("\\{.*\\}", $description, $regs);
	foreach ($regs as $reg) {
		$expression = str_replace('{', '', $reg);
		$expression = str_replace('}', '', $expression);
		$result = evalFormula($expression, $payeventid);
		echo "$reg=$result<br>";
		$description = str_replace($reg, $result, $description);
	}
	return $description;
}

function evalFormula($formula, $payeventid = null)
{
	if (isEmpty($formula))
		return 0;
	if ($payeventid != null) {
		$_REQUEST['payeventid'] = $payeventid;
	}	
	$code = '$ret = ' . $formula . ';';
	//echo $code;
	eval($code);
	return $ret;
}

function initCalc($employeeid, $periodid)
{
	$_REQUEST['employeeid']= $employeeid;
	$_REQUEST['periodid']= $periodid;
	$period_starttime = findValue("select unix_timestamp(starttime)
	                               from payperiod where periodid=$periodid");
	$_REQUEST['period_starttime'] = $period_starttime;
	$rs = query("select groupid, name from payaccountgroup");
	while ($row = fetch($rs)) {
		if (!defined($row->name))
			define($row->name, $row->groupid);
	}
	$rs = query("select attributeid, name from attribute");
	while ($row = fetch($rs)) {
		if (!defined($row->name))
			define($row->name, $row->attributeid);
	}
	$rs = query("select apid, name from advanced_percent");
	while ($row = fetch($rs)) {
		if (!defined($row->name))
			define($row->name, $row->apid);
	}
}

function periodSum($groupid)
{
	$periodid = $_REQUEST['periodid'];
	$employeeid = $_REQUEST['employeeid'];
	return findValue("select sum(amount)
	                  from payevent
					  where employeeid=$employeeid
					  and periodid=$periodid
					  and accountid in (select accountid from payaccount_group where groupid=$groupid)", 0);
}

function attribute($attributeid)
{
	$periodid = $_REQUEST['periodid'];
	$employeeid = $_REQUEST['employeeid'];
	$starttime = $_REQUEST['starttime'];
	$sql = "
	select
	   value
	from emp_attribute ea
	join attribute a on a.attributeid=ea.attributeid
	where employeeid=$employeeid
	and a.attributeid=$attributeid
	and regtime = (select max(regtime) from emp_attribute ea2
   			        where ea2.employeeid=ea.employeeid
			        and ea2.attributeid=ea.attributeid
			        and ea2.fromtime<=from_unixtime($starttime))
	";
	$value = findValue($sql);
	if (isEmpty($value)) {
		$policyid = getPolicy($employeeid, $periodid);
		$value = findValue("select value
		                    from policy_attribute_value pa
							where policyid=$policyid and attributeid=$attributeid
							and regtime=(select max(regtime) from policy_attribute_value pa2
							                      where pa2.policyid=pa.policyid 
							                      and pa2.attributeid=pa.attributeid
							                      and pa2.fromtime<=from_unixtime($starttime))
							");
	}
	if (isEmpty($value)) {
		$value = findValue("select value
		                    from attribute_value av
							where attributeid=$attributeid
							and regtime = (select max(regtime) from attribute_value av2 
							where av2.attributeid=av.attributeid
							and av2.fromtime<=from_unixtime($starttime)) 
							");
	}
	if (isEmpty($value))
		return 0;
	return $value;
}

function advanced_percent($apid, $amount)
{
	$rs = query("select ceiling, percent from ap_bracket where apid=$apid order by ceiling");
	$floor = 0;
	$x = 0;
	while ($row = fetch($rs)) {
		if ($amount > $row->ceiling) {
			$a = $row->ceiling - $floor;
		} else {
			$a= $amount - $floor;
		}
		$x += $a * $row->percent/100;
		$floor = $row->ceiling;
		if ($floor > $amount)
			break;
	}
	if ($amount == 0)
		return 0;
	$percent = $x / $amount;
	return $percent;
}

function yearAccountQuantitySum($accountid)
{
	$periodid = $_REQUEST['periodid'];
	$curryear = findValue("select year(starttime) from payperiod where periodid=$periodid");
	$employeeid = $_REQUEST['employeeid'];
	$count = findValue("select sum(quantity)
	                    from payevent
						where employeeid=$employeeid
						and year(starttime)=$curryear
						and accountid=$accountid
						", 0);
	return $count;
}

function createPayevent($accountid, $starttime, $endtime, $value, $parentid)
{
	$periodid = $_REQUEST['periodid'];
	$employeeid = $_REQUEST['employeeid'];
	$payeventid = $_REQUEST['payeventid'];
	sql("insert into payevent (employeeid, periodid, accountid, starttime, endtime, value, derived, parentid, regtime)
		 values ($employeeid, $periodid, $accountid, from_unixtime($starttime), from_unixtime($endtime), $value, 1, $parentid, now())");
	$payeventid2 = insert_id();
	calculatePayevent($payeventid2);
	return $payeventid2;
}

function sick_leave($paid_accountid, $unpaid_accountid)
{
	$periodid = $_REQUEST['periodid'];
	$employeeid = $_REQUEST['employeeid'];
	$payeventid = $_REQUEST['payeventid'];
	$count = yearAccountQuantitySum($paid_accountid);
	$limit = attribute(constant('sickdays_per_year'));
	$event = find("select unix_timestamp(starttime) as starttime,
	                      unix_timestamp(endtime) as endtime
	               from payevent
	               where payeventid=$payeventid");
	$days = dayDiff($event->endtime, $event->starttime);
	$paidDays = min($limit - $count, $days);
	$paidDays = max($paidDays, 0);
	$unpaidDays = $days - $paidDays;
	$starttime = $event->starttime;
	if ($paidDays > 0) {
		$endtime = addDay($starttime, $paidDays);
		createPayevent($paid_accountid, $starttime, $endtime, $paidDays, $payeventid);
		$starttime = $endtime;
	}
	if ($unpaidDays > 0) {
		$endtime = $event->endtime;
		createPayevent($unpaid_accountid, $starttime, $endtime, $unpaidDays, $payeventid);
	}
	return 0;
}

function getBirthDate()
{
	$employeeid = $_REQUEST['employeeid'];
	$birthDate = findValue("
	select birthdate
	from employee where employeeid=$employeeid");
	return $birthDate;
}
?>
