<?php
include_once('include.php');

include_once('../sql/upgrade.php');
upgrade();
	
$companyname = findValue("select companyname from companyinfo");

$action = 'index.php';
if (isset($_SESSION['ORG_SCRIPT_NAME']))
	$action = $_SESSION['ORG_SCRIPT_NAME'];

$mess = null;
if (isset($_REQUEST['login_mess']))
	$mess = $_REQUEST['login_mess'];
	
$dbs = array();
$i = 1;
while (defined("DBNAME_$i")) {
	$dbs[] = array(constant("DBNAME_$i"));
	$i++;
}

?>
<head>
<title>thERP - <?php etr("Login") ?></title>
<?php styleSheet() ?>
<script>
function onLoad()
{
	document.postform.username.focus();
}
</script>
</head>

<body onLoad="onLoad()" class="bg-light">
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 shadow-sm login-card w-100" style="max-width: 440px;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="login-mark mx-auto mb-3">ERP</div>
                <h1 class="h4 mb-1"><?php echo $companyname ?></h1>
                <p class="text-secondary mb-0"><?php etr("Login") ?></p>
            </div>

            <form name="postform" method="POST" action="<?php echo $action ?>">
                <?php if ($mess != null) { ?>
                    <div class="alert alert-danger py-2" role="alert"><?php echo $mess ?></div>
                <?php } ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><?php etr("Username") ?></label>
                    <input type="text" name="username" class="form-control" autocomplete="username">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><?php etr("Password") ?></label>
                    <input type="password" name="pwd" class="form-control" autocomplete="current-password">
                </div>

                <?php if (count($dbs) > 1) { ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold"><?php etr("Database") ?></label>
                        <?php combobox('dbname', $dbs, null, false); ?>
                    </div>
                <?php } ?>

                <button type="submit" name="login" class="btn btn-primary w-100"><?php etr("Login") ?></button>
            </form>
        </div>
    </div>
</div>
<script src="../include/bootstrap.bundle.min.js"></script>
