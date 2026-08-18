<?php
	include('include.php');
	include('policy.inc');

	$policyid = getParam('policyid');
	if (!isEmpty($policyid))
		$policyid = requirePolicyId($policyid);
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
			header("Location: policy.php?policyid=" . urlencode($policyid));
			die;
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
	$new = isEmpty($policyid);
	$title = $new ? tr("Create policy") : $policy->description;
	
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
title("<a href='policies.php'>" . tr("Policies") . "</a> > " . htmlspecialchars($title))
?>

	<nav class="policy-tabs" aria-label="<?php echo tr("Policy") ?>">
	<?php buildTabs($policyid, 'general') ?>
	</nav>
	<div id="main">
		<div id="contents">
			<form action="policy.php" method="POST" class="policy-editor">
				<div class="card border-0 shadow-sm">
					<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
						<div>
							<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($title) ?></h2>
							<p class="text-secondary small mb-0"><?php etr("Policy") ?></p>
						</div>
						<span class="badge text-bg-light border"><?php echo $new ? tr("New") : '#' . htmlspecialchars($policyid) ?></span>
					</div>
					<div class="card-body p-4">
						<div class="row g-4">
							<div class="col-12 col-lg-3">
								<label class="form-label fw-semibold"><?php echo tr("Policyid") ?></label>
								<div class="form-control-plaintext">
									<?php
										if (!$new) {
											echo htmlspecialchars($policyid);
											echo "<input type='hidden' name='policyid' value='" . htmlspecialchars($policyid) . "'/>";
										} else {
											echo "[" . tr("Auto generated") . "]";
											echo "<input type='hidden' name='new' value='1'/>";
										}
									?>
								</div>
							</div>
							<div class="col-12 col-lg-9">
								<label class="form-label fw-semibold" for="description"><?php echo tr("Description") ?></label>
								<input class="form-control" id="description" type="text" name="description" value="<?php echo htmlspecialchars($policy->description) ?>" required />
								<?php hidden('old_description', $policy->description) ?>
							</div>
							<div class="col-12 col-lg-6">
								<label class="form-label fw-semibold" for="glaccountid"><?php echo tr("GL Account") ?></label>
								<?php combobox('glaccountid', $accounts, $policy->glaccountid, true) ?>
							</div>
						</div>
					</div>
					<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3">
						<input type="submit" name="save" value="<?php echo tr("Save") ?>"/>
						<?php if (!$new) { ?>
							<button class="btn btn-outline-danger" type="submit" name="delete" value="1" onclick="return thERPConfirmDeleteSubmit(<?php echo htmlspecialchars(json_encode(tr('Are you sure you want to delete this record?')), ENT_QUOTES) ?>, <?php echo htmlspecialchars(json_encode(tr('Record deleted')), ENT_QUOTES) ?>)"><?php echo tr("Delete") ?></button>
						<?php } ?>
						<a class="btn btn-outline-secondary" href="policies.php"><?php etr("Back") ?></a>
					</div>
				</div>
			</form>
		</div>
	</div>
<?php bottom() ?>
</body>
</html>
