(function($) {
	$(document).ready(function() {	
		$("form#sort_options_form a.active_sumbit").live("click", function(){
			document.sort_options_form.submit();
		});	
	});
})(jQuery);