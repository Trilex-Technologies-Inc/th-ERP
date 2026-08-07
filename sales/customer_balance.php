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
<div class="border p-3 mb-4">
	<div class="row g-3 align-items-end">
		<div class="col-md-6">
			<label class="form-label"><?php etr("Name") ?></label>
			<input type="text" name="name" value="<?php echo htmlspecialchars($name) ?>" class="form-control" />
		</div>
		<div class="col-auto">
			<input type="submit" name="search" value="<?php etr("Search") ?>" class="btn btn-primary" />
		</div>
	</div>
</div>
</form>
&nbsp;

<div class="table-responsive">
<table class="table table-sm table-striped table-hover align-middle w-100">
<thead>
<tr>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<th class="text-end"><?php etr("Balance") ?></th>
</tr>
</thead>
<tbody>
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
