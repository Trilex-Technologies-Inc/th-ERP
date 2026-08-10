<?php
	include('include.php');

	checkPermission(PERMISSION_ADMINISTRATE_USERS);

	$del_username = getParam('del_username');
	if (!isEmpty($del_username)) {
		sql("delete from user where username='$del_username'");
	}

    $full_name = getParam('full_name');

	$selectSQL = <<<SQL
	select
	    username,
	    full_name,
	    language,
		password
	from user
	where full_name like '$full_name%'
SQL;

$languages = rs2array(query("select language, description from language"));
?>

<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Users") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("security.php") ?>
<?php title(tr("Users")) ?>

<form action="users.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Name") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="full_name" value="<?php echo  getParam('full_name') ?>"/></div></div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php echo tr("Search") ?>" /></div></div>

</div>
</div>
</form>
&nbsp;

<form action="users.php" method=POST>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Username") ?></th>
<th><?php echo tr("Name") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
	$i = 0;
    while ($row = fetch_object($rs)) {
		echo "<input type=hidden name=username_$i value='$row->username'/>";
        echo "<tr class='$class'>";
		echo "<td align=center>";
		deleteIcon("users.php?del_username=$row->username");
		echo "</td>";
		echo "<td><a href='user.php?uname=$row->username'>$row->username</a></td>";
		echo "<td>$row->full_name</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
		$i++;
    }
?>
</table>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("user.php") ?></div>
</div>
</div>
</form>
</body>
