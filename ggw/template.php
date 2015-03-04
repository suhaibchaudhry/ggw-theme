<?php
function ggw_form_element($element, $value) {
  // This is also used in the installer, pre-database setup.
  $t = get_t();

  $output = '<div class="form-item"';
  if (!empty($element['#id'])) {
    $output .= ' id="' . $element['#id'] . '-wrapper"';
  }
  $output .= ">\n";
  $required = !empty($element['#required']) && $element['#title'] != 'Size' ? '<span class="form-required" title="' . $t('This field is required.') . '">*</span>' : '';

  if (!empty($element['#title'])) {
    $title = $element['#title'];
	if($title == 'Size') {
		$title = 'Select a size';
	}
    if (!empty($element['#id'])) {
      $output .= ' <label for="' . $element['#id'] . '">' . $t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) . "</label>\n";
    }
    else {
      $output .= ' <label>' . $t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) . "</label>\n";
    }
  }

  $output .= " $value\n";

  if (!empty($element['#description'])) {
    $output .= ' <div class="description">' . $element['#description'] . "</div>\n";
  }

  $output .= "</div>\n";

  return $output;
}

//Wrap fieldsets into a div
function ggw_fieldset($element) {
	if (!empty($element['#collapsible'])) {
		drupal_add_js('misc/collapse.js');

		if (!isset($element['#attributes']['class'])) {
 			$element['#attributes']['class'] = '';
		}

		$element['#attributes']['class'] .= ' collapsible';

		if (!empty($element['#collapsed'])) {
 			$element['#attributes']['class'] .= ' collapsed';
 		}
 	}

	return '<div class=\'fieldset-outer fieldset-'.$element['#attributes']['class'].' \'><fieldset'. drupal_attributes($element['#attributes']) .'>'. ($element['#title'] ? '<legend>'. $element['#title'] .'</legend>' : '') . (isset($element['#description']) && $element['#description'] ? '<div>'. $element['#description'] .'</div>' : '') . (!empty($element['#children']) ? $element['#children'] : '') . (isset($element['#value']) ? $element['#value'] : '') ."</fieldset></div>\n";
}

function ggw_uc_product_price($price, $context, $options = array()) {
  global $user;
  $output = '';

  $wrap = '<div class="product-info ' . implode(' ', (array) $context['class']) . '">';
  if($user->uid) {
    if(!empty($context['subject']['node']->original_price_pre_sale)) {
      $output .= '<div class="product-info product-sale-price ' . implode(' ', (array) $context['class']) . '">';
      $options_sale = $options;
      $options_sale['label'] = FALSE;
      $output .= uc_price($context['subject']['node']->original_price_pre_sale, $context, $options_sale);
      $output .= '</div>';
    }

    $output .= $wrap.uc_price($price, $context, $options);
    $output .= '</div>';
  } else {
	  $output .= $wrap.'Please login for price'; 
    $output .= '</div>';
  }

  return $output;
}

function ggw_content_class($left, $right) {
	if(empty($left) && !empty($right)) {
		return ' one-bar lbar';
	} else if(!empty($left) && empty($right)) {
		return ' one-bar rbar';
	} elseif(!empty($left) && !empty($right)) {
		return ' both-bars';
	} else {
		return ' no-bars';
	}
}

//Theme breadcrums
function ggw_breadcrumb($breadcrumb) {
  if (module_exists('taxonomy_breadcrumb')) {
    $item = menu_get_item();
    if($item['path'] == 'taxonomy/term/%') {
      require_once(drupal_get_path('module', 'taxonomy_breadcrumb') . '/taxonomy_breadcrumb.inc');
      $breadcrumb = _taxonomy_breadcrumb_generate_breadcrumb(arg(2));
    }

    if($item['path'] == 'node/%' && $item['page_arguments'][0]->type == 'product') {
      $last = array_pop($breadcrumb);
      $breadcrumb = array_slice($breadcrumb, 0, 3);
      $breadcrumb[] = $last;
    }
  }

  //return theme_breadcrumb($breadcrumb);
  
  if (!empty($breadcrumb)) {
    return implode(' › ', $breadcrumb);
  }
}

