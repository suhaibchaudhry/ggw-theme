<?php
  /**
   * @file
   * This file is the default customer invoice template for Ubercart.
   */
    $uid = $order->uid;
    if($uid) {
      $profile = content_profile_load('profile', $uid);
      //dpm($profile);
    }
?>
<!DOCTYPE HTML>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="<?php print base_path() . path_to_theme(); ?>/css/invoice.css" media="all" />
  <title>Invoice</title>
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
    <div class="header">
      <div class="logo column"></div>
      <div class="address column">
        <p>8000 Harwin dr. SUITE #200 Houston, TX 77036</p>
        <p><span class="label">Email: </span>info@general-goods.com</p>
        <p><span class="label">Website: </span>info@general-goods.com</p>
        <p><span class="label">Phone: </span>713-780-3636 <span class="label">Fax: </span>713-780-3636</p>
        <p><span class="label">Tobacco ID: </span>11111111</p>
      </div>
      <div class="invoice-info column">
        <p class="page"><span class="label">Page:</span> <span class="value"></span></p>
        <p class="invoiceid"><span class="label">Invoice #:</span> <span class="value"><?php echo $order_link; ?></span></p>
        <p class="invoicedate"><span class="label">Invoice Date:</span> <span class="value"><?php echo $order_date_created; ?></span></p>
      </div>
    </div>

    <div class="customer-info box">
      <div class="header-line"></div>
      <div class="billing-address cust-address">
        <span class="label">Sold To:</span>
        <span class="value">
          <?php print $profile->title ?> (<?php print $profile->name ?>)<br />
          <?php print $profile->field_contact_remarks[0]['value'] ?><br />
          <?php print $profile->field_company_address[0]['street1'] ?><br />
          <?php print $profile->field_company_address[0]['city'] ?>, <?php print $profile->field_company_address[0]['state'] ?> <?php print $profile->field_company_address[0]['zip'] ?><br />
        </span>
      </div>
      <div class="shipping-address cust-address">
        <span class="label">Deliver To:</span>
        <span class="value">
          <?php print $profile->title ?> (<?php print $profile->name ?>)<br />
          <?php print $profile->field_shipping_address[0]['value'] ?><br />
          <?php print $profile->field_shipping_address[0]['street1'] ?><br />
          <?php print $profile->field_shipping_address[0]['city'] ?>, <?php print $profile->field_shipping_address [0]['state'] ?> <?php print $profile->field_shipping_address [0]['zip'] ?><br />
          <?php print $profile->field_contact_remarks[0]['value'] ?><br /><br />
          <strong>Phone: </strong><?php print $profile->field_profile_company_phone[0]['number'] ?>
        </span>
      </div>
    </div>

    <div class="products">
      <?php
      $tables = array();
      //Add suggested
      $header = array(array('data' => 'Carton Barcode', 'class' => 'left-item'), 'Description', 'Packaging', 'Qty', 'Price', 'Extended', 'Unit Price', 'Sugg. Retail', 'Profit Margin', 'Extended Retail');
      foreach ($products as $product) {
        $node = node_load($product->nid);
        foreach($node->taxonomy as $term) {
          if($term->vid == 7) {
            $term_info = db_fetch_object(db_query("SELECT invoice_category, retail_markup FROM {ggw_state_reporting_terms} WHERE vid = '%d'", $term->tid));
            $category = $term_info->invoice_category;
            $retail_markup = 1+($term_info->retail_markup/100);
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
        }

        $form = (int)$node->field_prod_form[0]['value'];
        $unit_price = $product->price/$form;
        $suggested = $unit_price*$retail_markup;
        $extended = $product->price*$product->qty*$retail_markup;
        $row = array(
          $product->model,
          //$node->field_prod_unit_barcode[0]['value'],
          $node->title,
          $node->field_prod_packing[0]['value'],
          array('data' => $product->qty, 'class' => 'qty-item'),
          array('data' => number_format($product->price, 2), 'class' => 'numeric-item'),
          array('data' => number_format($product->price*$product->qty, 2), 'class' => 'numeric-item'),
          array('data' => number_format($unit_price, 2), 'class' => 'numeric-item'),
          array('data' => number_format($suggested, 2), 'class' => 'numeric-item'),
          array('data' => $term_info->retail_markup.'%', 'class' => 'numeric-item'),
          array('data' => number_format($extended, 2), 'class' => 'numeric-item')
        );

        $tables[$category]['rows'][] = $row;
        $tables[$category]['line_count']++;
        $tables[$category]['item_count'] += $product->qty;
        $tables[$category]['total'] += $product->price;
        $tables[$category]['unit_price'] += $unit_price;
        $tables[$category]['suggested'] += $suggested;
        $tables[$category]['profit'] += $term_info->retail_markup;
        $i++;
        $tables[$category]['retail'] += $extended;
      }
      foreach($tables as $category => $rows) {
        print '<div class="category-wrap">'.$category.'</div>';
        $rows['rows'][] = array(
          '',
          '<center><strong>Line Count:</strong> '.$tables[$category]['line_count'].'</center>',
          array('data' => '<div style="text-align: right;"><strong>Count:</strong></div>', 'class' => 'no-border'),
          '<center>'.$tables[$category]['item_count'].'</center>',
          '',
          array('data' => '<strong>$'.number_format($tables[$category]['total'], 2).'</strong>', 'class' => 'numeric-item'),
          array('data' => '<strong>$'.number_format($tables[$category]['unit_price'], 2).'</strong>', 'class' => 'numeric-item'),
          array('data' => '<strong>$'.number_format($tables[$category]['suggested'], 2).'</strong>', 'class' => 'numeric-item'),
          array('data' => '<strong>'.number_format($tables[$category]['profit']/$i, 2).'%</strong>', 'class' => 'numeric-item'),
          array('data' => '<strong>$'.number_format($tables[$category]['retail'], 2).'</strong>', 'class' => 'numeric-item')
        );
        print theme('table', $header, $rows['rows']);
      }
      ?>
    </div>

    <div class="line-items"><?php print uc_order_pane_line_items('view', $order); ?></div>
  </div>
</body>
</html>