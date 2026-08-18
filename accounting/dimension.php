<?php
	include('include.php');

	$dimid = getParam('dimid');
	$new = true;
	$name = "";
	$type = 0;
	if (isSave()) {
		$dimid = getParam('dimid');
		$name = getParam('name');
		if (isNew()) {
			$sql = "insert into dimension (dimid, name)  values (";
			$sql = $sql . "$dimid,";
			$sql = $sql . "'$name'";
			$sql = $sql . ")";
			sql($sql);
			$dimid = insert_id();
			header("Location: dimensions.php");
			die;
		} else {
            $updateSQL =
    			"update dimension set
    			    name='$name'
                where dimid=$dimid";
    		sql($updateSQL);
		}
	}

	if (!isEmpty($dimid)) {
	    $selectSQL =
  		"select dimid,
		       name
		from dimension
		where dimid=$dimid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$dimid = $rec->dimid;
			$name = $rec->name;
			$new = false;
		}
	}

?>
<head>
<title>thERP - <?php etr("Dimension") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = $name;
if ($new)
	$title = tr("Create");
title("<a href='dimensions.php'>" . tr("Dimensions") . "</a> &gt; " . htmlspecialchars($title))
?>

<main class="container-fluid px-0">
<section class="card border-0 shadow-sm mb-4">
	<div class="card-body p-4 p-lg-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
		<div class="d-flex align-items-center gap-3">
			<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary fs-4 flex-shrink-0" aria-hidden="true">◇</span>
			<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Accounting structure") ?></span><h1 class="h3 fw-bold mt-1 mb-1"><?php echo $new ? tr("Create dimension") : htmlspecialchars($name) ?></h1><p class="text-secondary mb-0"><?php etr("Define a reporting dimension for organizing ledger accounts") ?></p></div>
		</div>
		<span class="badge rounded-pill <?php echo $new ? 'text-bg-primary' : 'text-bg-light border' ?> px-3 py-2"><?php echo $new ? tr("New dimension") : tr("Dimension") . ' #' . htmlspecialchars($dimid) ?></span>
	</div>
</section>

<form action="dimension.php" method="POST">
<section class="card border-0 shadow-sm overflow-hidden">
	<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Details") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Dimension information") ?></h2></div>
	<div class="card-body p-4">
		<div class="row g-4">
			<div class="col-12 col-md-4">
				<label class="form-label fw-semibold" for="dimid"><?php etr("Dimension ID") ?></label>
				<input id="dimid" type="number" name="dimid" value="<?php echo htmlspecialchars($dimid) ?>" min="1" required <?php echo $new ? '' : 'readonly' ?>/>
				<div class="form-text"><?php etr("Unique numeric identifier") ?></div>
			</div>
			<div class="col-12 col-md-8">
				<label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label>
				<input id="name" type="text" name="name" value="<?php echo htmlspecialchars($name) ?>" required/>
				<div class="form-text"><?php etr("A clear label used throughout accounting reports") ?></div>
			</div>
		</div>
	</div>
	<div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3">
		<a class="btn btn-outline-secondary" href="dimensions.php">&#8592; <?php etr("Back to dimensions") ?></a>
		<?php saveButton() ?>
	</div>
</section>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>
</body>
