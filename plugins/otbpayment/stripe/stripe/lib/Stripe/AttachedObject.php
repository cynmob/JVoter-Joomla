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

class AttachedObject extends StripeObject
{
    public function replaceWith($properties)
    {
        $removed = array_diff(array_keys($this->_values), array_keys($properties));
        foreach ($removed as $k) {
            $this->$k = null;
        }

        foreach ($properties as $k => $v) {
            $this->$k = $v;
        }
    }
}
