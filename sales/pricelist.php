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

	<form action="pricelist.php" method="GET" name="searchform">
		<div class="border p-3 mb-4">
			<div class="row g-3 align-items-end">
				<div class="col-md-4">
					<label class="form-label"><?php etr("Model") ?></label>
					<input type="text" name="model" value="<?php echo htmlspecialchars($model) ?>" class="form-control" />
				</div>
				<div class="col-md-4">
					<label class="form-label"><?php etr("Location") ?></label>
					<select name="locationid" class="form-select">
						<option></option>
						<?php foreach ($locations as $option) {
							if (count($option) > 2) {
								$label = $option[1] . ' - ' . $option[2];
							} else if (count($option) > 1) {
								$label = $option[1];
							} else {
								$label = $option[0];
							}
							$selected = $option[0] == $locationid ? ' selected' : '';
							echo "<option value='" . $option[0] . "'" . $selected . ">" . htmlspecialchars($label) . "</option>\n";
						} ?>
					</select>
				</div>
				<div class="col-auto">
					<?php searchButton() ?>
				</div>
			</div>
		</div>
	</form>

	<div class="table-responsive">
		<table class="table table-sm table-striped table-hover align-middle">
			<thead>
				<tr>
					<th><?php etr("Productno") ?></th>
					<th><?php etr("Product") ?></th>
					<th><?php echo htmlspecialchars($caption_incvat) ?></th>
					<th><?php echo htmlspecialchars($caption_exvat) ?></th>
					<th class="text-end"><?php etr("Quantity") ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$rs = query($selectSQL);
				$class = "odd";
				while ($row = fetch_object($rs)) {
					echo "<tr class='$class'>";
					echo "<td>" . htmlspecialchars($row->productid) . "</td>";
					echo "<td>" . htmlspecialchars($row->model) . "</td>";
					echo "<td class='text-end'>" . formatMoney($row->price_incvat) . "</td>";
					echo "<td class='text-end'>" . formatMoney($row->price_exvat) . "</td>";
					echo "<td class='text-end'>" . htmlspecialchars($row->quantity) . "</td>";
					echo "</tr>";
					$class = ($class == "odd" ? "even" : "odd");
				}
				?>
			</tbody>
		</table>
	</div>
	<br />
	<?php bottom() ?>
</body>