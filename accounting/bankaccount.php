<?php
	include('include.php');

	$number = getParam('number');
	$new = true;
	$name = "";
	$glaccountid = null;
	if (isSave()) {
		$name = getParam('name');
		$number = getParam('number');
		$glaccountid = getParam('glaccountid');
		if (isNew()) {
			$sql = "insert into bankaccount (name, number, glaccountid)  
			        values ('$name', '$number', $glaccountid)";
			sql($sql);
			$bankaccountid = insert_id();
			header("Location: bankaccounts.php");
			die;
		} else {
            $updateSQL =
    			"update bankaccount set
    			    name='$name',
					glaccountid=$glaccountid
                where number='$number'";
    		sql($updateSQL);
		}
	}

	if (!isEmpty($number)) {
	    $selectSQL =
  		"select name,
		       number,
			   glaccountid
		from bankaccount
		where number='$number'
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$name = $rec->name;
			$glaccountid = $rec->glaccountid;
			$new = false;
		}		
	}
	
	$glaccounts = rs2array(query("select accountid, name 
	                              from account a
								  join account_group g on g.accountid=a.accountid and groupid=" . GROUPID_ASSETS));

?>
<head>
<title>thERP - <?php etr("Bank account") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php
$title = $name;
if ($new)
	$title = tr("Create bankaccount");
title("<a href='bankaccounts.php'>" . tr("Bank accounts") . "</a> > $title") 
?>

<form action="bankaccount.php" method="POST">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Number") ?>:</div>
	<div class="col-12 col-md-auto">
	<input type=text name='number' value='<?php echo $number ?>'/></div>
</div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Name") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="name" value="<?php echo $name ?>"/></div>
</div><div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("General ledger account") ?>:</div>
	<div class="col-12 col-md-auto">
	<?php comboBox("glaccountid", $glaccounts, $glaccountid, false)?>
	</div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
<?php saveButton() ?>
&nbsp;
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
