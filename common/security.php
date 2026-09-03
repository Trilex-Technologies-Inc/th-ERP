<?php include("include.php") ?>

<?php head("Security") ?>

<body>
<?php menubar("security.php") ?>
<?php title(tr("Security")) ?>

<main class="container-fluid px-0">
	<section class="card border-0 shadow-sm mb-4">
		<div class="card-body p-4 p-lg-5">
			<div class="row align-items-center g-4">
				<div class="col-auto">
					<span class="security-hero-icon" aria-hidden="true">◆</span>
				</div>
				<div class="col">
					<span class="module-eyebrow"><?php etr("Common") ?></span>
					<h1 class="h2 fw-bold mt-2 mb-2"><?php etr("Security") ?></h1>
					<p class="text-secondary mb-0"><?php etr("Users") ?> · <?php etr("User groups") ?> · <?php etr("Sessions") ?></p>
				</div>
			</div>
		</div>
	</section>

	<div class="mb-3">
		<h2 class="h5 fw-bold mb-1"><?php etr("Administration") ?></h2>
		<p class="text-secondary mb-0"><?php etr("Security") ?></p>
	</div>

	<section class="row g-4">
		<div class="col-12 col-md-6">
			<a class="security-card card h-100 border-0 shadow-sm text-decoration-none" href="users.php">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary fs-4 flex-shrink-0" aria-hidden="true">◎</span>
					<div class="flex-grow-1"><h3 class="h5 fw-bold mb-2"><?php etr("Users") ?></h3><p class="text-secondary mb-0"><?php etr("Username") ?> · <?php etr("Language") ?> · <?php etr("Password") ?></p></div>
					<span class="text-primary fw-bold" aria-hidden="true">→</span>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6">
			<a class="security-card card h-100 border-0 shadow-sm text-decoration-none" href="usergroups.php">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success fs-4 flex-shrink-0" aria-hidden="true">◉</span>
					<div class="flex-grow-1"><h3 class="h5 fw-bold mb-2"><?php etr("User groups") ?></h3><p class="text-secondary mb-0"><?php etr("Permissions") ?> · <?php etr("Users") ?></p></div>
					<span class="text-primary fw-bold" aria-hidden="true">→</span>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6">
			<a class="security-card card h-100 border-0 shadow-sm text-decoration-none" href="sessions.php">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info fs-4 flex-shrink-0" aria-hidden="true">◷</span>
					<div class="flex-grow-1"><h3 class="h5 fw-bold mb-2"><?php etr("Sessions") ?></h3><p class="text-secondary mb-0"><?php etr("User") ?> · <?php etr("Time") ?> · <?php etr("Remote host") ?></p></div>
					<span class="text-primary fw-bold" aria-hidden="true">→</span>
				</div>
			</a>
		</div>

		<div class="col-12 col-md-6">
			<a class="security-card card h-100 border-0 shadow-sm text-decoration-none" href="logger.php">
				<div class="card-body p-4 d-flex align-items-start gap-3">
					<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning fs-4 flex-shrink-0" aria-hidden="true">≡</span>
					<div class="flex-grow-1"><h3 class="h5 fw-bold mb-2"><?php etr("Logger") ?></h3><p class="text-secondary mb-0"><?php etr("Time") ?> · <?php etr("User") ?> · <?php etr("Description") ?></p></div>
					<span class="text-primary fw-bold" aria-hidden="true">→</span>
				</div>
			</a>
		</div>
	</section>
</main>

<?php bottom() ?>
</body>
