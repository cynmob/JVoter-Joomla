<?php
/**
 * @version    v1.0.0
 * @package    jdonate
 * @author     Jdonate Team <support@jdonate.com>
 * @link       http://www.jdonate.com
 * @copyright  Copyright (C) 2018 Jdonate. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Stripe\Error;

/**
 * ensure this file is being included by a parent file
 */
defined('_JEXEC') or die('Restricted access');

class Card extends Base
{
    public function __construct(
        $message,
        $stripeParam,
        $stripeCode,
        $httpStatus,
        $httpBody,
        $jsonBody,
        $httpHeaders = null
    ) {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders);
        $this->stripeParam = $stripeParam;
        $this->stripeCode = $stripeCode;

        $this->declineCode = isset($jsonBody["error"]["decline_code"]) ? $jsonBody["error"]["decline_code"] : null;
    }

    public function getDeclineCode()
    {
        return $this->declineCode;
    }

    public function getStripeCode()
    {
        return $this->stripeCode;
    }

    public function getStripeParam()
    {
        return $this->stripeParam;
    }
}
