<?php
	include('include.php');

    $description = getParam('description');

	$selectSQL = "
	select
	    groupid,
	    description
	from accountgroup
	where description like '$description%'";

?>

<head>
<title>thERP - <?php etr("Account groups") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Account groups")) ?>

<form action="accountgroups.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $description ?>"/></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="accountgroups.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Description") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>$row->groupid</td>";
        echo "<td><a href='accountgroup.php?groupid=$row->groupid'>$row->description</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<br/>
<table>
<tr>
<td><?php newButton("accountgroup.php") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
</body>
