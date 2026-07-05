<?php
include('include.php');
$productid = getParam('productid');

$sql = "
select
  po.orderid,
  unitprice,
  quantity,
  unix_timestamp(orderdate) as orderdate
from purchaseorder_item pi
join purchaseorder po on po.orderid=pi.orderid
where productid=$productid
";

$rs = query($sql);

$model = findValue("select model from product where productid=$productid");
?>

<head>
<?php metatag() ?>
<title>thERP - <?php etr("Purchase price history") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('purchase.php') ?>
<?php
title("Product > <a href='product.php?productid=$productid'>$model</a> > Purchase price history");
?>
<br/>
<div class="border">

<table>
<th><?php etr("Date") ?></th>
<th><?php etr("Purchase order") ?></th>
<th><?php etr("Price") ?></th>
<th><?php etr("Quantity") ?></th>
<?php
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>" . formatDate($row->orderdate) . "</td>";
        echo "<td><a href='purchaseorder.php?orderid=$row->orderid'>$row->orderid</a></td>";
        echo "<td align=right>";
        echo formatMoney($row->unitprice);
        echo "</td>";
        echo "<td align=right>$row->quantity</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<?php bottom() ?>
</body>
