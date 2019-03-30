<?php
/**
 * @version    v1.0.0
 * @package    jdonate
 * @author     Jdonate Team <support@jdonate.com>
 * @link       http://www.jdonate.com
 * @copyright  Copyright (C) 2018 Jdonate. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Stripe\HttpClient;

/**
 * ensure this file is being included by a parent file
 */
defined('_JEXEC') or die('Restricted access');

interface ClientInterface
{
    public function request($method, $absUrl, $headers, $params, $hasFile);
}
