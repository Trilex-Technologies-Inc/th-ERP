<?php
	include('include.php');
	
	$selectSQL = <<<SQL
	select
		loggid,
	    username,
	    unix_timestamp(loggtime) as loggtime,
	    loggtext
	from logger
	order by loggid desc
	limit 20
SQL;

?>

<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Error log") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Error log")) ?>

<table>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Time") ?></th>
<th><?php echo tr("User") ?></th>
<th><?php echo tr("Error") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
	$i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		echo "<td><a href='logg.php?loggid=$row->loggid'>$row->loggid</a></td>";
		echo "<td>" . formatDate($row->loggtime) . ' ' . date('H:i', $row->loggtime) . "</td>";
		echo "<td>$row->username</td>";
		echo "<td><pre>$row->loggtext</pre></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
		$i++;
    }
?>
</table>
</body>
