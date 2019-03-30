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

abstract class ExternalAccount extends ApiResource
{
    public function instanceUrl()
    {
        $id = $this['id'];
        if (!$id) {
            $class = get_class($this);
            $msg = "Could not determine which URL to request: $class instance "
             . "has invalid ID: $id";
            throw new Error\InvalidRequest($msg, null);
        }

        if ($this['customer']) {
            $parent = $this['customer'];
            $base = Customer::classUrl();
            $path = 'sources';
        } elseif ($this['account']) {
            $parent = $this['account'];
            $base = Account::classUrl();
            $path = 'external_accounts';
        } elseif ($this['recipient']) {
            $parent = $this['recipient'];
            $base = Recipient::classUrl();
            $path = 'cards';
        } else {
            return null;
        }

        $parent = Util\Util::utf8($parent);
        $id = Util\Util::utf8($id);

        $parentExtn = urlencode($parent);
        $extn = urlencode($id);
        return "$base/$parentExtn/$path/$extn";
    }

    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    public function verify($params = null, $opts = null)
    {
        if ($this['customer']) {
            $url = $this->instanceUrl() . '/verify';
            list($response, $options) = $this->_request('post', $url, $params, $opts);
            $this->refreshFrom($response, $options);
            return $this;
        } else {
            $message = 'Only customer external accounts can be verified in this manner.';
            throw new Error\Api($message);
        }
    }
}
