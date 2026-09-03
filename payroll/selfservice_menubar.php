<nav class="app-sidebar" aria-label="<?php etr("Module navigation") ?>">
	<?php sidebarHomeLink() ?>
	<div class="app-nav-list">
		<div class="app-nav-item px-2 mb-2"><?php moduleCombo('selfservice') ?></div>
		<div class="app-nav-item"><a class="menubar" href="inout.php" accesskey="1"><?php echo tr("In/Out") ?></a></div>
		<div class="app-nav-item"><a class="menubar" href="calendar.php?employeeid=current" accesskey="1"><?php echo tr("Calendar") ?></a></div>
		<div class="app-nav-item"><a class="menubar" href="debit.php?employeeid=current"><?php echo tr("Debit") ?></a></div>
		<div class="app-nav-item"><a class="menubar" href="selfservice_settings.php"><?php echo tr("Settings") ?></a></div>
		<div class="app-nav-item"><a class="menubar" href="employee_paystub.php?employeeid=current"><?php echo tr("Pay stub") ?></a></div>
		<div class="app-nav-item"><a class="menubar" href="help.php"><?php echo tr("Help") ?></a></div>
	</div>
</nav>
