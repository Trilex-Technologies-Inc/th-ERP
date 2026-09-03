<?php include("include.php") ?>

<head>
<title>thERP - <?php etr("Accounting") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php
menubar('index.php', 'index_help.php');
title(tr("Accounting"));
menupage_begin();
?>
<div class="accounting-hub">
    <header class="accounting-hero">
        <div class="accounting-hero-copy">
            <span class="accounting-eyebrow"><?php etr("General ledger") ?></span>
            <h1><?php etr("Accounting") ?></h1>
            <p><?php etr("Record financial activity and keep your general ledger accurate and up to date.") ?></p>
        </div>
        <div class="accounting-hero-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 3h16v18H4zM8 7h8M8 11h2m4 0h2M8 15h2m4 0h2M8 19h2m4 0h2"/></svg>
        </div>
    </header>

    <section class="accounting-section" aria-labelledby="accounting-actions-title">
        <div class="accounting-section-heading">
            <span class="accounting-section-kicker"><?php etr("Quick actions") ?></span>
            <h2 id="accounting-actions-title"><?php etr("Register a transaction") ?></h2>
            <p><?php etr("Choose the transaction type that matches the activity you want to record.") ?></p>
        </div>

        <div class="accounting-grid">
            <a href="register_transaction.php" class="accounting-card">
                <span class="accounting-card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 5h16v14H4zM8 9h8M8 13h3M8 17h5"/></svg>
                </span>
                <span class="accounting-card-copy">
                    <strong><?php etr("Generic transaction") ?></strong>
                    <small><?php etr("Create a journal entry with debit and credit lines.") ?></small>
                </span>
                <span class="accounting-card-arrow" aria-hidden="true">&#8594;</span>
            </a>

            <a href="expense_trans.php" class="accounting-card accounting-card-expense">
                <span class="accounting-card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 7h16v12H4zM4 10h16M8 15h4M17 4v6"/></svg>
                </span>
                <span class="accounting-card-copy">
                    <strong><?php etr("Expense transaction") ?></strong>
                    <small><?php etr("Record an expense and its related payment details.") ?></small>
                </span>
                <span class="accounting-card-arrow" aria-hidden="true">&#8594;</span>
            </a>
        </div>
    </section>
</div>
<?php menupage_end() ?>
</body>
