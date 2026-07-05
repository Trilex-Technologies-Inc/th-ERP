<?php include('include.php') ?>
<head>
<title>thERP - <?php etr("Help - Sales order") ?></title>
<?php styleSheet() ?>
</head>
<body>
<?php menubar('salesorder_help.php', 'salesorder_help.php') ?>

<h1>Editing a Sales Order</h1>

<p class="help">
The sales order form allows a user to edit a sales order for a particular customer. <br>
An order is identifiable by a unique number, which is Order id.
<br>
</p>
<h2>To add a product to an order</h2>

<ol>
<li>To add a product to a customer order, you must first search for it by pressing the <b>Search</b> button in the
Product column.</li>
<li>The Products screen will open.
<ul><li>If you know the model of the product you wish to add, you may enter it into the Model field, and then press the <b>Search</b> button. </li>
<li>If you wish to select a product from the list
available in the Product column, click on a product name. You will be taken back
to the Sales Order screen.</li>
</ul>
</li>

<li>
You will notice that the
Product, Quantity, Unit price, and Purchase price fields are now populated
with the respective
properties of the product you have selected. </li>

<li>
Press the <b>Add</b> button to add the product you have selected to the customer
order. The VAT/GST is added to the product price to produce the final payable
amount, displayed in the To Pay field.
<ul><li>If you want to see how this order has affected
the current stock, press the <b>Stock moves</b> button.</li></ul>
</ol>

</p>

<h2>To delete a product from an order</h2>

<p class="help">
To delete a product, click on the red
"X" symbol in the Delete column. </p>

<h2>
To create a receipt for a sales order</h2>
<ol>
<li> Press the <b>Invoice</b> button. The sales order will now be marked as invoiced. 
The order may not longer be edited.</li>

<li>
Press the<b> Receipt</b> button. Alternatively, click the Print link to print the Invoice.</li>
</ol>

<h2>
To credit an order</h2>


<p class="help">
This function is used when a customer wishes to return a product for a refund.

</p>


<ol>
<li> Press the <b>Invoice</b> button. You will be taken to the Invoice screen. </li>

<li>
Press the<b> Credit order</b> button. </li>
<ul><li>If you want to see how the credit has affected the current
stock, press the <B>Stock moves</B> button.
</ol>


<h2>
To cancel an order</h2>


<p class="help">

To cancel an order at any time, press the <b>Cancel Order</b> button. If you want to see how the
cancellation of the order has affected
the current stock, press the <b>Stock moves</b> button.

<p>&nbsp;</p>

</body></html>
