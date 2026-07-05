<?php include("include.php") ?>

<head>
<title>thERP - <?php etr("Sales") ?></title>
<?php styleSheet() ?>
</head>

<body>

<div class=main>
<?php 
menubar('index.php', 'index_help.php');
menupage_begin();
?>
<ul>
<li class=menupage>
<a href='salesorder.php?customerid=1&action=create' id='newcacheorder' tabindex=1 accesskey='C' class=menupage
	title='<?php etr("toolTip_newCashSalesOrder")?>'>
<?php etr("New cash sales order") ?>
</a>
</li>
<script>document.getElementById('newcacheorder').focus();</script>
<li class=menupage>
<a href='customers.php?mode=createorder' tabindex=2 accesskey='O' class=menupage
   title='<?php etr("toolTip_newSalesOrder")?>'>
<?php etr("New sales order") ?>
</a>
</li>
<!-- 
<li class=menupage>
<a href='posclient.php' class=menupage>
<?php etr("Point-of-sales") ?>
</a>
</li>
 -->
<li class=menupage>
<a href='customers.php?mode=createorder&recur=1' tabindex=3 class=menupage
	title='<?php etr("toolTip_newRecurringSalesOrder")?>'>
<?php etr("New recurring sales order") ?>
</a>
</li>
<li class=menupage>
<a href='receipts.php' class=menupage title='<?php etr("toolTip_receipts")?>'>
<?php etr("Receipts") ?>
</a>
</li>
<li class=menupage><a href='sales.php' class=menupage title='<?php etr("toolTip_showSalesOrders")?>'>
<?php etr("Show sales orders") ?></a></li>
<li class=menupage><a href='pricelist.php' class=menupage title='<?php etr("toolTip_priceList")?>'>
<?php etr("Price list") ?></a></li>
<li class=menupage><a href='sales_analysis.php' class=menupage title='<?php etr("toolTip_salesAnalysis")?>'>
<?php etr("Sales analysis") ?></a></li>
</ul>
<?php menupage_end() ?>
</div>
</body>