function ggw_preprocess_node(&$vars) {
	//Determine if product is last in row
	if($vars['type'] == 'product') {
		drupal_add_js(drupal_get_path('theme', 'ggw').'/js/product.js');
	}
}

function ggw_backend_transaction_details($ticketId) {
  $content = '<table>';

  $cash_sql = "SELECT ticket_id, amount_paid, `change` FROM pos_api_expose_cash_log WHERE ticket_id = '%d'";
  $cash = db_fetch_object(db_query($cash_sql, $ticketId));
  if($cash->ticket_id) {
    $content .= '<tr><th>Method</th><th>Payment</th><th>Change</th></tr>';
    $content .= '<tr><td>CASH</td><td>'.uc_currency_format($cash->amount_paid).'</td><td>'.uc_currency_format($cash->change).'</td></tr>';
  }

  $check_sql = "SELECT ticket_id, paid_amount, cash_date FROM pos_api_expose_check_log WHERE ticket_id = '%d'";
  $check = db_fetch_object(db_query($check_sql, $ticketId));
  if($check->ticket_id) {
    $content .= '<tr><th>Method</th><th>Payment</th><th>Check Date</th></tr>';
    $content .= '<tr><td>CHECK</td><td>'.uc_currency_format($check->paid_amount).'</td><td>'.$check->cash_date.'</td></tr>';
  }

  $mo_sql = "SELECT ticket_id, paid_amount, reference FROM pos_api_expose_money_order_log WHERE ticket_id = '%d'";
  $mo = db_fetch_object(db_query($mo_sql, $ticketId));
  if($mo->ticket_id) {
    $content .= '<tr><th>Method</th><th>Payment</th><th>Ref.</th></tr>';
    $content .= '<tr><td>M.O.</td><td>'.uc_currency_format($mo->paid_amount).'</td><td>'.$mo->reference.'</td></tr>';
  }

  $cc_sql = "SELECT ticket_id, amount_paid, transaction_id FROM pos_api_expose_credit_card_log WHERE ticket_id = '%d'";
  $cc = db_fetch_object(db_query($cc_sql, $ticketId));
  if($cc->ticket_id) {
    $content .= '<tr><th>Method</th><th>Payment</th><th>CC Type</th><th>Account</th><th>Customer Name</th></tr>';
    $td = _pos_api_transaction_details($cc->transaction_id);

    $content .= '<tr><td>Credit Card</td><td>'.uc_currency_format($cc->amount_paid).'</td><td>'.$td->xml->transaction->payment->creditCard->cardType.'</td><td>'.$td->xml->transaction->payment->creditCard->cardNumber.'</td><td>'.$td->xml->transaction->billTo->firstName.' '.$td->xml->transaction->billTo->lastName.'</td></tr>';
  }

  $ar_sql = "SELECT order_id, credit_amount, due_date FROM user_term_credits_usages u WHERE u.order_id = '%d'";
  $ar = db_fetch_object(db_query($ar_sql, $ticketId));
  if($ar->order_id) {
    $content .= '<tr><th>Method</th><th>Amount</th><th>Due Date</th></tr>';
    $content .= '<tr><td>Credit</td><td>'.uc_currency_format($ar->credit_amount).'</td><td>'.date("n/j/Y", $ar->due_date).'</td></tr>';
  }

  $rma_sql = "SELECT rl.pid, ABS(rl.total_refund) as total_refund, tl.cuid FROM pos_api_expose_rma_refund_log rl INNER JOIN pos_api_expose_transaction_log tl ON rl.pid = tl.rma_pid WHERE rl.rma_ticket_id = '0' AND tl.ticket_id = '%d'";
  $rma = db_fetch_object(db_query($rma_sql, $ticketId));
  if($rma->pid) {
    $rma_credits = db_result(db_query("SELECT IFNULL(SUM(total_refund),0) FROM {pos_api_expose_rma_refund_log} WHERE cuid = '%d' AND credit_usage_id = '0'", $rma->cuid));
    $content .= '<tr><th>Method</th><th>Amount</th><th>Current RMA Balance</th></tr>';
    $content .= '<tr><td>Redeem RMA</td><td>'.uc_currency_format($rma->total_refund).'</td><td>'.uc_currency_format($rma_credits).'</td></tr>';
  }

  $content .= '</table>';

  return $content;
}