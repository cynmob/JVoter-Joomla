<?php
/**
 * @package    JVoter
 * @subpackage 
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

//TODO: transfer the helper the the joomla /library path
JLoader::register('OTBPayment', JPATH_ADMINISTRATOR . '/components/com_jdonate/helpers/payment.php');

class plgOTBPaymentPaypal extends OTBPayment
{

	public function __construct (&$subject, $config = array())
	{
		$config = array_merge($config,
				array(
						'ppName' => 'paypal',
						'ppKey' => 'PLG_OTBPAYMENT_PAYPAL_TITLE',
						'ppImage' => rtrim(JURI::base(), '/') . '/media/obeythebeagle/images/payment/paypal.png'
				));
		
		parent::__construct($subject, $config);
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
	 *        	$campaign_id
	 * @param
	 *        	$dataAjax
	 *        	
	 * @return string The payment form to render on the page. Use the special id
	 *         'paymentForm' to have it
	 *         automatically submitted after 5 seconds.
	 */
	public function onOTBPaymentNew ($paymentmethod, $campaign_id, $dataAjax)
	{
		if ($paymentmethod != $this->ppName)
		{
			return false;
		}
		
		// get the params from the campaign and merge with the plugin params
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table = JTable::getInstance('Campaign', 'JdonateTable');
		$table->load($campaign_id);
		
		$registry = new Registry($table->params);
		$this->params->merge($registry);
		
		$user = JFactory::getUser();
		$firstName = '';
		$lastName = '';
		if ($user->id)
		{
			$nameParts = explode(' ', $user->name, 2);
			$firstName = $nameParts[0];
			
			if (count($nameParts) > 1)
			{
				$lastName = $nameParts[1];
			}
		}
		
		$params = JComponentHelper::getParams('com_jdonate');
		
		$rootURL = rtrim(JUri::base(), '/');
		$subpathURL = JUri::base(true);
		
		if (! empty($subpathURL) && ($subpathURL != '/'))
		{
			$rootURL = substr($rootURL, 0, - 1 * strlen($subpathURL));
		}
		
		$data = (object) array(
				'url' => $this->getPaymentURL(),
				'merchant' => $this->getMerchantID(),
				'postback' => $this->getPostbackURL(),
				'success' => $rootURL .
				str_replace('&amp;', '&', JRoute::_('index.php?option=com_jdonate&task=message.thankyou&campaignid=' . $campaign_id)),
				'cancel' => $rootURL .
				str_replace('&amp;', '&', JRoute::_('index.php?option=com_jdonate&task=message.cancel&campaignid=' . $campaign_id)),
				'currency' => strtoupper($params->get('currency', 'USD')),
				'firstname' => $firstName,
				'lastname' => $lastName,
				'cmd' => '_donations'
		);
		
		@ob_start();
		$layout = $this->getLayoutPath('form');
		include ($layout);
		$html = @ob_get_clean();
		
		return $html;
	}

	/**
	 * Processes a callback from the payment processor
	 *
	 * @param string $paymentmethod
	 *        	The currently used payment method. Check it against
	 *        	$this->ppName
	 * @param array $data
	 *        	Input (request) data
	 *        	
	 * @return boolean True if the callback was handled, false otherwise
	 */
	public function onJdPaymentCallback ($paymentmethod, $data)
	{
		JLoader::import('joomla.utilities.date');
		
		// Check if we're supposed to handle this
		if ($paymentmethod != $this->ppName)
		{
			return false;
		}
		
		$isValid = false;
		
		if ($this->params->get('debug', 0))
		{
			JLog::add("PayPal: Debug mode enabled.", JLog::DEBUG, 'jdonate.payment');
			$isValid = true;
		}
		
		// Check IPN data for validity (i.e. protect against fraud attempt)
		try
		{
			if (! $isValid)
			{
				$isValid = $this->isValidIPN($data);
				JLog::add(sprintf("PayPal: PayPal responds that the IPN is %s", $isValid ? 'valid' : 'INVALID'), JLog::DEBUG, 'jdonate.payment');
			}
		}
		catch (RuntimeException $e)
		{
			$isValid = false;
			$data['jdonate_ipn_failure_reason'] = $e->getMessage();
		}
		
		try
		{
			$this->checkIPNPostbackRequirements();
		}
		catch (RuntimeException $e)
		{
			JLog::add("PayPal: IPN postback requirements are not met.", JLog::ERROR, 'jdonate.payment');
			
			$data['jdonate_ipn_warning'] = $e->getMessage();
		}
					
		if (! $isValid)
		{
			$data['jdonate_failure_reason'] = 'PayPal reports transaction as invalid';
		}
		
		// Check txn_type; we only accept web_accept transactions with this		
		if ($isValid)
		{
			// This is required to process some IPNs, such as Reversed and
			// Canceled_Reversal
			if (! array_key_exists('txn_type', $data))
			{
				$data['txn_type'] = 'workaround_to_missing_txn_type';
			}
			
			$validTypes = array(
					'workaround_to_missing_txn_type',
					'web_accept'
			);
			
			$isValid = in_array($data['txn_type'], $validTypes);
			
			if (! $isValid)
			{
				JLog::add(sprintf("PayPal: Transaction type “%s” cannot be processed.", $data['txn_type']), JLog::ERROR, 'jdonate.payment');
				
				$data['jdonate_failure_reason'] = "Transaction type " . $data['txn_type'] . " can't be processed by this payment plugin.";
			}
		}
	
		// Load the relevant donations
		if ($isValid)
		{			
			// ids here are campaign ids
			$donationId = array_key_exists('custom', $data) ? $data['custom'] : '';
					
			$donations = null;
			JLog::add("PayPal: custom {$donationId}", JLog::DEBUG, 'jdonate.payment');
			if ($donationId)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__jdonate_donations'));
				
				if (strpos($donationId, ',') !== false)
				{
					$donationId = explode(',', $donationId);					
					$donationId = ArrayHelper::toInteger($donationId);
					$donationId = implode(',', $donationId);
					$query->where($db->qn('id') . ' IN (' . $donationId . ')');
				}
				else
				{
					$query->where($db->qn('id') . ' = ' . (int) $donationId);
				}
				
				$donations = $db->setQuery($query)->loadObjectList();
				
				if (! $donations)
				{
					$donations = null;
					$isValid = false;
				}
			}
			else
			{
				JLog::add(sprintf("PayPal: Cannot find donations %u", $donationId), JLog::ERROR, 'jdonate.payment');
				
				$isValid = false;
			}
			
			/** @var Subscriptions $subscription */
			
			if (! $isValid)
			{
				$data['jdonate_failure_reason'] = 'The referenced donations ID ("custom" field) is invalid';
			}
		}
		
		/** @var Subscriptions $subscription */
		
		// Check that receiver_email / receiver_id is what the site owner has
		// configured
		if ($isValid)
		{
			$receiver_email = $data['receiver_email'];
			$receiver_id = $data['receiver_id'];
			$valid_id = $this->getMerchantID();
			$isValid = ($receiver_email == $valid_id) || (strtolower($receiver_email) == strtolower($receiver_email)) || ($receiver_id == $valid_id) ||
					(strtolower($receiver_id) == strtolower($receiver_id));
			
			if (! $isValid)
			{
				JLog::add("PayPal: Merchant ID is not valid", JLog::ERROR, 'jdonate.payment');
				
				$data['jdonate_failure_reason'] = 'Merchant ID does not match receiver_email or receiver_id';
			}
		}
		
		// Check that mc_gross is correct
		$isPartialRefund = false;
		
		if ($isValid)
		{
			$mc_gross = floatval($data['mc_gross']);
			$gross = 0;
			foreach ($donations as $donation)
			{
				$gross = $gross + $donation->amount;
			}
			
			$isValid = ($gross - $mc_gross) < 0.01;
			
			if (! $isValid)
			{
				JLog::add("PayPal: Paid amount does not match the susbcription amount", JLog::ERROR, 'jdonate.payment');
				$data['jdonate_failure_reason'] = "Amounts do not match. Expect $mc_gross, but get $gross.";
			}
		}
			
		// Check that mc_currency is correct
		if ($isValid && ! is_null($donations))
		{
			$params = JComponentHelper::getParams('com_jdonate');
			$mc_currency = strtoupper($data['mc_currency']);
			$currency = strtoupper($params->get('currency', 'USD'));
			
			if ($mc_currency != $currency)
			{
				$isValid = false;
				$data['jdonate_failure_reason'] = "Invalid currency; expected $currency, got $mc_currency";
				
				JLog::add("PayPal: Invalid currency; expected $currency, got $mc_currency", JLog::ERROR, 'jdonate.payment');
			}
		}
		
		// Log the IPN data
		$this->logIPN($data, $isValid);
		
		// Fraud attempt? Do nothing more!
		if (! $isValid)
		{
			return false;
		}
		/*
		 *
		 * Canceled_Reversal: A reversal has been canceled. For example, you won
		 * a dispute with the customer, and the funds for the transaction that
		 * was reversed have been returned to you.
		 * Completed: The payment has been completed, and the funds have been
		 * added successfully to your account balance.
		 * Created: A German ELV payment is made using Express Checkout.
		 * Denied: You denied the payment. This happens only if the payment was
		 * previously pending because of possible reasons described for the
		 * pending_reason variable or the Fraud_Management_Filters_x variable.
		 * Expired: This authorization has expired and cannot be captured.
		 * Failed: The payment has failed. This happens only if the payment was
		 * made from your customer’s bank account.
		 * Pending: The payment is pending. See pending_reason for more
		 * information.
		 * Refunded: You refunded the payment.
		 * Reversed: A payment was reversed due to a chargeback or other type of
		 * reversal. The funds have been removed from your account balance and
		 * returned to the buyer. The reason for the reversal is specified in
		 * the ReasonCode element.
		 * Processed: A payment has been accepted.
		 * Voided: This authorization has been voided.
		 */
		
		// Check the payment_status
		switch ($data['payment_status'])
		{
			case 'Canceled_Reversal':
			case 'Completed':
				$newStatus = 'completed';
				break;
			case 'Created':
			case 'Pending':
			case 'Processed':
				$newStatus = 'pending';
				break;
			case 'Denied':
				$newStatus = 'denied';
				break;
			case 'Refunded':
				$newStatus = 'refunded';
				break;
			case 'Expired':
			case 'Failed':
			case 'Reversed':
			case 'Voided':
			default:
				$newStatus = 'canceled';
				break;
		}
		
		// Save the changes
		JLog::add("PayPal: Saving donation updates", JLog::DEBUG, 'jdonate.payment');
		
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table = JTable::getInstance('Donation', 'JdonateTable');
		foreach ($donations as $donation)
		{
			$table->reset();
			$table->load($donation->id);
			
			$table->name = $data['first_name'] . ' ' . $data['last_name'];
			$table->email = $data['payer_email'];
			$table->country_code = $data['residence_country'];
			$table->status = strtolower($newStatus);
			$table->transaction_id = $data['txn_id'];
			$table->transaction_params = json_encode($data);
			
			if (! $table->store())
			{
				// Save the changes
				JLog::add("PayPal: Error saving donation with ID({$donation->id})." . $table->getError(), JLog::DEBUG, 'jdonate.payment');
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Gets the form action URL for the payment
	 */
	private function getPaymentURL ()
	{
		$sandbox = $this->params->get('sandbox', 0);
		
		return $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
	}

	/**
	 * Gets the PayPal Merchant ID (usually the email address)
	 */
	private function getMerchantID ()
	{
		$sandbox = $this->params->get('sandbox', 0);
		
		// TODO: update code with regards to the component version for free and
		// c
		return $sandbox ? $this->params->get('sandbox_merchant', '') : $this->params->get('paypal_merchant', '');
	}

	/**
	 * Creates the callback URL based on the plugins configuration.
	 */
	private function getPostbackURL ()
	{
		$url = JURI::base() . 'index.php?option=com_jdonate&task=campaign.callback&paymentmethod=paypal';
		
		$configurationValue = $this->params->get('protocol', 'keep');
		$pattern = '/https?:\/\//';
		
		if ($configurationValue == 'secure')
		{
			$url = preg_replace($pattern, "https://", $url);
		}
		
		if ($configurationValue == 'insecure')
		{
			$url = preg_replace($pattern, "http://", $url);
		}
		
		return $url;
	}

	/**
	 * Validates the incoming data against PayPal's IPN to make sure this is not
	 * a fraudulent request.
	 *
	 * @see https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNImplementation/#specs
	 * @see https://github.com/paypal/ipn-code-samples/blob/master/php/PaypalIPN.php
	 */
	private function isValidIPN (&$data)
	{
		$url = 'https://ipnpb.paypal.com/cgi-bin/webscr';
		
		if ($this->params->get('sandbox', 0))
		{
			$url = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
		}
		
		$newData = array(
				'cmd' => '_notify-validate'
		);
		$newData = array_merge($newData, $data);
		
		$options = [
				CURLOPT_SSLVERSION => 6,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_VERBOSE => false,
				CURLOPT_HEADER => false,
				CURLINFO_HEADER_OUT => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CAINFO => JPATH_LIBRARIES . '/src/Http/Transport/cacert.pem',
				CURLOPT_HTTPHEADER => [
						'User-Agent: JdonateDonations',
						'Connection: Close'
				],
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $newData,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CONNECTTIMEOUT => 30,
				CURLOPT_FORBID_REUSE => true,
				// Force the use of TLS (therefore SSLv3 is not used, mitigating
				// POODLE; see https://github.com/paypal/merchant-sdk-php)
				CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
				// This forces the use of TLS 1.x
				CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1
		];
		
		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		
		$response = curl_exec($ch);
		$errNo = curl_errno($ch);
		$error = curl_error($ch);
		$lastHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		if (($errNo > 0) && ! empty($error))
		{
			throw new RuntimeException("Could not open connection to $url, cURL error $errNo: $error");
		}
		
		if ($lastHttpCode >= 400)
		{
			throw new RuntimeException("Invalid HTTP status $lastHttpCode verifying PayPal's IPN");
		}
		
		if (stristr($response, "INVALID"))
		{
			throw new RuntimeException('PayPal claims the IPN data is INVALID – Possible fraud!');
		}
		
		if (stristr($response, "VERIFIED"))
		{
			return true;
		}
		
		throw new RuntimeException("Unknown PayPal response. HTTP $lastHttpCode. Message: $response");
	}

	/**
	 * Checks if the server meets the minimum PayPal IPN postback requirements.
	 * If not a RuntimeException is thrown.
	 *
	 * @return void
	 *
	 * @throws RuntimeException
	 */
	protected function checkIPNPostbackRequirements ()
	{
		// TLS 1.2 is only supported in OpenSSL 1.0.1c and later AND cURL 7.34.0
		// and later running on PHP 5.5.19+ or
		// PHP 5.6.3+. If these conditions are met we can use PayPal's minimum
		// requirement of TLS 1.2 which is mandatory
		// since June 2016.
		$curlVersionInfo = curl_version();
		$curlVersion = $curlVersionInfo['version'];
		$openSSLVersionRaw = $curlVersionInfo['ssl_version'];
		// OpenSSL version typically reported as "OpenSSL/1.0.1e", I need to
		// convert it to 1.0.1.5
		$parts = explode('/', $openSSLVersionRaw, 2);
		$openSSLVersionRaw = (count($parts) > 1) ? $parts[1] : $openSSLVersionRaw;
		$openSSLVersion = substr($openSSLVersionRaw, 0, - 1) . '.' . (ord(substr($openSSLVersionRaw, - 1)) - 96);
		// PHP version required for TLS 1.2 is 5.5.19+ or 5.6.3+
		$minPHPVersion = version_compare(PHP_VERSION, '5.6.0', 'ge') ? '5.6.3' : '5.5.19';
		
		if (! version_compare($curlVersion, '7.34.0', 'ge') || ! version_compare($openSSLVersion, '1.0.1.3', 'ge') ||
				! version_compare(PHP_VERSION, $minPHPVersion, 'ge'))
		{
			$phpVersion = PHP_VERSION;
			
			throw new RuntimeException(
					"WARNING! PayPal demands that connections be made with TLS 1.2. This requires PHP $minPHPVersion+ (you have $phpVersion), libcurl 7.34.0+ (you have $curlVersion) and OpenSSL 1.0.1c+ (you have $openSSLVersionRaw) on your server's PHP. Please upgrade these requirements to meet the stated minimum or the PayPal integration will cease working.");
		}
	}
}