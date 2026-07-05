<?php include("include.php") ?>

<head>
<title>thERP - <?php etr("Accounting") ?></title>
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
<a href='register_transaction.php' class=menupage>
<?php etr("Generic transaction") ?>
</a>
</li>
<li class=menupage>
<a href='expense_trans.php' class=menupage>
<?php etr("Expense transaction") ?>
</a>
</li>
</ul>
<?php menupage_end() ?>
</div>
</body>
