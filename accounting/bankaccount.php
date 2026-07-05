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
<table>
<tr>
	<td><?php etr("Number") ?>:</td>
	<td>
	<input type=text name='number' value='<?php echo $number ?>'/></td>
</tr>
<tr><td><?php etr("Name") ?>:</td><td><input type="text" name="name" value="<?php echo $name ?>"/></td>
<tr>
	<td><?php etr("General ledger account") ?>:</td>
	<td>
	<?php comboBox("glaccountid", $glaccounts, $glaccountid, false)?>
	</td>
</tr>
<tr>
<td colspan=2>
<?php saveButton() ?>
&nbsp;
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
