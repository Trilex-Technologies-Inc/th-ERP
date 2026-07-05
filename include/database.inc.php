<?php
function query($sql)
{
	$q = mysql_query($sql);
	$err = mysql_error();
	if (strlen($err) > 0) {
		$mess = "SQL error: " . $err . "\n";
		$mess .= "SQL errno: " . mysql_errno() . "\n";
		$mess .= "<br/>";
		$mess .= "SQL: " . $sql . "\n";
		//echo $mess;
		rollback();
		trigger_error($mess, E_USER_ERROR);
	}
	return $q;
}

function connect($host, $dbuser, $password)
{
	mysql_connect($host, $dbuser, $password);

	// set for utf8
	sql("SET NAMES 'utf8'");
	//sql("SET CHARACTER_SET 'utf8'");
}

function select_db($dbname)
{
	return mysql_select_db($dbname);
}

function fetch_row($query)
{
	return mysql_fetch_row($query);
}

function fetch_assoc($query)
{
	return mysql_fetch_assoc($query);
}

function fetch_array($query)
{
	return mysql_fetch_array($query);
}

function fetch_object($query)
{
	return mysql_fetch_object($query);
}

function num_rows($rs)
{
	return mysql_num_rows($rs);
}

function affected_rows()
{
	return mysql_affected_rows();
}

function find($sql, $dummy = false)
{
	$q = query($sql);
	if (num_rows($q) == 0) {
		if ($dummy)
			return new Dummy();
		else
			return null;
	}
	return fetch_object($q);
}

function select_value($sql)
{
    $q = query($sql);
	if (num_rows($q) == 0)
		return null;
    $row = fetch_array($q);
    return $row[0];
}

function sql($sql)
{
    return query($sql);
}

function fetch($rs)
{
    return fetch_object($rs);
}

function begin()
{
	sql("set autocommit=0");
	sql("begin");
}

function commit()
{
	sql("commit");
	sql("set autocommit=1");
}

function rollback()
{
	sql("rollback");
	sql("set autocommit=1");
}

function insert_id()
{
    return mysql_insert_id();
}

function findValue($sql, $default = null)
{
    $rs = query($sql);
    $row = fetch_array($rs);
    if ($row == null)
    	return $default;
    if ($row[0] == null)
    	return $default;
    return $row[0];
}

function rs2array($rs)
{
    $result = array();
    while ($row = fetch_row($rs)) {
        $result[] = $row;
    }
    return $result;
}

?>