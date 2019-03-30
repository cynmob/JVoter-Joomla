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
?>

<h3><?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_HEADER') ?></h3>
<div id="payment-errors" class="alert alert-error" style="display: none;"></div>
<div class="form-horizontal">
	<div class="control-group" id="control-group-card-holder">
		<label for="card-holder" class="control-label" style="width:190px; margin-right:20px; font-weight: bold;">
			<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_CARDHOLDER') ?>
		</label>
		<div class="controls">
			<input type="text" name="card-holder" id="card-holder" class="input-large" value="<?php echo $data->cardholder ?>" />
		</div>
	</div>
	<div class="control-group" id="control-group-card-number">
		<label for="card-number" class="control-label" style="width:190px; margin-right:20px; font-weight: bold;">
			<?php echo JText::_('JDONATE_ADDON_GATEWAY_STRIPE_FORM_CC') ?>
		</label>
		<div class="controls">
			<input type="text" name="card-number" id="card-number" class="input-large" />
		</div>
	</div>
	<div class="control-group" id="control-group-card-expiry">
		<label for="card-expiry" class="control-label" style="width:190px; margin-right:20px; font-weight: bold;">
			<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_EXPDATE') ?>
		</label>
		<div class="controls">
			<?php echo $this->selectMonth() ?><span> / </span><?php echo $this->selectYear() ?>
		</div>
	</div>
	<div class="control-group" id="control-group-card-cvc">
		<label for="card-cvc" class="control-label" style="width:190px; margin-right:20px; font-weight: bold;">
			<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_CVC') ?>
		</label>
		<div class="controls">
			<input type="text" name="card-cvc" id="card-cvc" class="input-mini" />
		</div>
	</div>
</div>
<form id="payment-form" action="<?php echo $data->url ?>" method="post" class="form form-horizontal">
	<input type="hidden" name="currency" id="currency" value="<?php echo $data->currency ?>" />
	<input type="hidden" name="amount" id="amount" value="<?php echo $data->amount ?>" />
	<input type="hidden" name="description" id="description" value="<?php echo $data->description ?>" />
	<input type="hidden" name="token" id="token" />
	<div class="control-group">
		<label for="pay" class="control-label" style="width:190px; margin-right:20px;">
		</label>
		<div class="controls">
			<input type="submit" id="payment-button" class="btn btn-success" value="<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_PAYBUTTON') ?>" />
		</div>
	</div>
</form>
<script type="text/javascript">
try {
	Stripe.setPublishableKey("<?php echo $this->getPublicKey();?>");	
} catch(e) {
	jQuery("#payment-errors").show();
	jQuery("#payment-button").removeAttr("disabled");
	
	if(e.message) jQuery("#payment-errors").text(e.message);
	else jQuery("#payment-errors").text(e);	
}

jQuery(function($)
{
	$("#payment-form").submit(function(e){
		var token = $("#token").val();
		if(!!token) {
			return true;
		}else{
			$("#payment-button").attr("disabled", "disabled");
			
			Stripe.createToken({
				number:$("#card-number").val(),
				exp_month:$("#card-expiry-month").val(),
				exp_year:$("#card-expiry-year").val(),
				cvc:$("#card-cvc").val()
			}, 
				function(status, response)
				{
					$(".control-group").removeClass("error");

					if (response.error) 
					{						
						if(response.error.code == "incorrect_number") {
							$("#control-group-card-number").addClass("error");								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_INCORRECT_NUMBER');?>");	
						}else if(response.error.code == "invalid_number") {
							$("#control-group-card-number").addClass("error");								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_INVALID_NUMBER');?>");						
						}else if(response.error.code == "invalid_expiry_month") {
							$("#control-group-card-expiry").addClass("error");								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_INVALID_EXP_MONTH');?>");									
						}else if(response.error.code == "invalid_expiry_year") {
							$("#control-group-card-expiry").addClass("error");								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_INVALID_EXP_YEAR');?>");								
						}else if(response.error.code == "invalid_cvc") {
							$("#control-group-card-cvc").addClass("error");								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_INVALID_CVC');?>");								
						}else if(response.error.code == "expired_card") {
							$("#control-group-card-expiry").addClass("error");								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_EXPIRED_CARD');?>");							
						}else if(response.error.code == "incorrect_cvc") {
							$("#control-group-card-cvc").addClass("error");								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_INCORRECT_CVC');?>");								
						}else if(response.error.code == "card_declined") {
							$("#control-group-card-number").addClass("error");								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_CARD_DECLINED');?>");								
						}else if(response.error.code == "missing") {								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_MISSING');?>");								
						}else if(response.error.code == "processing_error") {								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_PROCESSING_ERROR');?>");	
						}else if(status == 401) {								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_UNAUTHORIZED');?>");	
						}else if(status == 402) {								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_REQUEST_FAILED');?>");		
						}else if(status == 404) {								
								$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_NOT_FOUND');?>");	
						}else if(status >= 500) {								
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_SERVER_ERROR');?>");								
						}else {
							$("#payment-errors").text("<?php echo JText::_('PLG_JDPAYMENT_STRIPE_FORM_UNKNOWN_ERROR');?>");									
						}

						$("#payment-errors").show();
						$("#payment-button").removeAttr("disabled");
					}
					else 
					{
						$("#payment-errors").hide();
						var token = response.id;
						$("#token").val(token);
						$("#payment-form").submit();
					}
				}
			);
			
			return false;
		}
	});
});
</script>