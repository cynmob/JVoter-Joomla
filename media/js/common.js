jQuery(document).ready(function ($) {
	
	$(document).on('click', '.moreless', function(e){		
		
		if($(this).parent().find('.hidden-truncate').css('display') == 'none') {
			$(this).parent().find('.hidden-truncate').show();
			$(this).text(Joomla.JText._('COM_JDONATE_SHOW_LESS')).toggleClass('badge-info');
		}else {
			$(this).parent().find('.hidden-truncate').hide();
			$(this).text(Joomla.JText._('COM_JDONATE_SHOW_MORE')).toggleClass('badge-info');
		}		
    });	
	
});