<?php include("include.php") ?>

<head>
<title>thERP</title>
<?php 
styleSheet();
metatag(); 
?>
</head>

<body>

<div class=main>
<?php
top0();
?>
<table width="100%" cellspacing=0 cellpadding=0>
<tr class=menubar><td style="padding: 2">&nbsp;<?php etr("Select module") ?></td></tr>
</table>

<?php menupage_begin() ?>
<ul>
<li class=menupage>
<a href='../sales/index.php' class=menupage>
<?php etr("Sales") ?>
</a>
</li>
<li class=menupage>
<a href='../erp/index.php' class=menupage>
<?php etr("Stock/Inventory") ?>
</a>
</li>
<li class=menupage>
<a href='../manufacturing/index.php' class=menupage>
<?php etr("Manufacturing") ?>
</a>
</li>
<li class=menupage>
<a href='../payroll/employees.php' class=menupage>
<?php etr("Payroll") ?>
</a>
</li>
<li class=menupage>
<a href='../project/projects.php' class=menupage>
<?php etr("Project") ?>
</a>
</li>
<li class=menupage>
<a href='../accounting/index.php' class=menupage>
<?php etr("General ledger") ?>
</a>
</li>
<li class=menupage>
<a href='security.php' class=menupage>
<?php etr("Common") ?>
</a>
</li>
</ul>
<?php menupage_end() ?>
</div>
</body>
