$(document).ready(function() {

	//display country list
	$('#country_list').on('click', function() {
		$('#countries_div').slideToggle(250);
	});
	$('#countries_div').on('mouseleave', function() {
		$('#countries_div').slideUp(250);
	});
	
	$('.country').on('click', function() {
		$('#country_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		var country = $(this).html();
		var country_id = $(this).attr('id');
		$('#country_list').append('<div id="country">' + country + '</div>');
		$('#country_list').append('<p id="get_country_id" hidden>' + country_id + '</p>');
		$('#countries_div').slideUp(250);
		$('#region_list').html('<div class="region"><img><p>All</p></div><span class="glyphicon glyphicon-menu-down"></span>');
		getJobs(country_id); //change region list and display job offers for all regions in chosen country
	});
	
	//display region list
	$('#region_list').on('click', function() {
		$('#regions_div').slideToggle(250);
	});
	
	$('#regions_div').on('mouseleave', function() {
		$('#regions_div').slideUp(250);
	});
	
	$('#regions_div').on('click', '.region', function() {
		$('#region_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		var region = $(this).html();
		var region_id = $(this).attr('id');
		$('#region_list').append('<div class="region" id="' + region_id + '">' + region + '</div>');
		$('#regions_div').slideUp(250);
		if(region_id == undefined || region_id == 0) {
			var country_id = $('#get_country_id').html();
			getJobs(country_id)
		}
		else {
			getRegionJobs(region_id); //get jobs for chosen region
		}
	});
	
	/* my offers */
	$('#my_offers').on('click', function() {
		var country_id = $('#get_country_id').html();
		getJobs(country_id, my_jobs = true);
	});
	
	//search jobs by experience
	$('#search_job').on('click', function() {
		var region_id = $('#region_list .region').attr('id');
		if(region_id == undefined || region_id == 0) {
			var country_id = $('#get_country_id').html();
			getJobs(country_id)
		}
		else {
			getRegionJobs(region_id); //get jobs for chosen region
		}
	});
	
	//change region list and display job offers for all regions in chosen country
	function getJobs(country_id, my_jobs = false) {
		var skill = $('#skill_input').val();
		var data = new FormData();
		data.append("country_id", country_id);
		data.append("skill", skill);
		data.append("my_jobs", my_jobs);
		data.append('action', "get_jobs");
		var url = "../etc/apply_for_job";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				//display regions
				$('#region_list').html('<div class="region"><p>All</p></div><span class="glyphicon glyphicon-menu-down"></span>');
				$('#regions_div').html('<div class="region" id="0"><p id="' + country_id + '">All</p></div>');
				for(i = 0; i < temp.regions.length; i++) {
					$('#regions_div').append('<div class="region" id="' + temp.regions[i].region_id + '">' +
											 '<p>' + temp.regions[i].region_name + '</p></div>'
											);											
				}

				// display job offers
				$('#job_offers_div').html('<p id="company_name_head">Company Name</p>' +
										  '<p id="location_head">Location</p>' +
										  '<p id="skill_head">Skill</p>' +
										  '<p id="salary_head">Salary</p>');
				for(i = 0; i < temp.jobs.length; i++) {
					$('#job_offers_div').append('<div class="job">' +
												'<a href="user_profile?id=' + temp.jobs[i].employer_id + 
												'" class="user_name">' + temp.jobs[i].employer_name + '</a>' +
												'<img src="../user_images/' + temp.jobs[i].employer_img + '" class="user_img">' +
												'<p class="company_name">' + temp.jobs[i].company_name + '</p>' +
												'<img src="../building_icons/' + temp.jobs[i].building_icon + '" class="company_img">' +
												'<p class="region_name">' + temp.jobs[i].region_name + '</p>' +
												'<p class="bonus">' + temp.jobs[i].skill + '</p>' +
												'<p class="salary">' + numberFormat(temp.jobs[i].salary, 2, '.', ' ') + 
												' ' + temp.jobs[i].currency_abbr + '</p>' +
												'<p class="button blue apply" id="' + temp.jobs[i].job_id + '">Apply</p>' + 
												'</div>'
												);
				}
			}
			else {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
			}
		}
		submitData(data, url, dataReply);
	}
	
	//show jobs for chosen region
	function getRegionJobs(region_id) {
		var skill = $('#skill_input').val();
		var data = new FormData();
		data.append("region_id", region_id);
		data.append("skill", skill);
		data.append('action', "get_region_jobs");
		var url = "../etc/apply_for_job";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				// display job offers
				// display job offers
				$('#job_offers_div').html('<p id="company_name_head">Company Name</p>' +
										  '<p id="location_head">Location</p>' +
										  '<p id="skill_head">Skill</p>' +
										  '<p id="salary_head">Salary</p>');
				for(i = 0; i < temp.jobs.length; i++) {
					$('#job_offers_div').append('<div class="job">' +
												'<a href="user_profile?id=' + temp.jobs[i].employer_id + 
												'" class="user_name">' + temp.jobs[i].employer_name + '</a>' +
												'<img src="../user_images/' + temp.jobs[i].employer_img + '" class="user_img">' +
												'<p class="company_name">' + temp.jobs[i].company_name + '</p>' +
												'<img src="../building_icons/' + temp.jobs[i].building_icon + '" class="company_img">' +
												'<p class="region_name">' + temp.jobs[i].region_name + '</p>' +
												'<p class="bonus">' + temp.jobs[i].skill + '</p>' +
												'<p class="salary">' + numberFormat(temp.jobs[i].salary, 2, '.', ' ') + 
												' ' + temp.jobs[i].currency_abbr + '</p>' +
												'<p class="button blue apply" id="' + temp.jobs[i].job_id + '">Apply</p>' + 
												'</div>'
												);
				}
			}
			else {
				$('#for_popups_pop').empty();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
			}
		}
		submitData(data, url, dataReply);
	}
	
	//apply for a job
	var job_id = '';
	$('#job_offers_div').on('click', '.apply', function() {
		job_id = $(this).attr('id');
		var location = $(this).siblings('.region_name').html();
		var skill = $(this).siblings('.bonus').html();
		var salary = $(this).siblings('.salary').html();
		var data = new FormData();
		data.append("job_id", job_id);
		data.append('action', "get_workers");
		var url = "../etc/apply_for_job";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			$('#reply_info').append('<div id="msg_heading"></div>');
			$('#msg_heading').append('<p id="msg_location">Location: ' + location + '</p>');
			$('#msg_heading').append('<p id="msg_skill">Skill: ' + skill + '</p>');
			$('#msg_heading').append('<p id="msg_salary">Salary: ' + salary + '</p>');
			if(temp.success == true) {
				for(var x = 0; x < temp.workers.length; x++) {
					if(temp.workers[x].worked == true) {
						worked_color_class = 'person_worked';
						work_det = "Worked today";
					}
					else {
						worked_color_class = 'person_can_work';
						work_det = "Did not worked today";
					}
					
					$('#reply_info').append('<div class="about_persons">' +
											'<p class="person_name">' + temp.workers[x].person_name + '</p>' +
											'<span class="glyphicon glyphicon-user person_icon"></span>' +
											'<abbr title="Years"><p class="person_years">' + temp.workers[x].years + '</p></abbr>' +
											'<abbr title="' + work_det + '"><p class="person_worked_stat ' + worked_color_class + 
											'"><i class="fa fa-briefcase" aria-hidden="true"></i></p></abbr>' +
											'<abbr title="Work experience"><p class="person_experience">' + 
											temp.workers[x].experience + '</p></abbr>' +
											'<div class="bar">' +
											'<div class="progress" style="width:' + temp.workers[x].energy + '%;"></div>' +
											'<p>' +temp.workers[x].energy + '%</p> ' + 
											'</div>' +
											'<p class="person_status">' + temp.workers[x].status + '</p>' +
											'<p class="apply_btn">Apply</p>' +
											'<p id="' + temp.workers[x].person_id + '" hidden></p>' +
											'</div>');
				}
			}
			else {
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Cancel</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '.apply_btn',function() {
		var person_id = $(this).next().attr('id');
		var data = new FormData();
		data.append("job_id", job_id);
		data.append("person_id", person_id);
		data.append('action', "apply_for_job");
		var url = "../etc/apply_for_job";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok" id="span_ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				var e = $('#' + job_id).get(0);
				$(e).parent().slideUp();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	$('#for_popups_pop').on('click', '#close', function() { //close details
		 $("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$("#for_popups_pop2").fadeOut(300);
	});
});