<?php

$GLOBALS['therp_db_connection'] = null;

function db_connection()
{
	return $GLOBALS['therp_db_connection'];
}

function query($sql)
{
	$conn = db_connection();
	$q = mysqli_query($conn, $sql);
	$err = mysqli_error($conn);
	if (strlen($err) > 0) {
		$mess = "SQL error: " . $err . "\n";
		$mess .= "SQL errno: " . mysqli_errno($conn) . "\n";
		$mess .= "<br/>";
		$mess .= "SQL: " . $sql . "\n";
		rollback();
		trigger_error($mess, E_USER_ERROR);
	}
	return $q;
}

function connect($host, $dbuser, $password)
{
	$conn = mysqli_connect($host, $dbuser, $password);
	if ($conn === false) {
		trigger_error("Database connection failed: " . mysqli_connect_error(), E_USER_ERROR);
	}
	$GLOBALS['therp_db_connection'] = $conn;

	sql("SET NAMES 'utf8'");
}

function select_db($dbname)
{
	return mysqli_select_db(db_connection(), $dbname);
}

function fetch_row($query)
{
	return mysqli_fetch_row($query);
}

function fetch_assoc($query)
{
	return mysqli_fetch_assoc($query);
}

function fetch_array($query)
{
	return mysqli_fetch_array($query);
}

function fetch_object($query)
{
	return mysqli_fetch_object($query);
}

function num_rows($rs)
{
	return mysqli_num_rows($rs);
}

function affected_rows()
{
	return mysqli_affected_rows(db_connection());
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
	$conn = db_connection();
	if ($conn == null)
		return;
	@mysqli_rollback($conn);
	@mysqli_autocommit($conn, true);
}

function insert_id()
{
    return mysqli_insert_id(db_connection());
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
