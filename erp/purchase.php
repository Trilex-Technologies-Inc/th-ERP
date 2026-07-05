<?php include("include.php") ?>

<head>
<title>thERP - <?php etr("Transactions") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('purchase.php') ?>

<?php menupage_begin() ?>
<ul>
<li class=menupage><a href='suppliers.php?mode=createorder' class=menupage><?php etr("New purchase order") ?></a></li>
<li class=menupage><a href='purchaseorders.php' class=menupage><?php etr("Purchase orders") ?></a></li>
<li class=menupage><a href='suppliers.php?mode=payable' class=menupage><?php etr("New payable") ?></a></li>
<li class=menupage><a href='payables.php' class=menupage><?php etr("Payables") ?></a></li>
<li class=menupage><a href='suppliers.php' class=menupage><?php etr("Suppliers") ?></a></li>
<li class=menupage><a href='supplier_balance.php' class=menupage><?php etr("Supplier balance") ?></a></li>
</ul>
<?php menupage_end() ?>

</table>
</body>
