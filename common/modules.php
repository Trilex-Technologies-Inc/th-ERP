<?php include("include.php") ?>

<head>
<title>thERP</title>
<?php 
styleSheet();
metatag(); 
?>
</head>

<body>
<?php
top0();
?>
<nav class="app-sidebar module-sidebar" aria-label="<?php etr("Module navigation") ?>">
	<div class="app-nav-list">
		<?php
		menu('../sales/index.php', 'Sales', 14, true, null);
		menu('../erp/index.php', 'Stock/Inventory', 14, true, null);
		menu('../manufacturing/index.php', 'Manufacturing', 14, true, null);
		menu('../payroll/employees.php', 'Payroll', 14, true, null);
		menu('../project/projects.php', 'Project', 14, true, null);
		menu('../accounting/index.php', 'General ledger', 14, true, null);
		menu('security.php', 'Common', 16, false, null);
		?>
	</div>
</nav>

<main class="module-picker">
	<header class="module-picker-hero">
		<div>
			<span class="module-eyebrow"><?php etr("Workspace") ?></span>
			<h1><?php etr("Select module") ?></h1>
			<p><?php etr("Choose an area to continue working in thERP.") ?></p>
		</div>
		<div class="module-hero-mark" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
	</header>

	<div class="module-picker-heading">
		<div><span><?php etr("Modules") ?></span><h2><?php etr("Business areas") ?></h2></div>
		<small>7 <?php etr("available") ?></small>
	</div>

	<nav class="module-grid" aria-label="<?php etr("Module navigation") ?>">
		<a class="module-card" href="../sales/index.php">
			<span class="module-card-icon module-icon-sales" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
			<span class="module-card-copy"><strong><?php etr("Sales") ?></strong><small><?php etr("Customers") ?> · <?php etr("Sales orders") ?> · <?php etr("Receipts") ?></small></span><span class="module-card-arrow" aria-hidden="true">&#8594;</span>
		</a>
		<a class="module-card" href="../erp/index.php">
			<span class="module-card-icon module-icon-stock" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7 12 3l8 4-8 4-8-4ZM4 7v10l8 4 8-4V7M12 11v10"/></svg></span>
			<span class="module-card-copy"><strong><?php etr("Stock/Inventory") ?></strong><small><?php etr("Products") ?> · <?php etr("Purchase") ?> · <?php etr("Stock move") ?></small></span><span class="module-card-arrow" aria-hidden="true">&#8594;</span>
		</a>
		<a class="module-card" href="../manufacturing/index.php">
			<span class="module-card-icon module-icon-manufacturing" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 21V10l6 4v-4l6 4V7h4v14H4ZM8 21v-3M13 21v-3M18 21v-3"/></svg></span>
			<span class="module-card-copy"><strong><?php etr("Manufacturing") ?></strong><small><?php etr("Production orders") ?> · <?php etr("Products") ?></small></span><span class="module-card-arrow" aria-hidden="true">&#8594;</span>
		</a>
		<a class="module-card" href="../payroll/employees.php">
			<span class="module-card-icon module-icon-payroll" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M16 11h6"/></svg></span>
			<span class="module-card-copy"><strong><?php etr("Payroll") ?></strong><small><?php etr("Employees") ?> · <?php etr("Reporting") ?> · <?php etr("Schedules") ?></small></span><span class="module-card-arrow" aria-hidden="true">&#8594;</span>
		</a>
		<a class="module-card" href="../project/projects.php">
			<span class="module-card-icon module-icon-project" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4zM8 3v4M16 3v4M4 9h16M8 13h3M8 16h6"/></svg></span>
			<span class="module-card-copy"><strong><?php etr("Project") ?></strong><small><?php etr("Projects") ?> · <?php etr("Debit") ?></small></span><span class="module-card-arrow" aria-hidden="true">&#8594;</span>
		</a>
		<a class="module-card" href="../accounting/index.php">
			<span class="module-card-icon module-icon-ledger" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4zM8 4v16M12 8h4M12 12h4M12 16h4"/></svg></span>
			<span class="module-card-copy"><strong><?php etr("General ledger") ?></strong><small><?php etr("Transactions") ?> · <?php etr("Accounts") ?> · <?php etr("Balance") ?></small></span><span class="module-card-arrow" aria-hidden="true">&#8594;</span>
		</a>
		<a class="module-card module-card-wide" href="security.php">
			<span class="module-card-icon module-icon-common" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10ZM9 12l2 2 4-5"/></svg></span>
			<span class="module-card-copy"><strong><?php etr("Common") ?></strong><small><?php etr("Security") ?> · <?php etr("Languages") ?> · <?php etr("Company info") ?></small></span><span class="module-card-arrow" aria-hidden="true">&#8594;</span>
		</a>
	</nav>
</main>
<?php bottom() ?>
</body>
