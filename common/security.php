<?php include("include.php") ?>

<head>
<title>thERP - <?php etr("Security") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("security.php") ?> 

<?php menupage_begin() ?>

<ul>
<li class=menupage>
<a href='users.php' class=menupage>
<?php etr("Users") ?>
</a>
</li>
<li class=menupage>
<a href='usergroups.php' class=menupage>
<?php etr("User groups") ?>
</a>
</li>
<li class=menupage>
<a href='sessions.php' class=menupage>
<?php etr("Sessions") ?>
</a>
</li>
<li class=menupage>
<a href='logger.php' class=menupage>
<?php etr("Logger") ?>
</a>
</li>
</ul>
<?php menupage_end() ?>

</body>
