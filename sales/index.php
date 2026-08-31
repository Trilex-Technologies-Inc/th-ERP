<?php include("include.php") ?>

<?php head("Sales") ?>

<body>
<?php menubar('index.php', 'index_help.php') ?>
<?php title(tr("Sales")) ?>

<main class="container-fluid px-0">
	<section class="card border-0 shadow-sm mb-4 overflow-hidden">
		<div class="card-body p-4 p-lg-5">
			<div class="row align-items-center g-4">
				<div class="col-lg-6">
					<span class="badge text-bg-light border mb-3"><?php etr("Sales") ?></span>
					<h1 class="h2 fw-bold mb-2"><?php etr("Sales") ?></h1>
					<p class="text-secondary mb-0"><?php etr("Customers") ?> · <?php etr("Sales orders") ?> · <?php etr("Receipts") ?></p>
				</div>
				<div class="col-lg-6">
					<div class="d-flex flex-wrap gap-2 justify-content-lg-end">
						<a href="salesorder.php?customerid=1&amp;action=create" id="newcashorder" accesskey="C" class="btn btn-primary" title="<?php etr("toolTip_newCashSalesOrder") ?>">
							<?php etr("New cash sales order") ?>
						</a>
						<a href="customers.php?mode=createorder" accesskey="O" class="btn btn-outline-primary" title="<?php etr("toolTip_newSalesOrder") ?>">
							<?php etr("New sales order") ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<div class="mb-3">
		<h2 class="h5 fw-bold mb-1"><?php etr("Quick access") ?></h2>
		<p class="text-secondary mb-0"><?php etr("Sales") ?></p>
	</div>

	<section class="row g-4">
		<div class="col-12 col-md-6 col-xl-4">
			<a href="pos_shift.php" class="card h-100 border-0 shadow-sm text-decoration-none">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success fs-4 flex-shrink-0" aria-hidden="true">◷</span>
					<div><h3 class="h6 fw-bold mb-2"><?php etr("Open / close register") ?></h3><p class="text-secondary small mb-0"><?php etr("Shift counting") ?> / <?php etr("Daily report") ?></p></div>
				</div>
			</a>
		</div>
		<div class="col-12 col-md-6 col-xl-4">
			<a href="posclient.php" class="card h-100 border-0 shadow-sm text-decoration-none" target="therp_pos" onclick="window.open(this.href, this.target, 'popup=yes,width=1440,height=900,resizable=yes,scrollbars=yes'); return false;">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary fs-4 flex-shrink-0" aria-hidden="true">▣</span>
					<div><h3 class="h6 fw-bold mb-2"><?php etr("Point of sale") ?></h3><p class="text-secondary small mb-0"><?php etr("Browser POS") ?> / <?php etr("Cash sales") ?></p></div>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6 col-xl-4">
			<a href="customers.php?mode=createorder&amp;recur=1" class="card h-100 border-0 shadow-sm text-decoration-none" title="<?php etr("toolTip_newRecurringSalesOrder") ?>">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary fs-4 flex-shrink-0" aria-hidden="true">↻</span>
					<div><h3 class="h6 fw-bold mb-2"><?php etr("New recurring sales order") ?></h3><p class="text-secondary small mb-0"><?php etr("Recurring") ?> / <?php etr("Sales orders") ?></p></div>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6 col-xl-4">
			<a href="receipts.php" class="card h-100 border-0 shadow-sm text-decoration-none" title="<?php etr("toolTip_receipts") ?>">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success fs-4 flex-shrink-0" aria-hidden="true">✓</span>
					<div><h3 class="h6 fw-bold mb-2"><?php etr("Receipts") ?></h3><p class="text-secondary small mb-0"><?php etr("Receipt") ?> / <?php etr("Customers") ?></p></div>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6 col-xl-4">
			<a href="sales.php" class="card h-100 border-0 shadow-sm text-decoration-none" title="<?php etr("toolTip_showSalesOrders") ?>">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info fs-4 flex-shrink-0" aria-hidden="true">▤</span>
					<div><h3 class="h6 fw-bold mb-2"><?php etr("Show sales orders") ?></h3><p class="text-secondary small mb-0"><?php etr("Sales orders") ?> / <?php etr("Search") ?></p></div>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6 col-xl-4">
			<a href="customers.php" class="card h-100 border-0 shadow-sm text-decoration-none">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning fs-4 flex-shrink-0" aria-hidden="true">◎</span>
					<div><h3 class="h6 fw-bold mb-2"><?php etr("Customers") ?></h3><p class="text-secondary small mb-0"><?php etr("Customers") ?> / <?php etr("Balance") ?></p></div>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6 col-xl-4">
			<a href="pricelist.php" class="card h-100 border-0 shadow-sm text-decoration-none" title="<?php etr("toolTip_priceList") ?>">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-danger-subtle text-danger fs-4 flex-shrink-0" aria-hidden="true">$</span>
					<div><h3 class="h6 fw-bold mb-2"><?php etr("Price list") ?></h3><p class="text-secondary small mb-0"><?php etr("Products") ?> / <?php etr("Price") ?></p></div>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6 col-xl-4">
			<a href="sales_analysis.php" class="card h-100 border-0 shadow-sm text-decoration-none" title="<?php etr("toolTip_salesAnalysis") ?>">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-secondary-subtle text-secondary fs-4 flex-shrink-0" aria-hidden="true">↗</span>
					<div><h3 class="h6 fw-bold mb-2"><?php etr("Sales analysis") ?></h3><p class="text-secondary small mb-0"><?php etr("Sales") ?> / <?php etr("Reporting") ?></p></div>
				</div>
			</a>
		</div>
	</section>
</main>

<script>document.getElementById('newcashorder').focus();</script>
<?php bottom() ?>
</body>
