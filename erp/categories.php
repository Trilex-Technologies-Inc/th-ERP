<?php
	include('include.php');

    $description = getParam('description');

	$selectSQL = "
	select
	    categoryid,
	    description
	from category
	where description like '$description%'
	order by description";

?>

<head>
<title>thERP - <?php etr("Categories") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>
<?php title(tr("Categories")) ?>

<main class="categories-page">
	<header class="categories-intro">
		<div class="categories-intro-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M4 6.5 12 3l8 3.5-8 3.5-8-3.5Zm0 5L12 15l8-3.5M4 16.5 12 20l8-3.5"/></svg>
		</div>
		<div>
			<span class="categories-eyebrow"><?php etr("Product configuration") ?></span>
			<h1><?php etr("Categories") ?></h1>
			<p><?php etr("Organize products and manage their accounting and inventory defaults.") ?></p>
		</div>
		<div class="categories-create"><?php newButton("category.php") ?></div>
	</header>

	<form action="categories.php" method="GET" class="categories-filter card border-0 shadow-sm">
		<div class="card-body">
			<div class="categories-section-heading"><span><?php etr("Search") ?></span><h2><?php etr("Find categories") ?></h2></div>
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-9">
					<label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label>
					<div class="categories-search-field"><?php textbox('description', $description) ?></div>
				</div>
				<div class="col-12 col-md-3 d-grid"><?php searchButton() ?></div>
			</div>
		</div>
	</form>

	<section class="categories-list card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white categories-list-header"><div><span><?php etr("Catalog structure") ?></span><h2><?php etr("Product categories") ?></h2></div></div>
		<div class="erp-table-responsive">
			<table class="erp-data-table categories-table">
				<thead><tr><th><?php etr("Id") ?></th><th><?php etr("Description") ?></th><th class="text-end"><?php etr("Action") ?></th></tr></thead>
				<tbody>
				<?php
				$rs = query($selectSQL);
				$count = 0;
				while ($row = fetch_object($rs)) {
					$href = "category.php?categoryid=" . urlencode($row->categoryid);
					$id = htmlspecialchars($row->categoryid);
					$label = htmlspecialchars($row->description);
					echo "<tr><td><span class='category-id'>#$id</span></td><td><a class='category-name' href='$href'>$label</a></td><td class='text-end'><a class='category-open' href='$href'>" . tr("Open") . " <span aria-hidden='true'>&#8594;</span></a></td></tr>";
					$count++;
				}
				if ($count == 0) {
					echo "<tr><td colspan='3'><div class='categories-empty'><span aria-hidden='true'>&#9638;</span><strong>" . tr("No categories found") . "</strong><small>" . tr("Try another description or create a new category.") . "</small></div></td></tr>";
				}
				?>
				</tbody>
			</table>
		</div>
		<div class="categories-list-footer"><span><?php echo $count ?> <?php etr("categories") ?></span><?php newButton("category.php") ?></div>
	</section>
</main>
<?php bottom() ?>
</body>
