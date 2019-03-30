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

class Collection extends ApiResource
{
    protected $_requestParams = array();

    public function setRequestParams($params)
    {
        $this->_requestParams = $params;
    }

    public function all($params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        list($response, $opts) = $this->_request('get', $url, $params, $opts);
        $this->_requestParams = $params;
        return Util\Util::convertToStripeObject($response, $opts);
    }

    public function create($params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->_requestParams = $params;
        return Util\Util::convertToStripeObject($response, $opts);
    }

    public function retrieve($id, $params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        $id = Util\Util::utf8($id);
        $extn = urlencode($id);
        list($response, $opts) = $this->_request(
            'get',
            "$url/$extn",
            $params,
            $opts
        );
        $this->_requestParams = $params;
        return Util\Util::convertToStripeObject($response, $opts);
    }

    public function autoPagingIterator()
    {
        return new Util\AutoPagingIterator($this, $this->_requestParams);
    }

    private function extractPathAndUpdateParams($params)
    {
        $url = parse_url($this->url);
        if (!isset($url['path'])) {
            throw new Error\Api("Could not parse list url into parts: $url");
        }

        if (isset($url['query'])) {
            $query = array();
            parse_str($url['query'], $query);
            $params = array_merge($params ? $params : array(), $query);
        }

        return array($url['path'], $params);
    }
}
