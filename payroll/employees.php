<?php
	include('include.php');

    $surname = getParam('surname');
    $givenname = getParam('givenname');

    $deleteSQL = "update employee set active=0 where employeeid=:a0";

	$inactive = getParam("inactive") == "on" ? 1 : 0;

	$selectSQL = <<<SQL
	select
	    employeeid,
	    givenname,
	    surname
	from employee
	where surname like '$surname%' and givenname like '$givenname%'
SQL;
    if (!$inactive)
        $selectSQL .= " and active=1";

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Employees") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("employees.php") ?>
<br>
<form action="employees.php" method="GET">
<div class="border">
<table>
<tr><td><?php echo tr("Surname") ?>:</td><td><input type="text" name="surname" value="<?php echo  getParam('surname') ?>"/></td></tr>
<tr><td><?php echo tr("Show inactive") ?>:</td><td><input type=checkbox name=inactive <?php echo  $inactive ? "checked" : "" ?> /></td></tr>
<tr><td><input type="submit" name="search" value="<?php echo tr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>

<form action="employees.php" method=POST>
<table width='100%'>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Name") ?></th>
<th><?php echo tr("Calendar") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>$row->employeeid</td>";
        echo "<td><a href='employee_detail.php?employeeid=$row->employeeid'>$row->givenname $row->surname</a></td>";
        echo "<td align=center><a href='calendar.php?employeeid=$row->employeeid'>";
        image("calendar.png");
        echo "</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php newButton("employee_detail.php") ?></td>
</tr>
</table>
</form>
<?php bottom() ?>	
</body>
