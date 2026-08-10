<?php include("include.php") ?>

<head>
<title>thERP - <?php etr("Transactions") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('purchase.php') ?>

<?php menupage_begin() ?>
<div class="purchase-hub">
    <header class="purchase-hero">
        <div>
            <span class="purchase-eyebrow"><?php etr("Purchasing") ?></span>
            <h1><?php etr("Purchase management") ?></h1>
            <p><?php etr("Create purchase documents and keep track of suppliers and outstanding balances.") ?></p>
        </div>
        <div class="purchase-hero-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" role="img"><path d="M3 3h2l2.2 10.1a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.5L20.5 7H6.3M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
        </div>
    </header>

    <section class="purchase-section" aria-labelledby="purchase-actions-title">
        <div class="purchase-section-heading">
            <div>
                <span class="purchase-section-kicker"><?php etr("Quick actions") ?></span>
                <h2 id="purchase-actions-title"><?php etr("Create new") ?></h2>
            </div>
        </div>
        <div class="purchase-grid purchase-grid-actions">
            <a href="suppliers.php?mode=createorder" class="purchase-card purchase-card-primary">
                <span class="purchase-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></span>
                <span class="purchase-card-copy">
                    <strong><?php etr("New purchase order") ?></strong>
                    <small><?php etr("Start an order for a supplier") ?></small>
                </span>
                <span class="purchase-card-arrow" aria-hidden="true">&#8594;</span>
            </a>
            <a href="suppliers.php?mode=payable" class="purchase-card purchase-card-primary">
                <span class="purchase-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h16v12H4zM4 10h16M8 15h3"/></svg></span>
                <span class="purchase-card-copy">
                    <strong><?php etr("New payable") ?></strong>
                    <small><?php etr("Record a supplier liability") ?></small>
                </span>
                <span class="purchase-card-arrow" aria-hidden="true">&#8594;</span>
            </a>
        </div>
    </section>

    <section class="purchase-section" aria-labelledby="purchase-overview-title">
        <div class="purchase-section-heading">
            <div>
                <span class="purchase-section-kicker"><?php etr("Workspace") ?></span>
                <h2 id="purchase-overview-title"><?php etr("Purchasing overview") ?></h2>
            </div>
        </div>
        <div class="purchase-grid">
            <a href="purchaseorders.php" class="purchase-card">
                <span class="purchase-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h10v4h3v14H4V7h3V3Zm0 8h10M7 15h7"/></svg></span>
                <span class="purchase-card-copy"><strong><?php etr("Purchase orders") ?></strong><small><?php etr("Review and manage orders") ?></small></span>
                <span class="purchase-card-arrow" aria-hidden="true">&#8594;</span>
            </a>
            <a href="payables.php" class="purchase-card">
                <span class="purchase-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 3h14v18l-3-2-4 2-4-2-3 2V3Zm4 5h6M9 12h6"/></svg></span>
                <span class="purchase-card-copy"><strong><?php etr("Payables") ?></strong><small><?php etr("View supplier liabilities") ?></small></span>
                <span class="purchase-card-arrow" aria-hidden="true">&#8594;</span>
            </a>
            <a href="suppliers.php" class="purchase-card">
                <span class="purchase-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 8h4M19 6v4"/></svg></span>
                <span class="purchase-card-copy"><strong><?php etr("Suppliers") ?></strong><small><?php etr("Manage supplier records") ?></small></span>
                <span class="purchase-card-arrow" aria-hidden="true">&#8594;</span>
            </a>
            <a href="supplier_balance.php" class="purchase-card">
                <span class="purchase-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg></span>
                <span class="purchase-card-copy"><strong><?php etr("Supplier balance") ?></strong><small><?php etr("Check balances by supplier") ?></small></span>
                <span class="purchase-card-arrow" aria-hidden="true">&#8594;</span>
            </a>
        </div>
    </section>
</div>
<?php menupage_end() ?>

</body>
