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
<table>
<tr>
<td><?php etr("Description") ?>:</td>
<td><?php textbox("description") ?></td>
<td><input type="submit" name="search" value="<?php etr("Search") ?>"/>
</table>
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
<table>
<tr>
<td><?php saveButton() ?></td>
<td><?php newButton("schedule.php?action=new") ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
