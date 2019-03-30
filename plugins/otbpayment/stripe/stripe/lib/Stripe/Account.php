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

class Account extends ApiResource
{
    public function instanceUrl()
    {
        if ($this['id'] === null) {
            return '/v1/account';
        } else {
            return parent::instanceUrl();
        }
    }

    public static function retrieve($id = null, $opts = null)
    {
        if (!$opts && is_string($id) && substr($id, 0, 3) === 'sk_') {
            $opts = $id;
            $id = null;
        }
        return self::_retrieve($id, $opts);
    }

    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    public function reject($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/reject';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }
}
