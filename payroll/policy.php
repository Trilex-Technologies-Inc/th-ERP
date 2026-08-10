<?php
	include('include.php');
	include('policy.inc');

	$policyid = getParam('policyid');
	$periodid = getCurrentPeriod();

	if (isDelete()) {
		sql("delete from policy_description where policyid=$policyid");
		sql("delete from policy where policyid=$policyid");
		$policyid = null;
	}

	if (isSave()) {
		$description = getParam('description');
		$glaccountid = prepParam("glaccountid");
		if (isNew()) {
			$sql = "insert into policy ";
			$sql .= "(dimid, glaccountid) ";
			$sql .= "values (1, $glaccountid) ";
			sql($sql);
			$policyid = insert_id();
			sql("insert into policy_description (policyid, language, description)
			     select $policyid, language, '$description'
			     from language");
		} else {
			$sql = "
			update policy 
			set 
				glaccountid=$glaccountid
			where policyid=$policyid";
			sql($sql);
			if ($description != getParam('old_description')) {
				$sql = "
				update policy_description 
				set 
					description='$description'
				where policyid=$policyid and language='" . getLanguage() . "'";
				sql($sql);
				if (affected_rows() == 0) {
					sql("insert into policy_description (policyid, language, description)
					     values ($policyid, '" . getLanguage() . "', '$description')");
				}
	   		}
		}
	}

	$policy = new Dummy();
	if (!isEmpty($policyid)) {
		$sql = "select
		          description,
		          glaccountid
		        from policy_description pd
		        join policy p on p.policyid=pd.policyid
		        where p.policyid=$policyid and language='" . getLanguage() . "'";
		$policy = find($sql, true);
	}
	$accounts = rs2array(query("
	select a.accountid, a.accountid, a.name from account a 
	join account_group ag on ag.accountid=a.accountid and groupid=" . GROUPID_EXPENSES . "
	where a.dimid=1"));
	
?>
<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Policy") ?></title>
<?php styleSheet() ?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
</head>
<body>

<?php
menubar("configuration.php", "policy");
title("<a href='policies.php'>" . tr("Policies") . "</a> > $policy->description")
?>

	<div id="header">
	<?php buildTabs($policyid, 'general') ?>
	</div>
	<div id="main">
		<div id="contents">
<form action="policy.php" method="POST">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Policyid") ?>:</div>
<div class="col-12 col-md-auto">
<?php
	if (!isEmpty($policyid)) {
		echo $policyid;
		echo "<input type='hidden' name='policyid' value='$policyid'/>";
	} else {
		echo "[" . tr("Auto generated") . "]";
		echo "<input type='hidden' name='new' value='1'/>";
	}
?>
</div>

</div><div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php echo tr("Description") ?>:</div>
<div class="col-12 col-md-auto"><input type='text' name='description' value='<?php echo $policy->description ?>'/></div>
</div>
<?php hidden('old_description', $policy->description) ?>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php echo tr("GL Account") ?>:</div>
<div class="col-12 col-md-auto"><?php combobox('glaccountid', $accounts, $policy->glaccountid, true) ?></div>
</div>


<div class="row g-3 align-items-center mb-2">
</div><div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
  <input type="submit" name="save" value="<?php echo tr("Save") ?>"/>
  <input type="submit" name="delete" value="<?php echo tr("Delete") ?>"/>
</div>
</div>
</div>

</form>
		</div>
	</div>
<?php bottom() ?>
</body>
</html>
