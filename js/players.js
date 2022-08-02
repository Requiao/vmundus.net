$(document).ready( function() {
	//display country list
	var country_flag = 0;
	$('#country_list').on('click', function() {
		
		if(country_flag == 0) {
			$('#countries_div').slideDown(250);
			country_flag = 1;
		}
		else {
			$('#countries_div').slideUp(250);
			country_flag = 0;
		}
	});
	$('#countries_div').on('mouseleave', function() {
		$('#countries_div').slideUp(250);
		country_flag = 0;
	});
	
	$('.country').on('click', function() {
		$('#country_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		var country = $(this).html();
		var country_id = $(this).attr('id');
		$('#country_list').append('<div id="country">' + country + '</div>');
		$('#country_list').append('<p id="get_country_id" hidden>' + country_id + '</p>');
		$('#region_list').html('<div class="region"><img><p>All</p></div><span class="glyphicon glyphicon-menu-down"></span>');
		$('#countries_div').slideUp(250);
		country_flag = 0;
	});
	
	$('#find_players').on('click', function() {	
		var country_id = $('#get_country_id').html();
		var from_days = $('#from_days').val() ? $('#from_days').val() : 0;
		var to_days = $('#to_days').val() ? $('#to_days').val() : 99999;
		var player_name = $('#player_name').val();
		console.log(player_name);
		var form_data = new FormData();
		form_data.append('country_id', country_id);
		form_data.append('from_days', from_days);
		form_data.append('to_days', to_days);
		form_data.append('player_name', player_name);
		var url = "../etc/find_players";
		function displayPlayers(reply) {
			$('#player_list').empty();
			var temp = JSON.parse(reply);
			$('#player_list').append('<div id="pl_headings">' + 
									 '<p id="plh_name">User Name</p>' +
									 '<p id="plh_citizenship">Citizenship</p>' +
									 '<p id="plh_days">Days in game</p>' +
									 '</div>')
			for(var x = 0; x < temp.length; x++) {
				$('#player_list').append('<div class="player_info">' + 
										 '<img class="user_image" src="../user_images/' + temp[x].user_image + '">' + 
										 '<p class="name">' + temp[x].user_name + '</p>' +
										 '<img class="pi_flag" src="../country_flags/' + temp[x].flag + '">' + 
										 '<p class="days_in_game">' + temp[x].days_in_game + '</p>' +
										 '<a class="button blue view" href="user_profile?id=' + temp[x].user_id +
										 '" target="_blank">View</a>' +
										 '</div>')
			}
		}
		submitData(form_data, url, displayPlayers);
	});
})