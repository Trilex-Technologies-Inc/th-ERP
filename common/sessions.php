<?php
	include('include.php');
	
	$selectSQL = <<<SQL
	select
		sessionid,
	    username,
	    unix_timestamp(logintime) as logintime,
	    remote_host
	from session
	order by logintime desc
	limit 50
SQL;

?>

<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Sessions") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Sessions")) ?>

<table>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Time") ?></th>
<th><?php echo tr("User") ?></th>
<th><?php echo tr("Remote host") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
	$i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		echo "<td>$row->sessionid</td>";
		echo "<td>" . formatDate($row->logintime) . ' ' . date('H:i', $row->logintime) . "</td>";
		echo "<td>$row->username</td>";
		echo "<td>$row->remote_host</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
		$i++;
    }
?>
</table>
</body>
