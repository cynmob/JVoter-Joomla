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

class Customer extends ApiResource
{
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

    public function addInvoiceItem($params = null)
    {
        if (!$params) {
            $params = array();
        }
        $params['customer'] = $this->id;
        $ii = InvoiceItem::create($params, $this->_opts);
        return $ii;
    }

    public function invoices($params = null)
    {
        if (!$params) {
            $params = array();
        }
        $params['customer'] = $this->id;
        $invoices = Invoice::all($params, $this->_opts);
        return $invoices;
    }

    public function invoiceItems($params = null)
    {
        if (!$params) {
            $params = array();
        }
        $params['customer'] = $this->id;
        $iis = InvoiceItem::all($params, $this->_opts);
        return $iis;
    }

    public function charges($params = null)
    {
        if (!$params) {
            $params = array();
        }
        $params['customer'] = $this->id;
        $charges = Charge::all($params, $this->_opts);
        return $charges;
    }

    public function updateSubscription($params = null)
    {
        $url = $this->instanceUrl() . '/subscription';
        list($response, $opts) = $this->_request('post', $url, $params);
        $this->refreshFrom(array('subscription' => $response), $opts, true);
        return $this->subscription;
    }

    public function cancelSubscription($params = null)
    {
        $url = $this->instanceUrl() . '/subscription';
        list($response, $opts) = $this->_request('delete', $url, $params);
        $this->refreshFrom(array('subscription' => $response), $opts, true);
        return $this->subscription;
    }

    public function deleteDiscount()
    {
        $url = $this->instanceUrl() . '/discount';
        list($response, $opts) = $this->_request('delete', $url);
        $this->refreshFrom(array('discount' => null), $opts, true);
    }
}
