<?php include("include.php") ?>

<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php", "config") ?>
<?php menupage_begin() ?>
<table>
<tr>
<td>
<ul>
<li class=menupage><a href="policies.php"><?php echo tr("Policies") ?></a></li>
<li class=menupage><a href="payaccounts.php"><?php echo tr("Pay accounts") ?></a></li>
<li class=menupage><a href="payaccountgroups.php"><?php echo tr("Pay account groups") ?></a></li>
<li class=menupage><a href="schedules.php"><?php echo tr("Schedules") ?></a></li>
<li class=menupage><a href="attributes.php"><?php echo tr("Attributes") ?></a></li>
<li class=menupage><a href="formulas.php"><?php echo tr("Formulas") ?></a></li>
<li class=menupage><a href="teams.php"><?php echo tr("Teams") ?></a></li>
</ul>
</td>
<td>
<ul>
<li class=menupage><a href="daily_forms.php"><?php echo tr("Daily forms") ?></a></li>
<li class=menupage><a href="tabs.php"><?php echo tr("Tabs") ?></a></li>
<li class=menupage><a href="travelconf.php"><?php echo tr("Travel configuration") ?></a></li>
<li class=menupage><a href="advanced_percents.php"><?php echo tr("Advanced percent") ?></a></li>
<li class=menupage><a href="rangesets.php"><?php echo tr("Range sets") ?></a></li>
<li class=menupage><a href="se_taxtable.php"><?php echo tr("Swedish taxtables") ?></a></li>
<li class=menupage><a href="setup.php"><?php echo tr("Setup") ?></a></li>
</ul>
</td>
</tr>
</table>
<?php menupage_end() ?>

</body>
