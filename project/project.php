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
			        values ($projectid, '$description', customerid)";
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
$title = $row->description;
if ($new)
	$title = tr("Create project");
$title = "<a href='projects.php'>" . tr("Projects") . "</a> > $title";
top("projects", "Project", $title);
?>

<form action="project.php" method="POST">
<table>
<tr>
	<td><?php etr("Id") ?>:</td>
	<td>
	<?php numberBox('projectid', $projectid); ?>
	</td>
</tr>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $row->description ?>"/></td>
<tr>
	<td><?php etr("Customer") ?>:</td>
	<td><?php combobox('customerid', $customers, $row->customerid, true) ?></td>
</table>
<?php
if ($tasks != null) {
	echo "<br/>";
	echo "<div class=border>";
	echo "<table>";
	echo "<th>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Id") . "</th>";
	echo "<th>" . tr("Task") . "</th>";
	echo "<th>" . tr("Pay account") . "</th>";
	echo "<th>" . tr("Product") . "</th>";
	$class = 'odd';
	$i = 0;
	while ($row = fetch($tasks)) {
		hidden("taskid_$i", $row->taskid);
		echo "<tr class=$class>";
		echo "<td align=center>";
		deleteIcon("project.php?projectid=$projectid&del_taskid=$row->taskid");
		echo "</td>";
		echo "<td>$row->taskid</td>";
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
	hidden('count', $i);
	echo "<tr class=$class/>";
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
	echo "</table>";
	echo "</div>";
}
?>
<br/>
<?php saveButton() ?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
