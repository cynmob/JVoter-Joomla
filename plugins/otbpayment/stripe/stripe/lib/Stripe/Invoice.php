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

class Invoice extends ApiResource
{
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    public static function upcoming($params = null, $opts = null)
    {
        $url = static::classUrl() . '/upcoming';
        list($response, $opts) = static::_staticRequest('get', $url, $params, $opts);
        $obj = Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);
        return $obj;
    }

    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    public function pay($opts = null)
    {
        $url = $this->instanceUrl() . '/pay';
        list($response, $opts) = $this->_request('post', $url, null, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
