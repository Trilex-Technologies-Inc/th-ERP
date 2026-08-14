<?php include("include.php") ?>

<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php", "config") ?>
<?php title(tr("Configuration")) ?>
<?php menupage_begin() ?>
<div class="payroll-settings">
<header class="payroll-settings-hero">
<div class="payroll-settings-mark" aria-hidden="true">
<svg viewBox="0 0 24 24"><path d="M7 8h10M7 12h6M7 16h4M6 3h12a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2-3-2-3 2V5a2 2 0 0 1 2-2Z"/></svg>
</div>
<div>
<span class="payroll-settings-eyebrow"><?php etr("Payroll setup") ?></span>
<h1><?php etr("Configuration") ?></h1>
<p><?php etr("Manage payroll policies, accounts, schedules, and employee reference data.") ?></p>
</div>
</header>

<div class="payroll-settings-heading">
<div>
<span><?php etr("Core settings") ?></span>
<h2><?php etr("Payroll rules") ?></h2>
</div>
<span class="payroll-settings-count">14 <?php etr("settings") ?></span>
</div>

<nav class="payroll-settings-grid" aria-label="<?php etr("Payroll configuration") ?>">
<a class="payroll-settings-card payroll-settings-card-featured" href="policies.php"><span class="payroll-settings-card-icon"><svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-5"/><path d="M20 6 9 17l-5-5"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Policies") ?></strong><small><?php etr("Define payroll policy rules and approvals.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="payaccounts.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-green"><svg viewBox="0 0 24 24"><path d="M3 10h18"/><path d="M5 6h14a2 2 0 0 1 2 2v10H3V8a2 2 0 0 1 2-2Z"/><path d="M7 14h3"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Pay accounts") ?></strong><small><?php etr("Maintain wage, benefit, and deduction accounts.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="payaccountgroups.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-green"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/><path d="M8 5v14M16 5v14"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Pay account groups") ?></strong><small><?php etr("Group accounts for reports and calculations.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="schedules.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-orange"><svg viewBox="0 0 24 24"><path d="M8 2v4M16 2v4"/><path d="M3 9h18"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 14h.01M12 14h.01M16 14h.01"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Schedules") ?></strong><small><?php etr("Set recurring payroll periods and timing.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="attributes.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-blue"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h10M4 17h7"/><path d="M18 14v6M15 17h6"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Attributes") ?></strong><small><?php etr("Configure employee payroll fields.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="formulas.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-purple"><svg viewBox="0 0 24 24"><path d="M4 5h16"/><path d="M8 5c4 5 4 9 0 14"/><path d="M16 5c-4 5-4 9 0 14"/><path d="M4 19h16"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Formulas") ?></strong><small><?php etr("Build reusable calculation formulas.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="teams.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-blue"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Teams") ?></strong><small><?php etr("Organize employees for payroll workflows.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="daily_forms.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-orange"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Daily forms") ?></strong><small><?php etr("Manage daily payroll entry forms.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="tabs.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-blue"><svg viewBox="0 0 24 24"><path d="M3 6h7l2 3h9v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><path d="M3 10h18"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Tabs") ?></strong><small><?php etr("Control payroll page sections and labels.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="travelconf.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-purple"><svg viewBox="0 0 24 24"><path d="M22 16.92V21a1 1 0 0 1-1.2.98L12 20l-8.8 1.98A1 1 0 0 1 2 21v-4.08a1 1 0 0 1 .55-.9L12 11l9.45 5.02a1 1 0 0 1 .55.9Z"/><path d="M12 11V3"/><path d="M8 7l4-4 4 4"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Travel configuration") ?></strong><small><?php etr("Set travel compensation and allowance rules.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="advanced_percents.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-green"><svg viewBox="0 0 24 24"><path d="M19 5 5 19"/><circle cx="7" cy="7" r="2"/><circle cx="17" cy="17" r="2"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Advanced percent") ?></strong><small><?php etr("Tune percentage-based payroll calculations.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="rangesets.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-purple"><svg viewBox="0 0 24 24"><path d="M4 18V6"/><path d="M20 18V6"/><path d="M8 12h8"/><path d="m13 9 3 3-3 3"/><path d="m11 9-3 3 3 3"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Range sets") ?></strong><small><?php etr("Maintain thresholds and stepped ranges.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="se_taxtable.php"><span class="payroll-settings-card-icon payroll-settings-card-icon-orange"><svg viewBox="0 0 24 24"><path d="M4 4h16v16H4Z"/><path d="M4 9h16M9 4v16"/><path d="M13 13h4M13 17h4"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Swedish taxtables") ?></strong><small><?php etr("Update tax tables for Swedish payroll.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
<a class="payroll-settings-card" href="setup.php"><span class="payroll-settings-card-icon"><svg viewBox="0 0 24 24"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.33 1.82 2 2 0 0 1-3.34 0A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.82-.33 2 2 0 0 1 0-3.34A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-.6 1.65 1.65 0 0 0 .33-1.82 2 2 0 0 1 3.34 0A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 .6 1 1.65 1.65 0 0 0 1.82.33 2 2 0 0 1 0 3.34A1.65 1.65 0 0 0 19.4 15Z"/></svg></span><span class="payroll-settings-card-copy"><strong><?php etr("Setup") ?></strong><small><?php etr("Review module-wide payroll defaults.") ?></small></span><span class="payroll-settings-card-arrow" aria-hidden="true">&rsaquo;</span></a>
</nav>
</div>
<?php menupage_end() ?>

</body>
