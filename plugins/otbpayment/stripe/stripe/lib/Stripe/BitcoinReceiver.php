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

class BitcoinReceiver extends ExternalAccount
{
    public static function classUrl()
    {
        return "/v1/bitcoin/receivers";
    }

    public function instanceUrl()
    {
        $result = parent::instanceUrl();
        if ($result) {
            return $result;
        } else {
            $id = $this['id'];
            $id = Util\Util::utf8($id);
            $extn = urlencode($id);
            $base = BitcoinReceiver::classUrl();
            return "$base/$extn";
        }
    }

    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    public function refund($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/refund';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
