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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Surname") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="surname" value="<?php echo  getParam('surname') ?>"/></div></div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Show inactive") ?>:</div><div class="col-12 col-md-auto"><input type=checkbox name=inactive <?php echo  $inactive ? "checked" : "" ?> /></div></div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php echo tr("Search") ?>" /></div></div>

</div>
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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("employee_detail.php") ?></div>
</div>
</div>
</form>
<?php bottom() ?>	
</body>
