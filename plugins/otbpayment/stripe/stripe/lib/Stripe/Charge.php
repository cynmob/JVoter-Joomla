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

class Charge extends ApiResource
{
    public static function retrieve($id, $options = null)
    {
        return self::_retrieve($id, $options);
    }

    public static function all($params = null, $options = null)
    {
        return self::_all($params, $options);
    }

    public static function create($params = null, $options = null)
    {
        return self::_create($params, $options);
    }

    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    public function save($options = null)
    {
        return $this->_save($options);
    }

    public function refund($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/refund';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    public function capture($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/capture';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    public function updateDispute($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/dispute';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom(array('dispute' => $response), $opts, true);
        return $this->dispute;
    }

    public function closeDispute($options = null)
    {
        $url = $this->instanceUrl() . '/dispute/close';
        list($response, $opts) = $this->_request('post', $url, null, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    public function markAsFraudulent($opts = null)
    {
        $params = array('fraud_details' => array('user_report' => 'fraudulent'));
        $url = $this->instanceUrl();
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }

    public function markAsSafe($opts = null)
    {
        $params = array('fraud_details' => array('user_report' => 'safe'));
        $url = $this->instanceUrl();
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
