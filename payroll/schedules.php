<?php
	include('include.php');

    $name = getParam('name');
    $description = getParam('description');

    $rowcount = getParam("rowcount");
    $i = 0;
    while ($i < $rowcount) {
        $del = getParam("del_$i");
        if ($del == "on") {
            $scheduleid = getParam("scheduleid_$i");
            $sql = "delete from workshift w where scheduleid exists ";
            $sql .= "(select scheduleid from schedule_shift ss where ss.shiftid=w.shiftid and ss.scheduleid=$scheduleid)";
            //sql($sql);
            $sql = "delete from schedule_shift where scheduleid=$scheduleid";
            sql($sql);
            $sql = "delete from schedule where scheduleid=$scheduleid";
            sql($sql);
        }
        $i++;
    }
?>

<head>
<?php metatag() ?>
<title>Payroll - <?php etr("Schedules") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Schedules")) ?>

<form action="schedules.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php etr("Description") ?>:</div>
<div class="col-12 col-md-auto"><?php textbox("description") ?></div>
<div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>"/>
</div></div></div>
</div>
</form>

<form action="schedules.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<?php
    $sql = <<<SQL
    select
      scheduleid,
      description
    from schedule
    where description like '$description%'
SQL;
    $rs = query($sql);
    $i = 0;
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<input type=hidden name=scheduleid_$i value='$row->scheduleid'/>";
        echo "<tr class='$class'>";
        echo "<td align=center><input type=checkbox name='del_$i'/></td>";
        echo "<td>$row->scheduleid</td>";
        echo "<td><a href='schedule.php?scheduleid=$row->scheduleid'>$row->description</a></td>";
        echo "</tr>\n";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
    echo "<input type=hidden name=rowcount value='$i'/>";
?>
</table>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
<div class="col-12 col-md-auto"><?php newButton("schedule.php?action=new") ?></div>
</div>
</div>
</form>
<?php bottom() ?>
</body>
