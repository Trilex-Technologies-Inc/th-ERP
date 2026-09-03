<?php 
include("include.php");

if (getParam("setup") == "bas") {
	tx("runScript", array("../sql/bas.sql"));
}

?>

<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Setup") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Setup")) ?>

<main class="accounting-config-hub">
    <div class="accounting-section-heading">
        <span class="accounting-section-kicker"><?php etr("Accounting configuration") ?></span>
        <h1><?php etr("Setup") ?></h1>
        <p><?php etr("Load a prepared chart of accounts to get started quickly.") ?></p>
    </div>
    <a class="accounting-config-card accounting-config-card-accent" href="setup.php?setup=bas">
        <strong><?php etr("Load BAS accounts (Sweden)") ?></strong>
        <small><?php etr("Import the Swedish BAS chart of accounts") ?></small>
        <span aria-hidden="true">&#8594;</span>
    </a>
</main>
</body>
