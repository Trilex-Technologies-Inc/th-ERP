<?php
	define('public', 1);
	include('include.php');

    $model = getParam('model');
    $locationid = getParam('locationid');

	$locationSQL = '';
	if (!isEmpty($locationid))
		$locationSQL = " and locationid=$locationid ";
	$selectSQL = "
	select
	    p.productid,
	    model,
	    (select sum(diff) from stockmove m where m.productid=p.productid $locationSQL) as quantity,
	    incvat.price as price_incvat,
	    exvat.price as price_exvat 
	from product p 
	left outer join sales_price incvat on incvat.productid=p.productid and incvat.listid=2
	left outer join sales_price exvat on exvat.productid=p.productid and exvat.listid=1
	where model like '$model%'
	and active=1
	";
	$orderid = getParam('orderid');

	$locations = rs2array(query("select locationid, name from location"));
	$caption_exvat = findValue("select description from pricelist where listid=1");
	$caption_incvat = findValue("select description from pricelist where listid=2");

?>

<?php head("Products") ?>
<body>

<?php menubar('index.php') ?>
<?php title(tr("Price list")) ?>

<form action="pricelist.php" method="GET" name=searchform>
<div class="border">
<table>
<tr>
	<td><?php etr("Model") ?>:</td><td><?php textbox('model', $model) ?></td><td width=20/>
	<td><?php etr("Location") ?>:</td><td><?php combobox('locationid', $locations, $locationid, true) ?></td><td width=20/>
	<td>
	<?php searchButton() ?>
	</td>
</tr>
</table>
</div>
</form>

<form action="pricelist.php" method=POST>
<input type=hidden name=orderid value='<?php echo $orderid ?>'/>
<table width='100%'>
<th><?php etr("Productno") ?></th>
<th width='50%'><?php etr("Product") ?></th>
<?php
echo "<th>$caption_incvat</th>";
echo "<th>$caption_exvat</th>";
echo "<th>" . tr("Quantity") . "</th>";

$rs = query($selectSQL);
$class = "odd";
while ($row = fetch_object($rs)) {
	echo "<tr class='$class'>";
	echo "<td>$row->productid</td>";
	echo "<td>$row->model</td>";
	echo "<td align=right>". formatMoney($row->price_incvat) . "</td>";
	echo "<td align=right>". formatMoney($row->price_exvat) . "</td>";
	echo "<td align=right>$row->quantity</td>";
	echo "</tr>";
	$class = ($class == "odd" ? "even" : "odd");
}
?>
</table>
<br/>
</form>
<?php bottom() ?>
</body>
