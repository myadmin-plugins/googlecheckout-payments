<?php

namespace Detain\MyAdminGooglecheckout;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminGooglecheckout
 */
class Plugin
{
	public static $name = 'Googlecheckout Plugin';
	public static $description = 'Allows handling of Googlecheckout based Payments through their Payment Processor/Payment System.';
	public static $help = '';
	public static $type = 'plugin';

	/**
	 * Plugin constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @return array
	 */
	public static function getHooks()
	{
		return [
			'system.settings' => [__CLASS__, 'getSettings'],
			//'ui.menu' => [__CLASS__, 'getMenu'],
			'function.requirements' => [__CLASS__, 'getRequirements'],
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMenu(GenericEvent $event)
	{
		$menu = $event->getSubject();
		if ($GLOBALS['tf']->ima == 'admin') {
			function_requirements('has_acl');
            if (has_acl('client_billing')) {
            }
		}
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event)
	{
        /**
         * @var \MyAdmin\Plugins\Loader $this->loader
         */
        $loader = $event->getSubject();
		$loader->add_page_requirement('view_google_transaction', '/../vendor/detain/myadmin-googlecheckout-payments/src/view_google_transaction.php');
		$loader->add_page_requirement('view_google_order', '/../vendor/detain/myadmin-googlecheckout-payments/src/view_google_order.php');
		$loader->add_page_requirement('pay_balance_google', '/../vendor/detain/myadmin-googlecheckout-payments/src/pay_balance_google.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
    public static function getSettings(GenericEvent $event)
    {
        /**
         * @var \MyAdmin\Settings $settings
         **/
        $settings = $event->getSubject();
		$settings->add_radio_setting(_('Billing'), _('Google Checkout'), 'google_checkout_enabled', _('Enable Google Checkout'), _('Enable Google Checkout'), GOOGLE_CHECKOUT_ENABLED, [true, false], ['Enabled', 'Disabled']);
		$settings->add_dropdown_setting(_('Billing'), _('Google Checkout'), 'google_checkout_sandbox', _('Use Sandbox/Test Environment'), _('Use Sandbox/Test Environment'), GOOGLE_CHECKOUT_SANDBOX, [false, true], ['Live Environment', 'Sandbox Test Environment']);
		$settings->add_text_setting(_('Billing'), _('Google Checkout'), 'google_checkout_merchant_id', _('Live Merchant ID'), _('Live Merchant ID'), (defined('GOOGLE_CHECKOUT_MERCHANT_ID') ? GOOGLE_CHECKOUT_MERCHANT_ID : ''));
		$settings->add_text_setting(_('Billing'), _('Google Checkout'), 'google_checkout_merchant_key', _('Live Merchant Key'), _('Live Merchant Key'), (defined('GOOGLE_CHECKOUT_MERCHANT_KEY') ? GOOGLE_CHECKOUT_MERCHANT_KEY : ''));
		$settings->add_text_setting(_('Billing'), _('Google Checkout'), 'google_checkout_sandbox_merchant_id', _('Sandbox Merchant ID'), _('Sandbox Merchant ID'), (defined('GOOGLE_CHECKOUT_SANDBOX_MERCHANT_ID') ? GOOGLE_CHECKOUT_SANDBOX_MERCHANT_ID : ''));
		$settings->add_text_setting(_('Billing'), _('Google Checkout'), 'google_checkout_sandbox_merchant_key', _('Sandbox Merchant Key'), _('Sandbox Merchant Key'), (defined('GOOGLE_CHECKOUT_SANDBOX_MERCHANT_KEY') ? GOOGLE_CHECKOUT_SANDBOX_MERCHANT_KEY : ''));
	}
}
