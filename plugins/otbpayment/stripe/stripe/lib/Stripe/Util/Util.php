<?php
/**
 * @version    v1.0.0
 * @package    jdonate
 * @author     Jdonate Team <support@jdonate.com>
 * @link       http://www.jdonate.com
 * @copyright  Copyright (C) 2018 Jdonate. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Stripe\Util;

use Stripe\StripeObject;

/**
 * ensure this file is being included by a parent file
 */
defined('_JEXEC') or die('Restricted access');

abstract class Util
{
    private static $isMbstringAvailable = null;

    public static function isList($array)
    {
        if (!is_array($array)) {
            return false;
        }

        foreach (array_keys($array) as $k) {
            if (!is_numeric($k)) {
                return false;
            }
        }
        return true;
    }

    public static function convertStripeObjectToArray($values)
    {
        $results = array();
        foreach ($values as $k => $v) {
            if ($k[0] == '_') {
                continue;
            }
            if ($v instanceof StripeObject) {
                $results[$k] = $v->__toArray(true);
            } elseif (is_array($v)) {
                $results[$k] = self::convertStripeObjectToArray($v);
            } else {
                $results[$k] = $v;
            }
        }
        return $results;
    }

    public static function convertToStripeObject($resp, $opts)
    {
        $types = array(
            'account' => 'Stripe\\Account',
            'alipay_account' => 'Stripe\\AlipayAccount',
            'bank_account' => 'Stripe\\BankAccount',
            'balance_transaction' => 'Stripe\\BalanceTransaction',
            'card' => 'Stripe\\Card',
            'charge' => 'Stripe\\Charge',
            'country_spec' => 'Stripe\\CountrySpec',
            'coupon' => 'Stripe\\Coupon',
            'customer' => 'Stripe\\Customer',
            'dispute' => 'Stripe\\Dispute',
            'list' => 'Stripe\\Collection',
            'invoice' => 'Stripe\\Invoice',
            'invoiceitem' => 'Stripe\\InvoiceItem',
            'event' => 'Stripe\\Event',
            'file' => 'Stripe\\FileUpload',
            'token' => 'Stripe\\Token',
            'transfer' => 'Stripe\\Transfer',
            'order' => 'Stripe\\Order',
            'order_return' => 'Stripe\\OrderReturn',
            'plan' => 'Stripe\\Plan',
            'product' => 'Stripe\\Product',
            'recipient' => 'Stripe\\Recipient',
            'refund' => 'Stripe\\Refund',
            'sku' => 'Stripe\\SKU',
            'source' => 'Stripe\\Source',
            'subscription' => 'Stripe\\Subscription',
            'three_d_secure' => 'Stripe\\ThreeDSecure',
            'fee_refund' => 'Stripe\\ApplicationFeeRefund',
            'bitcoin_receiver' => 'Stripe\\BitcoinReceiver',
            'bitcoin_transaction' => 'Stripe\\BitcoinTransaction',
        );
        if (self::isList($resp)) {
            $mapped = array();
            foreach ($resp as $i) {
                array_push($mapped, self::convertToStripeObject($i, $opts));
            }
            return $mapped;
        } elseif (is_array($resp)) {
            if (isset($resp['object']) && is_string($resp['object']) && isset($types[$resp['object']])) {
                $class = $types[$resp['object']];
            } else {
                $class = 'Stripe\\StripeObject';
            }
            return $class::constructFrom($resp, $opts);
        } else {
            return $resp;
        }
    }

    public static function utf8($value)
    {
        if (self::$isMbstringAvailable === null) {
            self::$isMbstringAvailable = function_exists('mb_detect_encoding');

            if (!self::$isMbstringAvailable) {
                trigger_error("It looks like the mbstring extension is not enabled. " .
                    "UTF-8 strings will not properly be encoded. Ask your system " .
                    "administrator to enable the mbstring extension, or write to " .
                    "support@stripe.com if you have any questions.", E_USER_WARNING);
            }
        }

        if (is_string($value) && self::$isMbstringAvailable && mb_detect_encoding($value, "UTF-8", true) != "UTF-8") {
            return utf8_encode($value);
        } else {
            return $value;
        }
    }
}
