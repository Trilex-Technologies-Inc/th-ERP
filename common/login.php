<?php
include_once('include.php');

include_once('../sql/upgrade.php');
upgrade();
	
$companyname = findValue("select companyname from companyinfo");
if (isEmpty($companyname))
	$companyname = 'thERP';

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
function togglePassword()
{
	var password = document.getElementById('pwd');
	var toggle = document.getElementById('password-toggle');
	var visible = password.type === 'text';
	password.type = visible ? 'password' : 'text';
	toggle.setAttribute('aria-pressed', visible ? 'false' : 'true');
	toggle.textContent = visible ? <?php echo json_encode(tr("Show")) ?> : <?php echo json_encode(tr("Hide")) ?>;
}
</script>
</head>

<body class="login-page">
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4 py-md-5">
	<div class="card border-0 login-card login-shell w-100 overflow-hidden">
		<div class="row g-0">
			<div class="col-lg-5 login-brand-panel text-white p-4 p-lg-5 d-flex flex-column justify-content-between">
				<div>
					<div class="login-mark mb-4">ERP</div>
					<span class="badge rounded-pill bg-white text-primary mb-3">thERP</span>
					<h1 class="display-6 fw-bold text-white mb-3"><?php echo htmlspecialchars($companyname) ?></h1>
					<p class="lead text-white-50 mb-0"><?php etr("Order/Stock") ?> · <?php etr("Payroll") ?> · <?php etr("Accounting") ?></p>
				</div>
				<p class="small text-white-50 mb-0 d-none d-lg-block">www.therpsoft.com</p>
			</div>

			<div class="col-lg-7 bg-white">
				<div class="card-body p-4 p-md-5">
					<div class="mb-4">
						<span class="text-primary fw-bold small text-uppercase"><?php etr("Welcome") ?></span>
						<h2 class="h3 fw-bold mt-2 mb-2"><?php etr("Login") ?></h2>
						<p class="text-secondary mb-0"><?php etr("Username") ?> / <?php etr("Password") ?></p>
					</div>

					<form name="postform" method="POST" action="<?php echo htmlspecialchars($action) ?>">
						<?php if ($mess != null) { ?>
							<div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
								<span aria-hidden="true">!</span>
								<span><?php echo htmlspecialchars($mess) ?></span>
							</div>
						<?php } ?>

						<div class="mb-3">
							<label class="form-label fw-semibold" for="username"><?php etr("Username") ?></label>
							<input type="text" id="username" name="username" class="form-control form-control-lg" autocomplete="username" autofocus required>
						</div>

						<div class="mb-3">
							<label class="form-label fw-semibold" for="pwd"><?php etr("Password") ?></label>
							<div class="input-group">
								<input type="password" id="pwd" name="pwd" class="form-control form-control-lg" autocomplete="current-password" required>
								<button id="password-toggle" class="btn btn-outline-secondary password-toggle" type="button" onclick="togglePassword()" aria-controls="pwd" aria-pressed="false"><?php etr("Show") ?></button>
							</div>
						</div>

						<?php if (count($dbs) > 1) { ?>
							<div class="mb-3">
								<label class="form-label fw-semibold" for="dbname"><?php etr("Database") ?></label>
								<?php combobox('dbname', $dbs, null, false); ?>
							</div>
						<?php } ?>

						<button type="submit" name="login" class="btn btn-primary btn-lg w-100 mt-2"><?php etr("Login") ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="../include/bootstrap.bundle.min.js"></script>
</body>
</html>
