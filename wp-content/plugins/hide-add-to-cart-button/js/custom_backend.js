jQuery(document).ready(function($) {	
	$('#woo_disable_add_to_cart_date').datetimepicker();
	var check = $('#woo_disable_add_to_cart_check').attr('checked');
	if (check == 'checked'){
		$('.woo_disable_add_to_cart_date_field').show();
	}else {
		$('.woo_disable_add_to_cart_date_field').hide();
	}
	$('#woo_disable_add_to_cart_check').click(function() {
	$('.woo_disable_add_to_cart_date_field')[this.checked ? "show" : "hide"](500);
	});
});
