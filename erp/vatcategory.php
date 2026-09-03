<?php
	include('include.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);
	
	$vatcatid = getParam('vatcatid');
	$new = true;
	if (isSave()) {
		$vatcatid = getParam('vatcatid');
		$description = getParam('description');
		$percent = getParam('percent');
		if (isNew()) {
			$sql = "insert into vat_category (description, percent)  
			        values ('$description', $percent)";
			sql($sql);
			$vatcatid = insert_id();
		} else {
            $updateSQL =
    			"update vat_category set
    				description='$description',
    			    percent=$percent
                where vatcatid='$vatcatid'";
    		sql($updateSQL);
		}
	}
	if (isDelete()) {
		sql("delete from vat_category where vatcatid='$vatcatid'");
		$vatcatid = null;
	}

	$rec = new Dummy();
	if (!isEmpty($vatcatid)) {
	    $selectSQL =
  		"select vatcatid,
  		       description,
			   percent
		from vat_category
		where vatcatid='$vatcatid'
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
		}
	}

?>
<head>
<title>thERP - <?php etr("VAT category") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar('configuration.php') ?>
<?php title("<a href='vatcategories.php'>" . tr("VAT categories") . "</a> &gt; " . ($new ? tr("Create") : htmlspecialchars($rec->description))) ?>

<main class="container-fluid px-0">
<section class="card border-0 shadow-sm mb-4">
	<div class="card-body p-4 p-lg-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
		<div class="d-flex align-items-center gap-3">
			<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info fs-4 flex-shrink-0" aria-hidden="true">%</span>
			<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Tax configuration") ?></span><h1 class="h3 fw-bold mt-1 mb-1"><?php echo $new ? tr("Create VAT category") : htmlspecialchars($rec->description) ?></h1><p class="text-secondary mb-0"><?php etr("Define the VAT rate applied to products and transactions") ?></p></div>
		</div>
		<span class="badge rounded-pill <?php echo $new ? 'text-bg-primary' : 'text-bg-light border' ?> px-3 py-2"><?php echo $new ? tr("New category") : tr("VAT category") . ' #' . htmlspecialchars($vatcatid) ?></span>
	</div>
</section>

<form action="vatcategory.php" method="POST">
<section class="card border-0 shadow-sm overflow-hidden">
	<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Details") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("VAT category information") ?></h2></div>
	<div class="card-body p-4">
		<div class="row g-4">
			<?php if (!$new) { ?><div class="col-12 col-md-3"><label class="form-label fw-semibold"><?php etr("Category ID") ?></label><div class="form-control bg-body-tertiary font-monospace">#<?php echo htmlspecialchars($vatcatid) ?></div><input type="hidden" name="vatcatid" value="<?php echo htmlspecialchars($vatcatid) ?>"/></div><?php } ?>
			<div class="col-12 <?php echo $new ? 'col-md-8' : 'col-md-6' ?>"><label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label><input id="description" type="text" name="description" value="<?php echo htmlspecialchars($rec->description) ?>" required/><div class="form-text"><?php etr("A recognizable name shown in product and tax settings") ?></div></div>
			<div class="col-12 col-md-3"><label class="form-label fw-semibold" for="percent"><?php etr("Tax rate") ?></label><div class="input-group"><?php numberbox("percent", $rec->percent) ?><span class="input-group-text">%</span></div><div class="form-text"><?php etr("Percentage applied to the taxable amount") ?></div></div>
		</div>
	</div>
	<div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 px-4 py-3">
		<div><?php if (!$new) { deleteSubmitIcon('delete', tr('Delete this VAT category?')); } ?></div>
		<div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="vatcategories.php"><?php etr("Cancel") ?></a><?php saveButton() ?></div>
	</div>
</section>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>
</body>
