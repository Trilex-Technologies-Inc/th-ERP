<?php

define('PERMISSIONID_POS_CLOSE_SHIFT', 23);
define('PERMISSIONID_POS_REFUND_SALE', 22);

function pos_shift_table_exists()
{
	return findValue("show tables like 'pos_shift'", null) != null;
}

function pos_shift_schema_ready()
{
	return pos_shift_table_exists()
		&& findValue("show tables like 'pos_payment'", null) != null
		&& findValue("show columns from pos_payment like 'shiftid'", null) != null;
}

function pos_return_schema_ready()
{
	return findValue("show tables like 'pos_return'", null) != null
		&& findValue("show tables like 'pos_return_item'", null) != null;
}

function pos_get_open_shift($username = null)
{
	if (!pos_shift_schema_ready())
		return null;
	if ($username == null)
		$username = getUser();
	$username = addslashes($username);
	return find("select s.*, l.name location_name
		from pos_shift s join location l on l.locationid=s.locationid
		where s.username='$username' and s.closed_at is null
		order by s.shiftid desc limit 1");
}

function pos_get_shift($shiftid)
{
	return find("select s.*, l.name location_name
		from pos_shift s join location l on l.locationid=s.locationid
		where s.shiftid=$shiftid");
}

function pos_shift_totals($shiftid)
{
	return find("select
		coalesce(sum(case when p.methodid='cash' then p.amount else 0 end), 0) cash_sales,
		coalesce(sum(case when p.methodid='card' then p.amount else 0 end), 0) card_sales,
		coalesce(sum(case when p.methodid='bank' then p.amount else 0 end), 0) bank_sales,
		coalesce(sum(case when p.methodid not in ('cash','card','bank') then p.amount else 0 end), 0) other_sales,
		coalesce(sum(p.amount), 0) total_sales,
		count(distinct p.orderid) sale_count
		from pos_payment p where p.shiftid=$shiftid");
}

?>
