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

<body onLoad="onLoad()">
<center>
<form name=postform method="POST" action='<?php echo $action ?>'>
<table height='100%'>
<tr height='100%'><td valign=center>

<table cellspacing=0 cellpadding=0>
<tr>
<td width=20 class=login><img src='../images/tl.png'></td>
<td class=login></td>
<td width=20 class=login><img src='../images/tr.png'></td>
</tr>
<tr class=menubar height=20>
<td colspan=3 align=center>
<?php echo $companyname ?>
</td>
</tr>
<tr class=login>
	<td class=login></td>
	<td class=login>
		<table>
		<?php
		if ($mess != null) {
			echo "<tr><td colspan=2 align=center class=error>$mess</td></tr>";
		}
		?>
		<tr>
			<td><?php etr("Username") ?>:</td>
			<td><input type=text name=username></td>
		</tr>
		<tr>
			<td><?php etr("Password") ?>:</td>
			<td><input type=password name=pwd></td>
		</tr>
		<?php
		if (count($dbs) > 1) {
			echo "<tr>";
			echo "<td>" . tr("Database") . ":</td>";
			echo "<td>";
			combobox('dbname', $dbs, null, false);
			echo "</td>";
			echo "</tr>";
		}
		?>
		<tr>
			<td><input type=submit name=login value='<?php etr("Login") ?>'></td>	
		</tr>
		</table>
	</td>
	<td class=login></td>
</tr>
<tr>
<td width=20 class=login><img src='../images/bl.png'></td>
<td class=login></td>
<td width=20 class=login><img src='../images/br.png'></td>
</tr>
</table>

</td></tr></table>
</form>
</center>
</body>
