<?php
/**
* Billing Services related functions.
*
* This file has the general functions used heavily both by billing related code.
* @author Joe Huss <detain@interserver.net>
* @copyright 2019
* @package MyAdmin
* @category Billing
*/

function view_google_order()
{
    if (isset($_SERVER['HTTP_REFERER']) && preg_match('/google\.com\/.*t=(.*)$/', $_SERVER['HTTP_REFERER'], $matches)) {
        $order = $matches[1];
        $db = clone $GLOBALS['tf']->db;
        $db->query("select * from gcheckout where google_order='{$order}' and _type='link'", __LINE__, __FILE__);
        if ($db->num_rows() > 0) {
            $db->next_record(MYSQL_ASSOC);
            $GLOBALS['tf']->redirect($GLOBALS['tf']->link('index.php', $db->Record['data']));
        }
    }
}
