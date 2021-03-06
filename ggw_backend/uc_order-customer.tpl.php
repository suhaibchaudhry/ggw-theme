<?php
  /**
   * @file
   * This file is the default customer invoice template for Ubercart.
   */
    //db_query("SELECT billing_zone FROM uc_orders WHERE order_id = '%d'", $order->order_id);

    $uid = $order->uid;
    if($uid) {
      $profile = content_profile_load('profile', $uid);
      //dpm($profile);
    }

    $rma_ticket = false;
    $title_limit = 28;
    $packaging_limit = 8;
    if($order_status == "Closed RMA Ticket") {
      $rma_ticket = true;
      $title_limit = 40;
      $packaging_limit = 15;
      $cash_rma = db_result(db_query("SELECT COUNT(*) FROM pos_api_expose_transaction_log WHERE ticket_id = '%d' AND rma_pid = '-1'", $order->order_id));
    }

    /*$contact = '';
    if(!empty($profile->field_profile_first_name[0]['value']) || !empty($profile->field_last_name[0]['value'])) {
      if(!empty($profile->field_profile_first_name[0]['value'])) {
        $contact .= $profile->field_profile_first_name[0]['value'].' ';
      }

      $contact .= $profile->field_last_name[0]['value'];
    } else {
      $contact = preg_replace(array('/\s*(T|t)?(X|x)?(D|d)(L|l).{0,3}(\d+|[^a-zA-Z]$)/', '/\#\d+/', '/DL: /'), '', $profile->field_contact_remarks[0]['value']);
    }*/

    function sortCallback($a, $b) {
      return strcasecmp(trim($a[1]), trim($b[1]));
    }

    function sortCategoryCallback($a, $b) {
      return $a['weight'] - $b['weight'];
    }

    if($cash_rma == '0') {
      $status_names = array(
      	"Closed Ticket" => "Customer Invoice",
      	"Quote Ticket" => "Customer Quotation",
      	"Closed RMA Ticket" => "Credit Memo"
      );
    } else {
      $status_names = array(
        "Closed Ticket" => "Customer Invoice",
        "Quote Ticket" => "Customer Quotation",
        "Closed RMA Ticket" => "Cash Refund Receipt"
      );
    }
   
    $full_user = user_load($uid);

    //if($full_user->credit_limits->pending_payments) {
      $ticket_ar = ggw_backend_invoice_credit_amount($order->order_id);
      if($ticket_ar) {
        $consumption_date = db_result(db_query("SELECT utcu.consumption_date FROM {user_term_credits_usages} utcu WHERE utcu.order_id = '%d'", $order->order_id));
        $credit = _user_term_credits_getCredits($uid, $consumption_date);
        $payment_remaining = theme('table', array('Starting Balance', 'Ending Balance'), array(
          array(uc_currency_format($credit->pending_payments), uc_currency_format($credit->pending_payments+$ticket_ar))
        ));
      }
    //}
?>
<!DOCTYPE HTML>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="<?php print base_path() . path_to_theme(); ?>/css/invoice.css" media="all" />
  <?php if($order->billing_zone == '1') { ?>
  <title>Receipt</title>
  <?php } else { ?>
  <title><?php print $profile->title ?> (<?php print $profile->name ?>)</title>
  <?php } ?>
  <style type="text/css" media="all">
  body {
    -webkit-print-color-adjust:exact;
  }
  table td.numeric-item {
    text-align: right;
  }
  table td.qty-item {
    text-align: center;
  }
  table th.left-item {
    text-align: left;
  }
  </style>
  <script type="text/javascript">
  window.onload = function () {
    window.print();
    setTimeout(function(){window.close();}, 1);
  }
  </script>
