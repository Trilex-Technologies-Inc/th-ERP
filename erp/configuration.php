<?php include("include.php") ?>

<head>
<title>thERP - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>
<?php title(tr("Configuration")) ?>

<?php menupage_begin() ?>
<div class="inventory-settings">
    <header class="settings-hero">
        <div class="settings-hero-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.08A1.7 1.7 0 0 0 8.94 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.57 15 1.7 1.7 0 0 0 3 14H3v-4h.08A1.7 1.7 0 0 0 4.6 8.94a1.7 1.7 0 0 0-.34-1.88L4.2 7l2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.57 1.7 1.7 0 0 0 10 3V3h4v.08A1.7 1.7 0 0 0 15.06 4.6a1.7 1.7 0 0 0 1.88-.34L17 4.2 19.83 7l-.06.06A1.7 1.7 0 0 0 19.43 9 1.7 1.7 0 0 0 21 10h.08v4H21a1.7 1.7 0 0 0-1.6 1Z"/></svg>
        </div>
        <div>
            <span class="settings-eyebrow"><?php etr("Inventory setup") ?></span>
            <h1><?php etr("Configuration") ?></h1>
            <p><?php etr("Manage the reference data used across products, purchasing, and stock operations.") ?></p>
        </div>
    </header>

    <div class="settings-section-heading">
        <div>
            <span><?php etr("Reference data") ?></span>
            <h2><?php etr("Product and inventory settings") ?></h2>
        </div>
        <span class="settings-count">5 <?php etr("settings") ?></span>
    </div>

    <nav class="settings-grid" aria-label="<?php etr("Inventory configuration") ?>">
        <a href="categories.php" class="settings-card settings-card-featured">
            <span class="settings-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v6H4zM14 15h6v6h-6z"/></svg></span>
            <span class="settings-card-copy">
                <strong><?php etr("Categories") ?></strong>
                <small><?php etr("Organize products and apply shared defaults") ?></small>
            </span>
            <span class="settings-card-arrow" aria-hidden="true">&#8594;</span>
        </a>

        <a href="vatcategories.php" class="settings-card">
            <span class="settings-card-icon settings-card-icon-green" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 19 14-14M7.5 8.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM16.5 19.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg></span>
            <span class="settings-card-copy">
                <strong><?php etr("VAT categories") ?></strong>
                <small><?php etr("Define tax classifications for inventory") ?></small>
            </span>
            <span class="settings-card-arrow" aria-hidden="true">&#8594;</span>
        </a>

        <a href="locations.php" class="settings-card">
            <span class="settings-card-icon settings-card-icon-orange" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><path d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg></span>
            <span class="settings-card-copy">
                <strong><?php etr("Locations") ?></strong>
                <small><?php etr("Maintain warehouses and storage locations") ?></small>
            </span>
            <span class="settings-card-arrow" aria-hidden="true">&#8594;</span>
        </a>

        <a href="attributes.php" class="settings-card">
            <span class="settings-card-icon settings-card-icon-purple" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h10M18 7h2M4 17h2M10 17h10M14 4v6M6 14v6"/></svg></span>
            <span class="settings-card-copy">
                <strong><?php etr("Attributes") ?></strong>
                <small><?php etr("Configure additional product characteristics") ?></small>
            </span>
            <span class="settings-card-arrow" aria-hidden="true">&#8594;</span>
        </a>

        <a href="unittypes.php" class="settings-card">
            <span class="settings-card-icon settings-card-icon-blue" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 7h18v10H3zM7 7v4M11 7v2M15 7v4M19 7v2"/></svg></span>
            <span class="settings-card-copy">
                <strong><?php etr("Unit types") ?></strong>
                <small><?php etr("Set the units used to measure products") ?></small>
            </span>
            <span class="settings-card-arrow" aria-hidden="true">&#8594;</span>
        </a>
    </nav>
</div>
<?php menupage_end() ?>

</body>
