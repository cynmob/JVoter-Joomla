<?php
/**
 * @package    plg_otbpayment_paypal
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

class plgOTBPaymentPaypal extends JPlugin
{

    /**
     * Constructor
     *
     * @param string &$subject subject
     *            
     * @param string $config
     *            config
     */
    public function __construct(&$subject, $config = array())
    {
        $config = array_merge($config, array(
            'otbpName' => 'paypal'
        ));
        
        parent::__construct($subject, $config);
    }
    
    public function onOtbPaymentNew()
    {
        
    }

    /**
     * Process the payment
     */
    public function onPaymentProcess()
    {}
    
    /**
     * Processes a callback from the payment processor
     *
     * @return boolean True if the callback was handled, false otherwise
     */
    public function onPaymentCallback()
    {}
}