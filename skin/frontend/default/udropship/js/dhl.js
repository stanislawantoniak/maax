var Dhl = {
	button: {},
	messageField: {},
	init:function(url) {
		this.button = jQuery('#dhl_check_button');
		this.messageField = jQuery('#dhl_check_message'); 
		this.button.click(function() {
			Dhl.checkDhl(url)
		});
	},
	disable: function() {
		this.button.attr('disabled',true);
	},
	enable: function() {
		this.button.attr('disabled',false);
	},
	checkDhl: function(url) {
		Dhl.disable();
		jQuery.ajax({
			dataType: 'json',
			url: url,
			success: Dhl.success,
			error: Dhl.error,
		});
	},
	setInfo: function(color,mess) {
		this.messageField.html('<span style="color:'+color+'">'+mess+'</span>');
	},
	success: function(data) {
		Dhl.setInfo(data.color,data.value);
		Dhl.enable();
	},
	error: function() {
		Dhl.setInfo('red',Translator.translate('Unknown error. Please reload page.'));
		Dhl.enable();
	}
};

