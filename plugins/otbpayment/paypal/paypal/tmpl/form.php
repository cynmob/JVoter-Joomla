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

JLoader::register('JdonateHelper', JPATH_ADMINISTRATOR . '/components/com_jdonate/helpers/jdonate.php');
JLoader::registerAlias('JDHelper', 'JdonateHelper');

$ids = array();
$amount = 0;
$i =1;
foreach($dataAjax as $key => $item)
{
	$amount = $amount + $item['amount'];
	$ids[] = $item['id'];
	$i++;
}
?>
<div class="text-center form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<label><?php echo JText::_('PLG_JDPAYMENT_PAYPAL_TOTAL_DONATION_AMOUNT');?></label>
		</div>
		<div class="controls">			
			<input type="text" value="<?php echo JDHelper::formatAmount($amount);?>" class="disabled" disabled="disabled">
		</div>
	</div>
	<div class="alert alert-info">
    	<button type="button" class="close" data-dismiss="alert">&times;</button>        	
       	<div class="alert-message"><?php echo JText::_('PLG_JDPAYMENT_PAYPAL_REDIRECT_PAYPAL_SITE');?></div>
    </div>
<form action="<?php echo $data->url ?>"  method="post" id="paymentForm">
	<input type="hidden" name="cmd" value="<?php echo $data->cmd ?>" />
	<input type="hidden" name="business" value="<?php echo $data->merchant ?>" />
	<input type="hidden" name="return" value="<?php echo $data->success ?>" />
	<input type="hidden" name="cancel_return" value="<?php echo $data->cancel ?>" />
	<input type="hidden" name="notify_url" value="<?php echo $data->postback ?>" />
	
	<input type='hidden' name="amount" value="<?php echo$amount;?>">
	<input type="hidden" name="item_name" value="<?php echo JText::sprintf('PLG_JDPAYMENT_PAYPAL_DONATION_TITLE', $table->title);?>" />
	<input type="hidden" name="custom" value="<?php echo implode(',', $ids); ?>" />
		   
	<input type="hidden" name="currency_code" value="<?php echo $data->currency ?>" />
	<input type="hidden" name="first_name" value="<?php echo $data->firstname ?>" />
	<input type="hidden" name="last_name" value="<?php echo $data->lastname ?>" />

	<?php // Remove the following line if PayPal doing POST to your site causes a problem ?>
	<input type="hidden" name="rm" value="2">

	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="no_shipping" value="1" />
	<?php if($cbt = $this->params->get('cbt','')): ?>
	<input type="hidden" name="cbt" value="<?php echo $cbt ?>" />
	<?php endif; ?>
	<?php if($cpp_header_image = $this->params->get('cpp_header_image','')): ?>
	<input type="hidden" name="cpp_header_image" value="<?php echo $cpp_header_image?>" />
	<?php endif; ?>
	<?php if($cpp_headerback_color = $this->params->get('cpp_headerback_color','')): ?>
	<input type="hidden" name="cpp_headerback_color" value="<?php echo $cpp_headerback_color?>" />
	<?php endif; ?>
	<?php if($cpp_headerborder_color = $this->params->get('cpp_headerborder_color','')): ?>
	<input type="hidden" name="cpp_headerborder_color" value="<?php echo $cpp_headerborder_color?>" />
	<?php endif; ?>
	
	<div class="control-group">		
		<div class="controls">			
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" id="paypalsubmit" />
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</div>
	</div>
</form>
</div>