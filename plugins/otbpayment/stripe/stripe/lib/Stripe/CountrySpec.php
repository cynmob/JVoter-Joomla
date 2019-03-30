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

class CountrySpec extends ApiResource
{
    public static function className()
    {
        return 'country_spec';
    }

    public static function retrieve($country, $opts = null)
    {
        return self::_retrieve($country, $opts);
    }

    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }
}
