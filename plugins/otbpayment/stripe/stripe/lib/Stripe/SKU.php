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

class SKU extends ApiResource
{
    public static function retrieve($id, $opts = null)
    {
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

    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }
}
