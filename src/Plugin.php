<?php

namespace Detain\MyAdminGooglecheckout;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminGooglecheckout
 */
class Plugin {

	public static $name = 'Googlecheckout Plugin';
	public static $description = 'Allows handling of Googlecheckout emails and honeypots';
	public static $help = '';
	public static $type = 'plugin';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
	}

	/**
	 * @return array
	 */
	public static function getHooks() {
		return [
			'system.settings' => [__CLASS__, 'getSettings'],
			//'ui.menu' => [__CLASS__, 'getMenu'],
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMenu(GenericEvent $event) {
		$menu = $event->getSubject();
		if ($GLOBALS['tf']->ima == 'admin') {
			function_requirements('has_acl');
					if (has_acl('client_billing'))
							$menu->add_link('admin', 'choice=none.abuse_admin', '//my.interserver.net/bower_components/webhostinghub-glyphs-icons/icons/development-16/Black/icon-spam.png', 'Googlecheckout');
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event) {
		$loader = $event->getSubject();
		$loader->add_requirement('class.Googlecheckout', '/../vendor/detain/myadmin-googlecheckout-payments/src/Googlecheckout.php');
		$loader->add_requirement('deactivate_kcare', '/../vendor/detain/myadmin-googlecheckout-payments/src/abuse.inc.php');
		$loader->add_requirement('deactivate_abuse', '/../vendor/detain/myadmin-googlecheckout-payments/src/abuse.inc.php');
		$loader->add_requirement('get_abuse_licenses', '/../vendor/detain/myadmin-googlecheckout-payments/src/abuse.inc.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getSettings(GenericEvent $event) {
		$settings = $event->getSubject();
		$settings->add_radio_setting('Billing', 'Google Checkout', 'google_checkout_enabled', 'Enable Google Checkout', 'Enable Google Checkout', GOOGLE_CHECKOUT_ENABLED, [true, false], ['Enabled', 'Disabled']);
		$settings->add_dropdown_setting('Billing', 'Google Checkout', 'google_checkout_sandbox', 'Use Sandbox/Test Environment', 'Use Sandbox/Test Environment', GOOGLE_CHECKOUT_SANDBOX, [false, true], ['Live Environment', 'Sandbox Test Environment']);
		$settings->add_text_setting('Billing', 'Google Checkout', 'google_checkout_merchant_id', 'Live Merchant ID', 'Live Merchant ID', (defined('GOOGLE_CHECKOUT_MERCHANT_ID') ? GOOGLE_CHECKOUT_MERCHANT_ID : ''));
		$settings->add_text_setting('Billing', 'Google Checkout', 'google_checkout_merchant_key', 'Live Merchant Key', 'Live Merchant Key', (defined('GOOGLE_CHECKOUT_MERCHANT_KEY') ? GOOGLE_CHECKOUT_MERCHANT_KEY : ''));
		$settings->add_text_setting('Billing', 'Google Checkout', 'google_checkout_sandbox_merchant_id', 'Sandbox Merchant ID', 'Sandbox Merchant ID', (defined('GOOGLE_CHECKOUT_SANDBOX_MERCHANT_ID') ? GOOGLE_CHECKOUT_SANDBOX_MERCHANT_ID : ''));
		$settings->add_text_setting('Billing', 'Google Checkout', 'google_checkout_sandbox_merchant_key', 'Sandbox Merchant Key', 'Sandbox Merchant Key', (defined('GOOGLE_CHECKOUT_SANDBOX_MERCHANT_KEY') ? GOOGLE_CHECKOUT_SANDBOX_MERCHANT_KEY : ''));
	}

}