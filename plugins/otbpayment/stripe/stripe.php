<?php
/**
 * @version    v1.0.0
 * @package    jdonate
 * @author     Jdonate Team <support@jdonate.com>
 * @link       http://www.jdonate.com
 * @copyright  Copyright (C) 2018 Jdonate. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * ensure this file is being included by a parent file
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

JLoader::register('JdPayment', JPATH_ADMINISTRATOR . '/components/com_jdonate/helpers/payment.php');

class plgJdPaymentStripe extends JdPayment
{

	public function __construct (&$subject, $config = array())
	{
		$config = array_merge($config,
				array(
						'ppName' => 'stripe',
						'ppKey' => 'PLG_JDPAYMENT_STRIPE_TITLE',
						'ppImage' => rtrim(JURI::base(), '/') . '/media/com_jdonate/images/payment/stripe.png'
				));
		
		parent::__construct($subject, $config);
		
		//\JFactory::getDocument()->addScript('https://js.stripe.com/v2/');
		require_once dirname(__FILE__) . '/stripe/lib/Stripe.php';
	}

	/**
	 * Returns the payment form to be submitted by the user's browser.
	 * The form must have an ID of
	 * "paymentForm" and a visible submit button.
	 *
	 * @param string $paymentmethod
	 *        	The currently used payment method. Check it against
	 *        	$this->ppName.
	 * @param
	 *        	$campaignId
	 * @param
	 *        	$dataAjax
	 *        	
	 * @return string The payment form to render on the page. Use the special id
	 *         'paymentForm' to have it
	 *         automatically submitted after 5 seconds.
	 */
	public function onJDPaymentNew ($paymentmethod, $campaignId, $dataAjax)
	{
		if ($paymentmethod != $this->ppName)
			return false;
			
		$user = \JFactory::getUser();
		$params = JComponentHelper::getParams('com_jdonate');
		
		// get the params from the campaign and merge with the plugin params
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table = JTable::getInstance('Campaign', 'JdonateTable');
		$table->load($campaignId);
		
		// merge campaign params with the plugin params
		$registry = new Registry($table->params);
		$this->params->merge($registry);
		
		$amount = 0;
		$name = '';
		$url_string = '&campaignid=' . $campaignId;
		foreach ($dataAjax as $item)
		{
			$amount = $amount + $item['amount'];
			$url_string = '&did[]=' . $item['id'];
			if (empty($name))
			{
				$name = $item['name'];
			}
		}
		
		$callbackUrl = JURI::base() . 'index.php?option=com_jdonate&task=campaign.callback&paymentmethod=stripe' . $url_string;
		$data = (object) array(
				'url' => $callbackUrl,
				'amount' => (int) ($amount * 100),
				'currency' => strtolower($params->get('currency', 'USD')),
				'description' => $table->title . ' #' . $campaignId,
				'cardholder' => empty($name) ? $user->name : $name
		);
		
		@ob_start();
		$layout = $this->getLayoutPath('form');
		include ($layout);
		$html = @ob_get_clean();
		
		return $html;
	}

	public function onJdPaymentCallback ($paymentmethod, $data)
	{
		JLoader::import('joomla.utilities.date');
		
		// Check if we're supposed to handle this
		if ($paymentmethod != $this->ppName)
			return false;
		
		$isValid = true;
		
		$donations = null;
		$amount = 0; // donation amount
		$campaignId = null;
		
		// Load the relevant subscription row
		$donationIds = $data['did'];
		
		if (is_array($donationIds) && count($donationIds))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__jdonate_donations'));
			
			$donationIds = ArrayHelper::toInteger($donationIds);
			$donationIds = implode(',', $donationIds);
			
			$query->where($db->qn('id') . ' IN (' . $donationIds . ')');
			$donations = $db->setQuery($query)->loadObjectList();
			
			foreach ($donations as $donation)
			{
				$campaignId = $donation->campaign_id;
				$amount = $amount + $donation->amount;				
			}			
		}
		else
		{
			$isValid = false;
		}
		
		if (! $isValid)
			$data['jdonate_failure_reason'] = 'The donations IDs are invalid';
		
		if ($isValid)
		{
			try
			{
				$apiKey = $this->getPrivateKey();
				
				\Stripe\Stripe::setApiKey($apiKey);
				
				$charge = \Stripe\Charge::create(
						array(
								'amount' => $data['amount'],
								'currency' => $data['currency'],
								'card' => $data['token'],
								'description' => $data['description']
						));
			}
			catch (Exception $e)
			{
				$isValid = false;
				$data['jdonate_failure_reason'] = $e->getMessage();
			}
		}
		
		if ($isValid && ! empty($charge->failure_message))
		{
			$isValid = false;
			$data['jdonate_failure_reason'] = "Stripe failure: " . $charge->failure_message;
		}			
		
		// Check that transaction has not been previously processed
		if ($isValid)
		{
			foreach ($donations as $donation)
			{								
				if ($charge->id == $donation->transaction_id)
				{
					$isValid = false;
					$data['jdonate_failure_reason'] = "I will not process Donation #{$donation->id} transaction twice";
				}
			}
		}
		
		// Check that amount is correct
		if ($isValid && ! is_null($donations))
		{
			$mc_gross = $charge->amount;
			$gross = (int) ($amount * 100);
			if ($mc_gross > 0)
			{
				// A positive value means "payment". The prices MUST match!
				// Important: NEVER, EVER compare two floating point values for
				// equality.
				$isValid = ($gross - $mc_gross) < 0.01;
			}
			
			if (! $isValid)
				$data['jdonate_failure_reason'] = 'Paid amount does not match the donation amount';
		}
		
		if ($isValid)
		{
			if ($data['currency'] != strtolower($charge->currency))
			{
				$isValid = false;
				$data['jdonate_failure_reason'] = "Currency doesn't match.";
			}
		}
		
		$sandbox = $this->params->get('sandbox');
		if ($isValid)
		{
			if ($sandbox == $charge->livemode)
			{
				$isValid = false;
				$data['jdonate_failure_reason'] = "Transaction done in wrong mode.";
			}
		}		
		
		// Log the IPN data		
		$this->logIPN($charge->__toArray(), $isValid);
		
		// Fraud attempt? Do nothing more!
		if (! $isValid)
		{
			JLog::add("Stripe Failure: {$data['jdonate_failure_reason']}", JLog::ERROR, 'jdonate.payment');
			
			$error_url = 'index.php?option=com_jdonate&view=campaign&id=' . $campaignId;
			$error_url = JRoute::_($error_url, false);
			\JFactory::getApplication()->redirect($error_url, $data['jdonate_failure_reason'], 'error');
			return false;
		}
		// Payment status
		$newStatus = $charge->paid ? 'completed' : 'canceled';
				
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table = JTable::getInstance('Donation', 'JdonateTable');
		foreach ($donations as $donation)
		{
			$table->reset();
			$table->load($donation->id);
			
			$table->status = strtolower($newStatus);
			$table->transaction_id = $charge->id;
			$table->transaction_params = json_encode($charge);
			
			if (! $table->store())
			{
				return false;
			}
		}
		
		// Redirect the user to the "thank you" page
		$thankyouUrl = JRoute::_('index.php?option=com_jdonate&task=message.thankyou&campaignid=' . $campaignId, false);
		\JFactory::getApplication()->redirect($thankyouUrl);
		return true;
	}

	private function getPublicKey ()
	{
		$sandbox = $this->params->get('sandbox', 0);
		
		return $sandbox ? trim($this->params->get('sb_public_key', '')) : trim($this->params->get('stripe_public_key', ''));
	}

	private function getPrivateKey ()
	{
		$sandbox = $this->params->get('sandbox', 0);
		
		return $sandbox ? trim($this->params->get('sb_private_key', '')) : trim($this->params->get('stripe_private_key', ''));
	}

	public function selectMonth ()
	{
		$options = array();
		$options[] = JHTML::_('select.option', 0, '--');
		for ($i = 1; $i <= 12; $i ++)
		{
			$m = sprintf('%02u', $i);
			$options[] = JHTML::_('select.option', $m, $m);
		}
		
		return JHTML::_('select.genericlist', $options, 'card-expiry-month', 'class="input-small"', 'value', 'text', '', 'card-expiry-month');
	}

	public function selectYear ()
	{
		$year = gmdate('Y');
		
		$options = array();
		$options[] = JHTML::_('select.option', 0, '--');
		for ($i = 0; $i <= 10; $i ++)
		{
			$y = sprintf('%04u', $i + $year);
			$options[] = JHTML::_('select.option', $y, $y);
		}
		
		return JHTML::_('select.genericlist', $options, 'card-expiry-year', 'class="input-small"', 'value', 'text', '', 'card-expiry-year');
	}
}