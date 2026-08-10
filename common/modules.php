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

<main class="container py-4 py-lg-5 module-picker">
	<header class="mb-4 mb-lg-5">
		<span class="module-eyebrow"><?php etr("Dashboard") ?></span>
		<h1 class="display-6 fw-bold mt-2 mb-2"><?php etr("Select module") ?></h1>
		<p class="text-secondary mb-0"><?php etr("Switch module") ?></p>
	</header>

	<nav class="row g-4" aria-label="<?php etr("Module navigation") ?>">
		<div class="col-12 col-md-6 col-xl-4">
			<a class="module-card card h-100 border-0 shadow-sm text-decoration-none" href="../sales/index.php">
				<div class="card-body p-4"><span class="module-card-icon bg-primary-subtle text-primary">$</span><h2 class="h5 fw-bold mt-4 mb-2"><?php etr("Sales") ?></h2><p class="text-secondary mb-0"><?php etr("Customers") ?> · <?php etr("Sales orders") ?> · <?php etr("Receipts") ?></p></div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="module-card card h-100 border-0 shadow-sm text-decoration-none" href="../erp/index.php">
				<div class="card-body p-4"><span class="module-card-icon bg-success-subtle text-success">▦</span><h2 class="h5 fw-bold mt-4 mb-2"><?php etr("Stock/Inventory") ?></h2><p class="text-secondary mb-0"><?php etr("Products") ?> · <?php etr("Purchase") ?> · <?php etr("Stock move") ?></p></div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="module-card card h-100 border-0 shadow-sm text-decoration-none" href="../manufacturing/index.php">
				<div class="card-body p-4"><span class="module-card-icon bg-warning-subtle text-warning">⚙</span><h2 class="h5 fw-bold mt-4 mb-2"><?php etr("Manufacturing") ?></h2><p class="text-secondary mb-0"><?php etr("Production orders") ?> · <?php etr("Products") ?></p></div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="module-card card h-100 border-0 shadow-sm text-decoration-none" href="../payroll/employees.php">
				<div class="card-body p-4"><span class="module-card-icon bg-info-subtle text-info">◉</span><h2 class="h5 fw-bold mt-4 mb-2"><?php etr("Payroll") ?></h2><p class="text-secondary mb-0"><?php etr("Employees") ?> · <?php etr("Reporting") ?> · <?php etr("Schedules") ?></p></div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="module-card card h-100 border-0 shadow-sm text-decoration-none" href="../project/projects.php">
				<div class="card-body p-4"><span class="module-card-icon bg-danger-subtle text-danger">◇</span><h2 class="h5 fw-bold mt-4 mb-2"><?php etr("Project") ?></h2><p class="text-secondary mb-0"><?php etr("Projects") ?> · <?php etr("Debit") ?></p></div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="module-card card h-100 border-0 shadow-sm text-decoration-none" href="../accounting/index.php">
				<div class="card-body p-4"><span class="module-card-icon bg-secondary-subtle text-secondary">≡</span><h2 class="h5 fw-bold mt-4 mb-2"><?php etr("General ledger") ?></h2><p class="text-secondary mb-0"><?php etr("Transactions") ?> · <?php etr("Accounts") ?> · <?php etr("Balance") ?></p></div>
			</a>
		</div>
		<div class="col-12">
			<a class="module-card module-card-wide card border-0 shadow-sm text-decoration-none" href="security.php">
				<div class="card-body p-4 d-md-flex align-items-center gap-4"><span class="module-card-icon bg-dark-subtle text-dark flex-shrink-0">⌘</span><div><h2 class="h5 fw-bold mb-2 mt-3 mt-md-0"><?php etr("Common") ?></h2><p class="text-secondary mb-0"><?php etr("Security") ?> · <?php etr("Languages") ?> · <?php etr("Company info") ?></p></div><span class="ms-md-auto text-primary fw-bold" aria-hidden="true">→</span></div>
			</a>
		</div>
	</nav>
</main>
<?php bottom() ?>
</body>
