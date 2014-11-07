<?php
/**
 * This function formats an administrative page for viewing.
 *
 * @param $blocks
 *   An array of blocks to display. Each array should include a
 *   'title', a 'description', a formatted 'content' and a
 *   'position' which will control which container it will be
 *   in. This is usually 'left' or 'right'.
 * @ingroup themeable
 */
function ggw_backend_admin_page($blocks) {
  $stripe = 0;
  $container = array();

  foreach ($blocks as $block_name => $block) {
    if ($block_output = theme('admin_block', $block)) {
      if (empty($block['position'])) {
        // perform automatic striping.
        $block['position'] = ++$stripe % 2 ? 'left' : 'right';
      }
	  if($block_name == '49952 User management 437') {
		 $block['position'] = 'right'; 
	  }
      if (!isset($container[$block['position']])) {
        $container[$block['position']] = '';
      }
      $container[$block['position']] .= $block_output;
    }
  }

  $output = '<div class="admin clear-block">';
  $output .= '<div class="compact-link">';
  if (system_admin_compact_mode()) {
    $output .= l(t('Show descriptions'), 'admin/compact/off', array('attributes' => array('title' => t('Expand layout to include descriptions.'))));
  }
  else {
    $output .= l(t('Hide descriptions'), 'admin/compact/on', array('attributes' => array('title' => t('Compress layout by hiding descriptions.'))));
  }
  $output .= '</div>';

  foreach ($container as $id => $data) {
    $output .= '<div class="'. $id .' clear-block">';
    $output .= $data;
    $output .= '</div>';
  }
  $output .= '</div>';
  return $output;
}

function ggw_backend_invoice_credit_amount($ticketId) {
  $ar_sql = "SELECT credit_amount FROM user_term_credits_usages u WHERE u.order_id = '%d'";
  return db_result(db_query($ar_sql, $ticketId));
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

  $rma_sql = "SELECT rl.pid, rl.total_refund, tl.cuid FROM pos_api_expose_rma_refund_log rl INNER JOIN pos_api_expose_transaction_log tl ON rl.pid = tl.rma_pid WHERE rl.rma_ticket_id = '0' AND tl.ticket_id = '%d'";
  $rma = db_fetch_object(db_query($rma_sql, $ticketId));
  if($rma->pid) {
    $rma_credits = db_result(db_query("SELECT IFNULL(SUM(total_refund),0) FROM {pos_api_expose_rma_refund_log} WHERE cuid = '%d' AND credit_usage_id = '0'", $rma->cuid));
    $content .= '<tr><th>Method</th><th>Amount</th><th>Current RMA Balance</th></tr>';
    $content .= '<tr><td>Redeem RMA</td><td>'.uc_currency_format($rma->total_refund).'</td><td>'.uc_currency_format($rma_credits).'</td></tr>';
  }

  $content .= '</table>';

  return $content;
}