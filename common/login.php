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
	<div class="card border-0 login-card login-split w-100 overflow-hidden">
		<div class="row g-0">
			<aside class="col-lg-5 login-brand-panel p-4 p-lg-5 text-white">
				<div class="login-mark mb-4">ERP</div>
				<span class="login-product-label">thERP</span>
				<h1 class="h2 fw-bold text-white mt-3 mb-2"><?php echo htmlspecialchars($companyname) ?></h1>
				<p class="text-white-50 mb-4"><?php etr("Business management") ?></p>
				<div class="login-feature-list">
					<span><?php etr("Order/Stock") ?></span>
					<span><?php etr("Payroll") ?></span>
					<span><?php etr("Accounting") ?></span>
				</div>
				<p class="login-brand-footer">Copyright THERP 2008 - <?php echo date('Y') ?> <a href="https://th-erp.com" class="text-white-50">th-erp.com</a> GPLv2</p>
			</aside>

			<section class="col-lg-7 bg-white">
				<div class="card-body p-4 p-md-5 login-form-panel">
					<div class="mb-4">
						<span class="module-eyebrow"><?php etr("Welcome") ?></span>
						<h2 class="h3 fw-bold mt-2 mb-2"><?php etr("Login") ?></h2>
						<p class="text-secondary mb-0"><?php etr("Enter your account details") ?></p>
					</div>

					<form name="postform" method="POST" action="<?php echo htmlspecialchars($action) ?>">
						<?php if ($mess != null) { ?>
							<div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($mess) ?></div>
						<?php } ?>

						<div class="mb-3">
							<label class="form-label fw-semibold" for="username"><?php etr("Username") ?></label>
							<input type="text" id="username" name="username" class="form-control" autocomplete="username" autofocus>
						</div>

						<div class="mb-3">
							<label class="form-label fw-semibold" for="pwd"><?php etr("Password") ?></label>
							<div class="password-field">
								<input type="password" id="pwd" name="pwd" class="form-control" autocomplete="current-password">
								<button id="password-toggle" class="password-toggle" type="button" onclick="togglePassword()" aria-controls="pwd" aria-pressed="false"><?php etr("Show") ?></button>
							</div>
						</div>

						<?php if (count($dbs) > 1) { ?>
							<div class="mb-3">
								<label class="form-label fw-semibold" for="dbname"><?php etr("Database") ?></label>
								<?php combobox('dbname', $dbs, null, false); ?>
							</div>
						<?php } ?>

						<button type="submit" name="login" class="btn btn-primary w-100 mt-2"><?php etr("Login") ?></button>
					</form>
					<p class="text-center text-secondary small mt-4 mb-0"><?php etr("Secure access") ?></p>
				</div>
			</section>
		</div>
	</div>
</div>
<script src="../include/bootstrap.bundle.min.js"></script>
</body>
</html>
