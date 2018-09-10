<?php

	function pay_balance_google()
	{
		myadmin_log('billing', 'info', 'Pay with Google Called', __LINE__, __FILE__);
		page_title('Pay Balance With Google Checkout');
		$table = new TFTable;
		if ($GLOBALS['tf']->ima == 'admin') {
			$custid = $GLOBALS['tf']->db->real_escape($GLOBALS['tf']->variables->request['custid']);
		} else {
			$custid = $GLOBALS['tf']->session->account_id;
		}
		$table = new TFTable;
		$module = 'default';
		if (isset($GLOBALS['tf']->variables->request['module'])) {
			$module = $GLOBALS['tf']->variables->request['module'];
		}
		$module = get_module_name($module);
		$settings = \get_module_settings($module);
		$custid = get_custid($custid, $module);
		$table->add_hidden('module', $module);
		$db = get_module_db($module);
		$GLOBALS['tf']->accounts->set_db_module($module);
		$GLOBALS['tf']->history->set_db_module($module);
		$data = $GLOBALS['tf']->accounts->read($custid);
		$table->set_title('Make '.$settings['TBLNAME'].' Google Checkout Payment');
		$table->add_hidden('custid', $custid);
		$table->add_field('Invoice Description');
		$table->add_field('Invoice Amount');
		$table->add_row();
		$invoices = [];
		if (isset($GLOBALS['tf']->variables->request['invoices'])) {
			$GLOBALS['tf']->variables->request['invoices'] = $db->real_escape(str_replace('INV'.$module, '', $GLOBALS['tf']->variables->request['invoices']));
			$table->add_hidden('invoices', $GLOBALS['tf']->variables->request['invoices']);
			$query = "select * from invoices where invoices_module='{$module}' and invoices_paid=0 and invoices_type=1 and invoices_custid='{$custid}' and invoices_id in ('" . implode("','", explode(',', $GLOBALS['tf']->variables->request['invoices'])) . "') order by invoices_id desc";
			myadmin_log('billing', 'info', $query, __LINE__, __FILE__);
			$db->query($query, __LINE__, __FILE__);
		} else {
			$query = "select * from invoices where invoices_module='{$module}' and invoices_paid=0 and invoices_type=1 and invoices_custid='{$custid}' order by invoices_id desc";
			$db->query($query, __LINE__, __FILE__);
		}
		// START GOOGLE CHECKOUT CODE
		$gpost = ['_type' => 'checkout-shopping-cart'];
		//				add_output("<form enctype='multipart/form-data' action='https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/".GOOGLE_CHECKOUT_MERCHANT_ID."' METHOD='POST'>");
		$gidx = 0;
		$gpost['checkout-flow-support.merchant-checkout-flow-support.continue-shopping-url'] = 'https://'.DOMAIN . URLDIR . $GLOBALS['tf']->link('/index.php', 'choice=none.view_balance');
		$iids = [];
		$amount = 0;
		while ($db->next_record(MYSQL_ASSOC)) {
			$amount = bcadd($amount, $db->Record['invoices_amount'], 2);
			$invoices[] = $db->Record['invoices_id'];
			$iids[] = 'INV'.$module . $db->Record['invoices_id'];
			$table->add_field($db->Record['invoices_description']);
			$table->add_field('$'.$db->Record['invoices_amount'], 'r');
			$table->add_row();
			++$gidx;
			$gpost['item_name_'.$gidx] = $db->Record['invoices_description'];
			$gpost['item_description_'.$gidx] = $db->Record['invoices_description'];
			$gpost['item_price_'.$gidx] = $db->Record['invoices_amount'];
			$gpost['item_currency_'.$gidx] = 'USD';
			$gpost['item_quantity_'.$gidx] = 1;
			$gpost['shopping-cart.items.item-'.$gidx.'.digital-content.email-delivery'] = 'true';
			//add_output('<input type="hidden" name="item_name_'.$gidx.'" value="'.$checkout_item['name'].'"/><input type="hidden" name="item_description_'.$gidx.'" value="'.$checkout_item['name'].'"/><input type="hidden" name="item_price_'.$gidx.'" value="'.number_format($checkout_item['amt'], 2).'"/><input type="hidden" name="item_currency_'.$gidx.'" value="USD"/><input type="hidden" name="item_quantity_'.$gidx.'" value="'.$checkout_item['qty'].'"/><input type="hidden" name="shopping-cart.items.item-'.$gidx.'.digital-content.display-disposition" value="PESSIMISTIC"/><input type="hidden" name="shopping-cart.items.item-'.$gidx.'.digital-content.email-delivery" value="true"/><input type="hidden" name="item_merchant_id" value="1234abcd"/>');
			//			}
		}
		$gpost['shopping-cart.items.item-1.merchant-private-item-data'] = myadmin_stringify(['ipn', $iids]);
		$table->add_hidden('balance', $amount);
		$table->add_field('<b>Total Amount To Pay</b>', 'r');
		$table->add_field('<b>$'.$amount.'</b>', 'r');
		//			$table->add_field('$'.$table->make_input('balance', $data['balance'], 10));
		$table->add_row();
		if (GOOGLE_CHECKOUT_SANDBOX === true) {
			$goptions = [
				CURLOPT_USERPWD => GOOGLE_CHECKOUT_SANDBOX_MERCHANT_ID.':'.GOOGLE_CHECKOUT_SANDBOX_MERCHANT_KEY,
				CURLOPT_POST => true,
				CURLOPT_HTTPAUTH => CURLAUTH_BASIC
			];
			$gresponse = getcurlpage('https://sandbox.google.com/checkout/api/checkout/v2/merchantCheckoutForm/Merchant/'.GOOGLE_CHECKOUT_SANDBOX_MERCHANT_ID, $gpost, $goptions);
		} else {
			$goptions = [
				CURLOPT_USERPWD => GOOGLE_CHECKOUT_MERCHANT_ID.':'.GOOGLE_CHECKOUT_MERCHANT_KEY,
				CURLOPT_POST => true,
				CURLOPT_HTTPAUTH => CURLAUTH_BASIC
			];
			$gresponse = getcurlpage('https://checkout.google.com/api/checkout/v2/merchantCheckoutForm/Merchant/'.GOOGLE_CHECKOUT_MERCHANT_ID, $gpost, $goptions);
		}
		$gparts = explode('&', $gresponse);
		$gresponse = [];
		foreach ($gparts as $gpart) {
			$gpart = explode('=', $gpart);
			$gresponse[$gpart[0]] = urldecode($gpart[1]);
		}
		//echo '<pre>';print_r($gresponse);echo '</pre>';
		if ($gresponse['_type'] == 'checkout-redirect') {
			$table->set_colspan(2);
			if (GOOGLE_CHECKOUT_SANDBOX === true) {
				$table->add_field('<a href="'.$gresponse['redirect-url'].'"><img src="http://sandbox.google.com/checkout/buttons/checkout.gif?merchant_id='.GOOGLE_CHECKOUT_SANDBOX_MERCHANT_ID.'&w=160&h=43&style=trans&variant=text&loc=en_US" height=43 width=160 alt="Google Checkout"></a>');
			} else {
				$table->add_field('<a href="'.$gresponse['redirect-url'].'"><img src="http://checkout.google.com/buttons/checkout.gif?merchant_id='.GOOGLE_CHECKOUT_MERCHANT_ID.'&w=160&h=43&style=trans&variant=text&loc=en_US" height=43 width=160 alt="Google Checkout"></a>');
			}
			$table->add_row();
		}
		// END GOOGLE CHECKOUT CODE
		add_output($table->get_table());
	}
