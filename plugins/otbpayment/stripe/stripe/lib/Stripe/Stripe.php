<?php
/**
 * @version    v1.0.0
 * @package    jdonate
 * @author     Jdonate Team <support@jdonate.com>
 * @link       http://www.jdonate.com
 * @copyright  Copyright (C) 2018 Jdonate. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Stripe;

/**
 * ensure this file is being included by a parent file
 */
defined('_JEXEC') or die('Restricted access');

class Stripe
{
    public static $apiKey;

    public static $apiBase = 'https://api.stripe.com';

    public static $apiUploadBase = 'https://uploads.stripe.com';

    public static $apiVersion = null;

    public static $accountId = null;

    public static $verifySslCerts = true;

    const VERSION = '3.21.0';

    public static function getApiKey()
    {
        return self::$apiKey;
    }

    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

    public static function getVerifySslCerts()
    {
        return self::$verifySslCerts;
    }

    public static function setVerifySslCerts($verify)
    {
        self::$verifySslCerts = $verify;
    }

    public static function getAccountId()
    {
        return self::$accountId;
    }

    public static function setAccountId($accountId)
    {
        self::$accountId = $accountId;
    }
}
