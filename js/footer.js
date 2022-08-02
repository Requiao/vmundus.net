$(document).ready(function() {

	//up arrow
	$(window).scroll(function(){
		var scroll = $(window).scrollTop();
		if(scroll >= 100) {
			$('#up').fadeIn('700');
		}
		else if(scroll <= 95){
			$('#up').fadeOut('700');
		}
	});
	
	$('#up').on('click', function() {
		$("html, body").animate({scrollTop: 0 }, 250);
	});
});
