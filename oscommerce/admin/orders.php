<?php
/*
  $Id: orders.php,v 1.112 2003/06/29 22:50:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "'");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update_order':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);
        $status = tep_db_prepare_input($HTTP_POST_VARS['status']);
        $comments = tep_db_prepare_input($HTTP_POST_VARS['comments']);

        $order_updated = false;
        $check_status_query = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
        $check_status = tep_db_fetch_array($check_status_query);

        if ( ($check_status['orders_status'] != $status) || tep_not_null($comments)) {
          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int)$oID . "'");

          $customer_notified = '0';
          if (isset($HTTP_POST_VARS['notify']) && ($HTTP_POST_VARS['notify'] == 'on')) {
            $notify_comments = '';
            if (isset($HTTP_POST_VARS['notify_comments']) && ($HTTP_POST_VARS['notify_comments'] == 'on')) {
              $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
            }

            $email = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' . tep_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) . "\n\n" . $notify_comments . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);

            tep_mail($check_status['customers_name'], $check_status['customers_email_address'], EMAIL_TEXT_SUBJECT, $email, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            $customer_notified = '1';
          }

          tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$oID . "', '" . tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" . tep_db_input($comments)  . "')");

          if ($status == 3) {
	          include('includes/therp.inc.php');
	          create_salesorder($oID);
          }
          
          $order_updated = true;
        }

        if ($order_updated == true) {
         $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        } else {
          $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        tep_remove_order($oID, $HTTP_POST_VARS['restock']);

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action'))));
        break;
    }
  }

  if (($action == 'edit') && isset($HTTP_GET_VARS['oID'])) {
    $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
    $order_exists = true;
    if (!tep_db_num_rows($orders_query)) {
      $order_exists = false;
      $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
    }
  }

  include(DIR_WS_CLASSES . 'order.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php
  require(DIR_WS_INCLUDES . 'header.php');
?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid px-0 erp-form-layout">
  <div class="row g-3 align-items-center mb-2">
    <div class="col-12 col-md-auto">" valign="top"><div class="container-fluid px-0 erp-form-layout">" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </div></div>
<!-- body_text //-->
    <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
<?php
  if (($action == 'edit') && ($order_exists == true)) {
    $order = new order($oID);
?>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo HEADING_TITLE; ?></div>
            <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></div>
            <div class="col-12 col-md-auto"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></div>
          </div>
        </div></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo tep_draw_separator(); ?></div>
          </div>
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
              <div class="row g-3 align-items-center mb-2">
                <div class="col-12 col-md-auto"><b><?php echo ENTRY_CUSTOMER; ?></b></div>
                <div class="col-12 col-md-auto"><?php echo tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?></div>
              </div>
              <div class="row g-3 align-items-center mb-2">
                <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></div>
              </div>
              <div class="row g-3 align-items-center mb-2">
                <div class="col-12 col-md-auto"><b><?php echo ENTRY_TELEPHONE_NUMBER; ?></b></div>
                <div class="col-12 col-md-auto"><?php echo $order->customer['telephone']; ?></div>
              </div>
              <div class="row g-3 align-items-center mb-2">
                <div class="col-12 col-md-auto"><b><?php echo ENTRY_EMAIL_ADDRESS; ?></b></div>
                <div class="col-12 col-md-auto"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></div>
              </div>
            </div></div>
            <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
              <div class="row g-3 align-items-center mb-2">
                <div class="col-12 col-md-auto"><b><?php echo ENTRY_SHIPPING_ADDRESS; ?></b></div>
                <div class="col-12 col-md-auto"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></div>
              </div>
            </div></div>
            <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
              <div class="row g-3 align-items-center mb-2">
                <div class="col-12 col-md-auto"><b><?php echo ENTRY_BILLING_ADDRESS; ?></b></div>
                <div class="col-12 col-md-auto"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?></div>
              </div>
            </div></div>
          </div>
        </div></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b></div>
            <div class="col-12 col-md-auto"><?php echo $order->info['payment_method']; ?></div>
          </div>
<?php
    if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
?>
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
          </div>
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></div>
            <div class="col-12 col-md-auto"><?php echo $order->info['cc_type']; ?></div>
          </div>
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></div>
            <div class="col-12 col-md-auto"><?php echo $order->info['cc_owner']; ?></div>
          </div>
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></div>
            <div class="col-12 col-md-auto"><?php echo $order->info['cc_number']; ?></div>
          </div>
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></div>
            <div class="col-12 col-md-auto"><?php echo $order->info['cc_expires']; ?></div>
          </div>
<?php
    }
?>
        </div></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo TABLE_HEADING_PRODUCTS; ?></div>
            <div class="col-12 col-md-auto"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></div>
            <div class="col-12 col-md-auto"><?php echo TABLE_HEADING_TAX; ?></div>
            <div class="col-12 col-md-auto"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></div>
            <div class="col-12 col-md-auto"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></div>
            <div class="col-12 col-md-auto"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></div>
            <div class="col-12 col-md-auto"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></div>
          </div>
<?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      echo '          <div class="row g-3 align-items-center mb-2">' . "\n" .
           '            <div class="col-12 col-md-auto">' . $order->products[$i]['qty'] . '&nbsp;x</div>' . "\n" .
           '            <div class="col-12 col-md-auto">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
          echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          echo '</i></small></nobr>';
        }
      }

      echo '            </div>' . "\n" .
           '            <div class="col-12 col-md-auto">' . $order->products[$i]['model'] . '</div>' . "\n" .
           '            <div class="col-12 col-md-auto">' . tep_display_tax_value($order->products[$i]['tax']) . '%</div>' . "\n" .
           '            <div class="col-12 col-md-auto"><b>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</b></div>' . "\n" .
           '            <div class="col-12 col-md-auto"><b>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true), true, $order->info['currency'], $order->info['currency_value']) . '</b></div>' . "\n" .
           '            <div class="col-12 col-md-auto"><b>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></div>' . "\n" .
           '            <div class="col-12 col-md-auto"><b>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></div>' . "\n";
      echo '          </div>' . "\n";
    }
?>
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <div class="row g-3 align-items-center mb-2">' . "\n" .
           '                <div class="col-12 col-md-auto">' . $order->totals[$i]['title'] . '</div>' . "\n" .
           '                <div class="col-12 col-md-auto">' . $order->totals[$i]['text'] . '</div>' . "\n" .
           '              </div>' . "\n";
    }
?>
            </div></div>
          </div>
        </div></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><table border="1" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_DATE_ADDED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_STATUS; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
          </tr>
<?php
    $orders_history_query = tep_db_query("select orders_status_id, date_added, customer_notified, comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
    if (tep_db_num_rows($orders_history_query)) {
      while ($orders_history = tep_db_fetch_array($orders_history_query)) {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" align="center">' . tep_datetime_short($orders_history['date_added']) . '</td>' . "\n" .
             '            <td class="smallText" align="center">';
        if ($orders_history['customer_notified'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK) . "</td>\n";
        } else {
          echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS) . "</td>\n";
        }
        echo '            <td class="smallText">' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' . "\n" .
             '            <td class="smallText">' . nl2br(tep_db_output($orders_history['comments'])) . '&nbsp;</td>' . "\n" .
             '          </tr>' . "\n";
      }
    } else {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '          </tr>' . "\n";
    }
?>
        </table></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><br><b><?php echo TABLE_HEADING_COMMENTS; ?></b></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></div>
      </div>
      <div class="row g-3 align-items-center mb-2"><?php echo tep_draw_form('status', FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=update_order'); ?>
        <div class="col-12 col-md-auto"><?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
              <div class="row g-3 align-items-center mb-2">
                <div class="col-12 col-md-auto"><b><?php echo ENTRY_STATUS; ?></b> <?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></div>
              </div>
              <div class="row g-3 align-items-center mb-2">
                <div class="col-12 col-md-auto"><b><?php echo ENTRY_NOTIFY_CUSTOMER; ?></b> <?php echo tep_draw_checkbox_field('notify', '', true); ?></div>
                <div class="col-12 col-md-auto"><b><?php echo ENTRY_NOTIFY_COMMENTS; ?></b> <?php echo tep_draw_checkbox_field('notify_comments', '', true); ?></div>
              </div>
            </div></div>
            <div class="col-12 col-md-auto"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></div>
          </div>
        </div></div>
      </form></div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $HTTP_GET_VARS['oID']) . '" TARGET="_blank">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $HTTP_GET_VARS['oID']) . '" TARGET="_blank">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></div>
      </div>
<?php
  } else {
?>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><?php echo HEADING_TITLE; ?></div>
            <div class="col-12 col-md-auto"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></div>
            <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
              <div class="row g-3 align-items-center mb-2"><?php echo tep_draw_form('orders', FILENAME_ORDERS, '', 'get'); ?>
                <div class="col-12 col-md-auto"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('oID', '', 'size="12"') . tep_draw_hidden_field('action', 'edit'); ?></div>
              <?php echo tep_hide_session_id(); ?></form></div>
              <div class="row g-3 align-items-center mb-2"><?php echo tep_draw_form('status', FILENAME_ORDERS, '', 'get'); ?>
                <div class="col-12 col-md-auto"><?php echo HEADING_TITLE_STATUS . ' ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onChange="this.form.submit();"'); ?></div>
              <?php echo tep_hide_session_id(); ?></form></div>
            </div></div>
          </div>
        </div></div>
      </div>
      <div class="row g-3 align-items-center mb-2">
        <div class="col-12 col-md-auto"><div class="container-fluid px-0 erp-form-layout">
          <div class="row g-3 align-items-center mb-2">
            <div class="col-12 col-md-auto"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    if (isset($HTTP_GET_VARS['cID'])) {
      $cID = tep_db_prepare_input($HTTP_GET_VARS['cID']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$cID . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by orders_id DESC";
    } elseif (isset($HTTP_GET_VARS['status']) && is_numeric($HTTP_GET_VARS['status']) && ($HTTP_GET_VARS['status'] > 0)) {
      $status = tep_db_prepare_input($HTTP_GET_VARS['status']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and s.orders_status_id = '" . (int)$status . "' and ot.class = 'ot_total' order by o.orders_id DESC";
    } else {
      $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by o.orders_id DESC";
    }
    $orders_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($orders = tep_db_fetch_array($orders_query)) {
    if ((!isset($HTTP_GET_VARS['oID']) || (isset($HTTP_GET_VARS['oID']) && ($HTTP_GET_VARS['oID'] == $orders['orders_id']))) && !isset($oInfo)) {
        $oInfo = new objectInfo($orders);
      }

      if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $orders['customers_name']; ?></td>
                <td class="dataTableContent" align="right"><?php echo strip_tags($orders['order_total']); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_datetime_short($orders['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders['orders_status_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><div class="container-fluid px-0 erp-form-layout">
                  <div class="row g-3 align-items-center mb-2">
                    <div class="col-12 col-md-auto"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></div>
                    <div class="col-12 col-md-auto"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'oID', 'action'))); ?></div>
                  </div>
                </div></td>
              </tr>
            </table></div>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ORDER . '</b>');

      $contents = array('form' => tep_draw_form('orders', FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br><br><b>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</b>');
      $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<b>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased));
        if (tep_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . tep_date_short($oInfo->last_modified));
        $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <div class="col-12 col-md-auto">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </div>' . "\n";
  }
?>
          </div>
        </div></div>
      </div>
<?php
  }
?>
    </div></div>
<!-- body_text_eof //-->
  </div>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
