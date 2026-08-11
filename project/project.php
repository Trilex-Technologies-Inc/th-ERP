<?php
	include('include.php');
	//include('../payroll/include.php');

	$projectid = getParam('projectid');
	$new = true;
	$name = "";
	if (isSave()) {
		$projectid = getParam('projectid');
		$description = getParam('description');
		$customerid = prepParam('customerid');
		if (isNew()) {
			$sql = "insert into project (projectid, description, customerid)
			        values ($projectid, '$description', $customerid)";
			sql($sql);
			//header("Location: projects.php");
			//die;
		} else {
            $updateSQL =
    			"update project set
    			    description='$description',
    			    customerid=$customerid
                where projectid=$projectid";
    		sql($updateSQL);
    		$count = getParam("count");
    		for ($i=0; $i < $count; $i++) {
    			$taskid = getParam("taskid_$i");
				$description = getParam("description_$i");
				$payaccountid = prepParam("payaccountid_$i");
				$productid = prepStringParam("productid_$i");
				sql("
				update task set 
					description='$description',
					payaccountid=$payaccountid,
					productid=$productid
				where projectid=$projectid and taskid=$taskid");
    		}
		}
	}
	$del_taskid = getParam('del_taskid');
	if (!isEmpty($del_taskid)) {
		sql("delete from task where projectid=$projectid and taskid=$del_taskid");
	}
	$taskid_new = getParam('taskid_new');
	if (!isEmpty($taskid_new)) {
		$description_new = getParam('description_new');
		$payaccountid = prepParam('payaccountid_new');
		$productid = prepStringParam('productid_new');
		sql("insert into task (projectid, taskid, description, payaccountid, productid)
		     values ($projectid, $taskid_new, '$description_new', $payaccountid, $productid)");
	}

	$tasks = null;
	$row = new Dummy();
	if (!isEmpty($projectid)) {
	    $selectSQL =
  		"select projectid,
		       description,
		       customerid
		from project
		where projectid=$projectid
		";
		$row = find($selectSQL);
		if ($row != null) {
			$new = false;
			$tasks = query("select taskid, description, payaccountid, productid
			                 from task
							 where projectid=$projectid");
		}
	}

	$payaccounts = rs2array(query(
	"select a.accountid, description
	from payaccount a
	where inputtype in (" . INPUT_TYPE_MINUTES . "," . INPUT_TYPE_DAYS . ")
	"));
	$products = rs2array(query("
	select productid, productid, model from product order by productid"));
	$customers = rs2array(query("
	select customerid, name from customer"));
?>
<?php head("Project") ?>

<body>
<?php
$title = tr("Project");
top("projects", "Project", $title);
?>

<main class="project-editor-page">
	<header class="project-editor-intro">
		<a class="project-editor-back" href="projects.php" aria-label="<?php etr("Projects") ?>">&#8592;</a>
		<div class="projects-intro-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h6l2 2h8v10H4V7Zm0 4h16"/></svg></div>
		<div class="project-editor-heading">
			<span><?php etr("Project management") ?></span>
			<h1><?php echo $new ? tr("Create project") : htmlspecialchars($row->description) ?></h1>
			<p><?php etr("Manage the project customer, tasks, payroll accounts, and linked products.") ?></p>
		</div>
		<?php if (!$new) { ?><span class="project-editor-id">#<?php echo htmlspecialchars($projectid) ?></span><?php } ?>
	</header>

<form action="project.php" method="POST">
<section class="project-editor-overview card border-0 shadow-sm">
	<div class="card-header bg-white project-editor-card-header"><div><span><?php etr("Project details") ?></span><h2><?php etr("General information") ?></h2></div></div>
	<div class="card-body">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-3"><label class="form-label fw-semibold" for="projectid"><?php etr("Id") ?></label></div>
	<div class="col-12 col-md-9">
	<?php numberBox('projectid', $projectid); ?>
	</div>
</div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-3"><label class="form-label fw-semibold" for="project-description"><?php etr("Description") ?></label></div><div class="col-12 col-md-9"><input id="project-description" type="text" name="description" value="<?php echo htmlspecialchars($row->description) ?>"/></div>
</div><div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-3"><label class="form-label fw-semibold" for="customerid"><?php etr("Customer") ?></label></div>
	<div class="col-12 col-md-9"><?php combobox('customerid', $customers, $row->customerid, true) ?></div>
</div></div></div></section>
<?php
if ($tasks != null) {
	echo "<section class='project-tasks card border-0 shadow-sm overflow-hidden'>";
	echo "<div class='card-header bg-white project-editor-card-header'><div><span>" . tr("Project work") . "</span><h2>" . tr("Tasks") . "</h2></div></div>";
	echo "<div class='erp-table-responsive'><table class='erp-data-table project-tasks-table'><thead><tr>";
	echo "<th>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Id") . "</th>";
	echo "<th>" . tr("Task") . "</th>";
	echo "<th>" . tr("Pay account") . "</th>";
	echo "<th>" . tr("Product") . "</th>";
	echo "</tr></thead><tbody>";
	$class = 'odd';
	$i = 0;
	while ($row = fetch($tasks)) {
		echo "<tr class=$class>";
		echo "<td align=center>";
		deleteIcon("project.php?projectid=$projectid&del_taskid=$row->taskid");
		echo "</td>";
		echo "<td>";
		hidden("taskid_$i", $row->taskid);
		echo htmlspecialchars($row->taskid) . "</td>";
		echo "<td>";
		textbox("description_$i", $row->description);
		echo "</td>";
		echo "<td>";
		combobox("payaccountid_$i", $payaccounts, $row->payaccountid, true);
		echo "</td>";
		echo "<td>";
		combobox("productid_$i", $products, $row->productid, true);
		echo "</td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
	}
	echo "<tr class='$class project-task-new'>";
	echo "<td/>";
	echo "<td>";
	numberbox('taskid_new', '');
	echo "</td>";
	echo "<td>";
	textbox('description_new', '');
	echo "</td>";
	echo "<td>";
	combobox('payaccountid_new', $payaccounts, null, true);
	echo "</td>";
	echo "<td>";
	combobox("productid_new", $products, null, true);
	echo "</td>";
	echo "</tr>";
	echo "</tbody></table></div>";
	hidden('count', $i);
	echo "</section>";
}
?>
<div class="project-editor-actions"><?php saveButton() ?></div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>
</body>
