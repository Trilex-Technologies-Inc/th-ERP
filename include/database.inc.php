<?php

/** @var mysqli|null $db_connection */
$db_connection = null;

function db_connection()
{
    global $db_connection;
    if (!($db_connection instanceof mysqli)) {
        throw new RuntimeException('Database connection has not been established.');
    }
    return $db_connection;
}

function query($sql)
{
    $connection = db_connection();
    $q = mysqli_query($connection, $sql);
    if ($q === false) {
        $err = mysqli_error($connection);
        $mess = "SQL error: " . $err . "\n";
        $mess .= "SQL errno: " . mysqli_errno($connection) . "\n";
        $mess .= "<br/>";
        $mess .= "SQL: " . $sql . "\n";
        rollback();
        trigger_error($mess, E_USER_ERROR);
    }
    return $q;
}

function connect($host, $dbuser, $password)
{
    global $db_connection;

    mysqli_report(MYSQLI_REPORT_OFF);
    $db_connection = @mysqli_connect($host, $dbuser, $password);
    if ($db_connection === false) {
        throw new RuntimeException(
            'Database connection failed. Please ensure MySQL/MariaDB is running and that the credentials in conf/config.php are correct. ' . mysqli_connect_error()
        );
    }

    if (!mysqli_set_charset($db_connection, 'utf8')) {
        throw new RuntimeException('Failed to set database charset to UTF-8: ' . mysqli_error($db_connection));
    }
    return $db_connection;
}

function select_db($dbname)
{
    return mysqli_select_db(db_connection(), $dbname);
}

function fetch_row($query)
{
    if (!($query instanceof mysqli_result)) {
        throw new RuntimeException('Database fetch expected a mysqli_result, got a null/invalid result.');
    }
    return mysqli_fetch_row($query);
}

function fetch_assoc($query)
{
    if (!($query instanceof mysqli_result)) {
        throw new RuntimeException('Database fetch expected a mysqli_result, got a null/invalid result.');
    }
    return mysqli_fetch_assoc($query);
}

function fetch_array($query)
{
    if (!($query instanceof mysqli_result)) {
        throw new RuntimeException('Database fetch expected a mysqli_result, got a null/invalid result.');
    }
    return mysqli_fetch_array($query);
}

function fetch_object($query)
{
    if (!($query instanceof mysqli_result)) {
        throw new RuntimeException('Database fetch expected a mysqli_result, got a null/invalid result.');
    }
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
    mysqli_begin_transaction(db_connection());
}

function commit()
{
    mysqli_commit(db_connection());
}

function rollback()
{
    global $db_connection;
    if ($db_connection instanceof mysqli) {
        @mysqli_rollback($db_connection);
    }
}

function insert_id()
{
    return mysqli_insert_id(db_connection());
}

/** Quote a scalar as a SQL string literal for legacy interpolated queries. */
function sql_string($value)
{
    if ($value === null) {
        return 'NULL';
    }
    return "'" . mysqli_real_escape_string(db_connection(), (string)$value) . "'";
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
