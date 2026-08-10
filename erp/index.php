<?php
include('include.php');
?>

<?php head("Stock/Inventory") ?>

<body>
<?php menubar('index.php') ?>
<?php title(tr("Stock/Inventory")) ?>

<main class="container-fluid px-0">
	<section class="card border-0 shadow-sm overflow-hidden mb-4">
		<div class="card-body p-4 p-lg-5 bg-primary text-white">
			<div class="row align-items-center g-4">
				<div class="col-lg-8">
					<span class="badge bg-white text-primary mb-3"><?php etr("Stock/Inventory") ?></span>
					<h1 class="display-6 fw-bold text-white mb-3"><?php etr("Order/Stock") ?></h1>
					<p class="lead mb-0 opacity-75"><?php etr("Products") ?> · <?php etr("Purchase") ?> · <?php etr("Stock move") ?></p>
				</div>
				<div class="col-lg-4">
					<div class="d-flex flex-wrap gap-2 justify-content-lg-end">
						<a class="btn btn-light text-primary fw-semibold" href="product.php"><?php etr("Add product") ?></a>
						<a class="btn btn-outline-light fw-semibold" href="suppliers.php?mode=createorder"><?php etr("New purchase order") ?></a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<div class="d-flex justify-content-between align-items-end mb-3">
		<div>
			<h2 class="h4 fw-bold mb-1"><?php etr("Quick access") ?></h2>
			<p class="text-secondary mb-0"><?php etr("Stock/Inventory") ?></p>
		</div>
	</div>

	<section class="row g-4 mb-4">
		<div class="col-12 col-md-6 col-xl-4">
			<a class="card h-100 border-0 border-start border-4 border-primary shadow-sm text-decoration-none" href="products.php">
				<div class="card-body p-4 d-flex gap-3 align-items-start">
					<span class="fs-2 text-primary" aria-hidden="true">▦</span>
					<div><h3 class="h5 fw-bold mb-2"><?php etr("Products") ?></h3><p class="text-secondary mb-0"><?php etr("Products") ?> / <?php etr("Stock/Inventory") ?></p></div>
				</div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="card h-100 border-0 border-start border-4 border-success shadow-sm text-decoration-none" href="purchase.php">
				<div class="card-body p-4 d-flex gap-3 align-items-start">
					<span class="fs-2 text-success" aria-hidden="true">🛒</span>
					<div><h3 class="h5 fw-bold mb-2"><?php etr("Purchase") ?></h3><p class="text-secondary mb-0"><?php etr("Purchase orders") ?> / <?php etr("Payables") ?></p></div>
				</div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="card h-100 border-0 border-start border-4 border-info shadow-sm text-decoration-none" href="goodsmoves.php">
				<div class="card-body p-4 d-flex gap-3 align-items-start">
					<span class="fs-2 text-info" aria-hidden="true">⇄</span>
					<div><h3 class="h5 fw-bold mb-2"><?php etr("Stock move") ?></h3><p class="text-secondary mb-0"><?php etr("Stock move") ?> / <?php etr("Locations") ?></p></div>
				</div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="card h-100 border-0 border-start border-4 border-warning shadow-sm text-decoration-none" href="suppliers.php">
				<div class="card-body p-4 d-flex gap-3 align-items-start">
					<span class="fs-2 text-warning" aria-hidden="true">♙</span>
					<div><h3 class="h5 fw-bold mb-2"><?php etr("Suppliers") ?></h3><p class="text-secondary mb-0"><?php etr("Suppliers") ?> / <?php etr("Supplier balance") ?></p></div>
				</div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="card h-100 border-0 border-start border-4 border-danger shadow-sm text-decoration-none" href="inventory_report.php">
				<div class="card-body p-4 d-flex gap-3 align-items-start">
					<span class="fs-2 text-danger" aria-hidden="true">▤</span>
					<div><h3 class="h5 fw-bold mb-2"><?php etr("Inventory report") ?></h3><p class="text-secondary mb-0"><?php etr("Reporting") ?> / <?php etr("Quantity") ?></p></div>
				</div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a class="card h-100 border-0 border-start border-4 border-secondary shadow-sm text-decoration-none" href="configuration.php">
				<div class="card-body p-4 d-flex gap-3 align-items-start">
					<span class="fs-2 text-secondary" aria-hidden="true">⚙</span>
					<div><h3 class="h5 fw-bold mb-2"><?php etr("Configuration") ?></h3><p class="text-secondary mb-0"><?php etr("Categories") ?> / <?php etr("Locations") ?></p></div>
				</div>
			</a>
		</div>
	</section>
</main>

<?php bottom() ?>
</body>