</head>
<body>
  <div class="invoice">
    <?php if($order->billing_zone == '0') { ?>
      <div class="header">
        <div class="logo column"></div>
        <div class="address column">
          <p>8000 Harwin dr. Suite #200 Houston, TX 77036</p>
          <p><span class="label">Email: </span>info@general-goods.com</p>
          <p><span class="label">Website: </span>info@general-goods.com</p>
          <p><span class="label">Phone: </span>713-780-3636 <span class="label">Fax: </span>713-780-1718</p>
          <br /><br />
        </div>
        <div class="invoice-info column">
          <?php if($status_names[$order_status]) : ?><p class="invoiceid" style="font-size: 1.5em;"><span class="label"><?php print $status_names[$order_status] ?></span></p><?php endif; ?>
          <p class="invoiceid"><span class="label">Invoice #:</span> <span class="value"><?php echo $order_link; ?></span></p>
          <p class="invoicedate"><span class="label">Invoice Date:</span> <span class="value"><?php echo date("n/j/Y g:ia", $order->created); ?></span></p>
          <?php if($profile->field_tobacco_permit_id[0]['value']) : ?><p><span class="label">Tobacco ID: </span><?php echo $profile->field_tobacco_permit_id[0]['value']; ?></p><?php endif; ?>
          <?php if($profile->field_tax_id[0]['value']) : ?><p><span class="label">Tax ID: </span><?php echo $profile->field_tax_id[0]['value']; ?></p><?php endif; ?>
        </div>
      </div>

      <div class="customer-info box">
        <div class="header-line"></div>
        <div class="billing-address cust-address">
          <span class="label">Sold To:</span>
          <span class="value">
            <?php if($uid) { ?>
            <?php print $profile->title ?> (<?php print $profile->name ?>)<br />
            <!-- <?php //print $contact ?><br /> -->
            <?php print $profile->field_company_address[0]['street1'] ?><br />
            <?php print $profile->field_company_address[0]['city'] ?>, <?php print $profile->field_company_address[0]['state'] ?> <?php print $profile->field_company_address[0]['zip'] ?><br />
            <?php } else { ?>
              Walk-in Customer
            <?php } ?>
          </span>
        </div>
        <?php if($uid) : ?>
        <div class="shipping-address cust-address">
          <span class="label">Deliver To:</span>
          <span class="value">
            <?php print $profile->title ?> (<?php print $profile->name ?>)<br />
            <?php print $profile->field_shipping_address[0]['value'] ?><br />
            <?php print $profile->field_shipping_address[0]['street1'] ?><br />
            <?php print $profile->field_shipping_address[0]['city'] ?>, <?php print $profile->field_shipping_address [0]['state'] ?> <?php print $profile->field_shipping_address [0]['zip'] ?><br />
            <!-- <?php //print $contact ?><br /><br /> -->
            <strong>Phone: </strong><?php print $profile->field_profile_company_phone[0]['number'] ?>
          </span>
        </div>
        <?php endif; ?>
      </div>
    <?php } ?>

    <div class="products">
      <?php
      $tables = array();
      if($rma_ticket) {
        //Add suggested
        $header = array(array('data' => 'Barcode', 'class' => 'left-item'), 'Description', 'Packaging', 'Qty', 'Price', 'Extended');
      } else {
        //Add suggested
        $header = array(array('data' => 'Barcode', 'class' => 'left-item'), 'Description', 'Packaging', 'Qty', 'Price', 'Extended', 'Unit Price', 'Sugg. Retail', 'Profit Margin', 'Extended Retail');
      }
      //Condense multi-line item products.
      $exists = array();
      $new = array();
      
      $products_new = array();
      foreach ($products as $k => $v) {
       $products_new[$k] = clone $v;
      }

      foreach($products_new as $key => $product) {
        if(isset($exists[$product->nid])) {
          $key_first = $exists[$product->nid];
          $qty_split = db_result(db_query("SELECT qty_split FROM pos_api_expose_manager_override_log WHERE product_nid = '%d'", $product->order_product_id));
          if($qty_split) {
            $products_new[$key_first]->qty += $product->qty/$qty_split;
          } else {
            $products_new[$key_first]->qty += $product->qty;
          }
          unset($products_new[$key]);
        } else {
          $exists[$product->nid] = $key;
        }
      }

      foreach ($products_new as $product) {
        $node = node_load($product->nid);
        foreach($node->taxonomy as $term) {
          if($term->vid == 7) {
            $term_info = db_fetch_object(db_query("SELECT invoice_category, retail_markup FROM {ggw_state_reporting_terms} WHERE vid = '%d'", $term->tid));
            $category = $term_info->invoice_category;
            $retail_markup = $term_info->retail_markup;
            break;
          }
        }

        if(!isset($category)) {
          $category = 'Misc';
        }

        if(!isset($tables[$category]['rows'])) {
          $tables[$category]['rows'] = array();
          $tables[$category]['line_count'] = 0;
          $tables[$category]['item_count'] = 0;
          $tables[$category]['total'] = 0;
          $tables[$category]['unit_price'] = 0;
          $tables[$category]['suggested'] = 0;
          $i = 0;
          $tables[$category]['profit'] = 0;
          $tables[$category]['retail'] = 0;
          if(!isset($tables[$category]['weight'])) {
            $tables[$category]['weight'] = db_result(db_query("SELECT weight FROM term_data WHERE name = '%s' AND vid = '1'", $category));
          }
        }

        $form = (int)$node->field_prod_form[0]['value'];
        if(empty($form)) {
          $form = 1;
        }
        $qty_split = db_result(db_query("SELECT qty_split FROM pos_api_expose_manager_override_log WHERE product_nid = '%d'", $product->order_product_id));
      	if($qty_split) {
      		$form = $form/$qty_split;
      	}
	      $unit_price = $product->price/$form;
        $suggested = ($unit_price/(100-$retail_markup))*100;
        $extended = (($product->price*$product->qty)/(100-$retail_markup))*100;

        if($node->original_price_pre_sale) {
          $price_formatted = '<span style="text-decoration: line-through;">'.number_format($node->original_price_pre_sale, 2).'</span> '.number_format($product->price, 2);
        } else {
          $price_formatted = number_format($product->price, 2);
        }

        if($rma_ticket) {
          $row = array(
            $product->model,
            //$node->field_prod_unit_barcode[0]['value'],
            strlen($node->title) > $title_limit ? substr($node->title,0,$title_limit)."..." : $node->title,
            strlen($node->field_prod_packing[0]['value']) > $packaging_limit ? substr($node->field_prod_packing[0]['value'],0,$packaging_limit)."..." : $node->field_prod_packing[0]['value'],
            array('data' => round($product->qty, 2), 'class' => 'qty-item'),
            array('data' => number_format($product->price, 2), 'class' => 'numeric-item'),
            array('data' => number_format($product->price*$product->qty, 2), 'class' => 'numeric-item')
          );
        } else {
          $row = array(
            $product->model,
            //$node->field_prod_unit_barcode[0]['value'],
            strlen($node->title) > $title_limit ? substr($node->title,0,$title_limit)."..." : $node->title,
            strlen($node->field_prod_packing[0]['value']) > $packaging_limit ? substr($node->field_prod_packing[0]['value'],0,$packaging_limit)."..." : $node->field_prod_packing[0]['value'],
            array('data' => round($product->qty, 2), 'class' => 'qty-item'),
            array('data' => $price_formatted, 'class' => 'numeric-item'),
            array('data' => number_format($product->price*$product->qty, 2), 'class' => 'numeric-item'),
            array('data' => number_format($unit_price, 2), 'class' => 'numeric-item'),
            array('data' => number_format($suggested, 2), 'class' => 'numeric-item'),
            array('data' => $term_info->retail_markup.'%', 'class' => 'numeric-item'),
            array('data' => number_format($extended, 2), 'class' => 'numeric-item')
          );
        }

        $tables[$category]['rows'][] = $row;
        $tables[$category]['line_count']++;
        $tables[$category]['item_count'] += $product->qty;
        $tables[$category]['total'] += $product->price*$product->qty;
        //$tables[$category]['unit_price'] += $unit_price;
        //$tables[$category]['suggested'] += $suggested*$product->qty;
        $tables[$category]['profit'] += $term_info->retail_markup;
        $i++;
        $tables[$category]['retail'] += $extended;
      }

      uasort($tables, 'sortCategoryCallback');
      $j = 0;
      $cat_count = count($tables);
      foreach($tables as $category => $rows) {
        usort($rows['rows'], 'sortCallback');

        if($cat_count > 1 && $j == $cat_count-1) {
          print '<div style="page-break-inside: avoid;">';
        } else {
          print '<div>';
        }

        print '<div class="category-wrap" style="page-break-inside: avoid;">'.$category.'</div>';
        if($rma_ticket) {
          $rows['rows'][] = array(
            '',
            '<center><strong>Line Count:</strong> '.$tables[$category]['line_count'].'</center>',
            array('data' => '<div style="text-align: right;"><strong>Count:</strong></div>', 'class' => 'no-border'),
            '<center>'.$tables[$category]['item_count'].'</center>',
            '',
            array('data' => '<strong>$'.number_format($tables[$category]['total'], 2).'</strong>', 'class' => 'numeric-item')
          );
        } else {
          $rows['rows'][] = array(
            '',
            '<center><strong>Line Count:</strong> '.$tables[$category]['line_count'].'</center>',
            array('data' => '<div style="text-align: right;"><strong>Count:</strong></div>', 'class' => 'no-border'),
            '<center>'.$tables[$category]['item_count'].'</center>',
            '',
            array('data' => '<strong>$'.number_format($tables[$category]['total'], 2).'</strong>', 'class' => 'numeric-item'),
            '',
            '',
            //array('data' => '<strong>$'.number_format($tables[$category]['unit_price'], 2).'</strong>', 'class' => 'numeric-item'),
            //array('data' => '<strong>$'.number_format($tables[$category]['suggested'], 2).'</strong>', 'class' => 'numeric-item'),
            array('data' => '<strong>'.number_format($tables[$category]['profit']/$tables[$category]['line_count'], 2).'%</strong>', 'class' => 'numeric-item'),
            array('data' => '<strong>$'.number_format($tables[$category]['retail'], 2).'</strong>', 'class' => 'numeric-item')
          );
        }

        print theme('table', $header, $rows['rows']).'</div>';
        $j++;
      }
      ?>
        <div class="last-item" style="page-break-inside: avoid;">
          <hr style="margin-top: 5em;" />
          <?php if($rma_ticket) { ?>
          <div class="line-items"><?php print str_replace('Total', 'Refund Total', uc_order_pane_line_items('view', $order)); ?></div>
          <?php } else { ?>
          <div class="line-items"><?php print uc_order_pane_line_items('view', $order); ?></div>
          <?php } ?>
            <hr />
            <?php if($order->billing_zone == '0') { ?>
            <div class="payment-details"><?php print ggw_backend_transaction_details($order->order_id); ?></div>
            <?php } else { ?>
            <div class="payment-details"><?php print ggw_backend_transaction_details_dp($order->order_id); ?></div>
            <?php } ?>
            <?php if(isset($payment_remaining)) : ?>
            <hr />
            <div class="payment-details">
                  <p><?php print $payment_remaining; ?></p>
                  <hr />
            </div>
            <?php endif; ?>
          </div>
          <?php if(!$rma_ticket) : ?>
            <p style="margin: 2pt 0 0;">Customer Signature _____________________</p>
          <?php endif; ?>
        </div>
    </div>
</body>
</html>
