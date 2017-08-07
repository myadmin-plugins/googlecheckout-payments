<?php
/**
* Billing Services related functions.
*
* This file has the general functions used heavily both by billing related code.
* Last Changed: $LastChangedDate: 2017-06-24 21:22:10 -0400 (Sat, 24 Jun 2017) $
* @author detain
* @copyright 2017
* @package MyAdmin
* @category Billing
*/

function view_google_order() {
	if (isset($_SERVER['HTTP_REFERER']) && preg_match('/google\.com\/.*t=(.*)$/', $_SERVER['HTTP_REFERER'], $matches)) {
		$order = $matches[1];
		$db = clone $GLOBALS['tf']->db;
		$db->query("select * from gcheckout where google_order='$order' and _type='link'", __LINE__, __FILE__);
		if ($db->num_rows() > 0) {
			$db->next_record(MYSQL_ASSOC);
			$GLOBALS['tf']->redirect($GLOBALS['tf']->link('index.php', $db->Record['data']));
		}
	}
}

