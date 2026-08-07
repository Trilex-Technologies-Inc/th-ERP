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
<table width="100%" cellspacing="0" cellpadding="0" class="menubar module-sidebar">
<tr>
<?php
menu('../sales/index.php', 'Sales', 14, true, null);
menu('../erp/index.php', 'Stock/Inventory', 14, true, null);
menu('../manufacturing/index.php', 'Manufacturing', 14, true, null);
menu('../payroll/employees.php', 'Payroll', 14, true, null);
menu('../project/projects.php', 'Project', 14, true, null);
menu('../accounting/index.php', 'General ledger', 14, true, null);
menu('security.php', 'Common', 16, false, null);
?>
</tr>
</table>

<?php menupage_begin() ?>
<div class="module-heading">
<span class="module-eyebrow"><?php etr("Dashboard") ?></span>
<h1><?php etr("Select module") ?></h1>
</div>
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
