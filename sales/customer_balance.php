<?php
	include('include.php');
	include('salesorder.inc.php');

    $name = getParam('name');

	$selectSQL = "
	select
	    c.customerid,
	    name
	from customer c
	where name like '$name%'
	";
?>

<head>
<title>thERP - <?php etr("Customer  balance") ?></title>
<LINK REL=StyleSheet HREF="therp.css" TYPE="text/css">
</head>

<body>

<?php 
include("menubar.php");
title(tr("Customer balance"))
?>

<form action="customer_balance.php" method="GET">
<div class="border">
<table>
<tr><td>Name:</td><td><input type="text" name="name" value="<?php echo $name ?>"/></td>
<tr><td><input type="submit" name="search" value="Search" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<th><?php etr("Balance") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>$row->customerid</td>";
        echo "<td><a href='customer.php?customerid=$row->customerid'>$row->name</a></td>";
		$href = "sales.php?customerid=$row->customerid";
		echo "<td align=right><a href='$href'>" . formatMoney(getCustomerBalance($row->customerid)) . "</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
</body>
