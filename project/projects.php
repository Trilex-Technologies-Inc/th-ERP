<?php
	include('include.php');

    $description = getParam('description');

	$del_projectid = getParam('del_projectid');
	if (!isEmpty($del_projectid)) {
		sql("delete from task where projectid=$del_projectid");
		sql("delete from project where projectid=$del_projectid");
	}
	
	$selectSQL = "
	select
	    projectid,
	    description
	from project
	where description like '$description%'
	order by description";

?>

<head>
<title>thERP - <?php etr("Projects") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php top("projects", "Projects") ?>

<main class="projects-page">
	<header class="projects-intro">
		<div class="projects-intro-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h6l2 2h8v10H4V7Zm0 4h16"/></svg></div>
		<div><span class="projects-eyebrow"><?php etr("Project management") ?></span><h1><?php etr("Projects") ?></h1><p><?php etr("Organize customer work, tasks, products, and payroll activity.") ?></p></div>
		<div class="projects-create"><?php newButton("project.php") ?></div>
	</header>

	<form action="projects.php" method="GET" class="projects-filter card border-0 shadow-sm">
		<div class="card-body">
			<div class="projects-section-heading"><span><?php etr("Search") ?></span><h2><?php etr("Find projects") ?></h2></div>
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-9"><label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label><input id="description" type="text" name="description" value="<?php echo htmlspecialchars($description) ?>" /></div>
				<div class="col-12 col-md-3 d-grid"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div>
			</div>
		</div>
	</form>

	<section class="projects-list card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white projects-list-header"><div><span><?php etr("Workspace") ?></span><h2><?php etr("Project directory") ?></h2></div></div>
		<div class="erp-table-responsive"><table class="erp-data-table projects-table">
			<thead><tr><th class="project-delete"><?php etr("Delete") ?></th><th><?php etr("Id") ?></th><th><?php etr("Description") ?></th><th class="text-end"><?php etr("Action") ?></th></tr></thead><tbody>
			<?php
			$rs = query($selectSQL);
			$count = 0;
			while ($row = fetch_object($rs)) {
				$id = htmlspecialchars($row->projectid);
				$label = htmlspecialchars($row->description);
				$href = "project.php?projectid=" . urlencode($row->projectid);
				echo "<tr><td class='project-delete'>";
				deleteIcon("projects.php?del_projectid=" . urlencode($row->projectid));
				echo "</td><td><span class='project-id'>#$id</span></td><td><a class='project-name' href='$href'>$label</a></td><td class='text-end'><a class='project-open' href='$href'>" . tr("Open") . " <span aria-hidden='true'>&#8594;</span></a></td></tr>";
				$count++;
			}
			if ($count == 0) echo "<tr><td colspan='4'><div class='projects-empty'><span aria-hidden='true'>&#9633;</span><strong>" . tr("No projects found") . "</strong><small>" . tr("Try another description or create a new project.") . "</small></div></td></tr>";
			?>
			</tbody></table></div>
		<div class="projects-list-footer"><span><?php echo $count ?> <?php etr("projects") ?></span><?php newButton("project.php") ?></div>
	</section>
</main>
<?php bottom() ?>
</body>
