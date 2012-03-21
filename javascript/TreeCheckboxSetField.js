(function($) {
	$(document).ready(function() {
		$("ul.treecheckboxsetfield li.root input.root").live("click", function(){
			if($(this).attr('checked')){
				$(this).nextAll('ul').find('li.leaf input').each(function(e){
					$(this).attr({checked:false,disabled:true});
				});
			}else{
				$(this).nextAll('ul').find('li.leaf input').each(function(e){
					$(this).attr({disabled:false});
				});
			}
		});
	});
})(jQuery);