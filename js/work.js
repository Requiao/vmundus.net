$(document).ready(function() {
	
	/* retire from job */
	$('.retire').on('click', function() {
		var person_id = $(this).next().html();
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want this Person to quit this job?</p>');
		$('#reply_info').append('<p class="button red" id="retire_yes">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$('#reply_info').append('<p id="temp_person_id" hidden>' + person_id + '</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#retire_yes', function() {
		var person_id = $('#temp_person_id').html();
		var data = new FormData();
		data.append('person_id', person_id);
		var url = "../etc/retire_from_job";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				if(temp.summary[0].success == true) {
					$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
					$('#reply_info').append('<p class="wsd_msg">' + temp.summary[0].msg + '</p>');
					$('#p_' + temp.summary[0].person_id).parent().slideUp(250);
					$('#p_' + temp.summary[0].person_id).parent().find('.r_people_id_check').remove();
				}
				else {
					$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
					$('#reply_info').append('<p class="wsd_msg">' + temp.summary[0].error + '</p>');
				}
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
	
	//retire all
	$('#runcheck_box').on('change', function () {
		if ($(this).prop('checked') == true) {
            $('.r_people_id_check').prop('checked', true);
        }
		else {
			$('.r_people_id_check').prop('checked', false);
		}
	});
	
	$('.r_people_id_check').on('change', function () {
		$('#runcheck_box').prop('checked', true);
		$('.r_people_id_check').each(function() {
			if ($(this).prop('checked') == false) {
				$('#runcheck_box').prop('checked', false);
				return;
			}
		});
	});
	
	$('#retire_all').on('click', function() {
		$('#for_popups_pop').empty();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">Are you sure you want these People to quit their job?</p>');
		$('#reply_info').append('<p class="button red" id="retire_all_yes">Yes</p>');
		$('#reply_info').append('<p class="button blue" id="cancel">No</p>');
		$("#for_popups_pop").fadeIn(300);
	});
	
	$('#for_popups_pop').on('click', '#retire_all_yes', function() {
		var person_id = [];
		$('.r_people_id_check:checkbox:checked').each(function(e) {
			person_id[e] = ($(this).val());
		});
		var data = new FormData();
		data.append('person_id', person_id);
		var url = "../etc/retire_from_job";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			$('#reply_info').prepend('<div id="ri_head"><span id="form_close" class="glyphicon glyphicon-remove-circle"></span></div>');
			if(temp.success == true) {
				for(var x = 0; x < temp.summary.length; x++) {
					if(temp.summary[x].success == true) {
						$('#reply_info').append('<span class="glyphicon glyphicon-ok tiny_span"></span>');
						$('#reply_info').append('<p class="wsd_msg">' + temp.summary[x].msg + '</p>');
						$('#p_' + temp.summary[x].person_id).parent().slideUp(250);
						$('#p_' + temp.summary[x].person_id).parent().find('.r_people_id_check').remove();
					}
					else {
						$('#reply_info').append('<span class="fa fa-exclamation-triangle tiny_span error"></span>');
						$('#reply_info').append('<p class="wsd_msg">' + temp.summary[x].error + '</p>');
					}
				}
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
	
	//work all
	$('#uncheck_box').on('change', function () {
		if ($(this).prop('checked') == true) {
            $('.people_id_check').prop('checked', true);
        }
		else {
			$('.people_id_check').prop('checked', false);
		}
	});
	
	$('.people_id_check').on('change', function () {
		$('#uncheck_box').prop('checked', true);
		$('.people_id_check').each(function() {
			if ($(this).prop('checked') == false) {
				$('#uncheck_box').prop('checked', false);
				return;
			}
		});
	});
	
	$('#work_all').on('click', function() {
		var person_id = [];
		$('.people_id_check:checkbox:checked').each(function(e) {
			person_id[e] = ($(this).val());
		});
		var data = new FormData();
		data.append('id', person_id);
		data.append('action', 'work');
		var url = "../etc/work";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			$('#reply_info').prepend('<div id="ri_head"><span id="form_close" class="glyphicon glyphicon-remove-circle"></span></div>');
			if(temp.success == true) {
				setNewExperience();
				
				var summary = new ProductivitySummary(temp);
				summary.displayProductivitySummary();
				
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
			
			if(temp.success == true) {
				Tutorials.isTutorialActivated('work_all_button');
			}
		}
		submitData(data, url, dataReply);
	});
	
	function ProductivitySummary(temp) {
		this.temp = temp;
		this.displayProductivitySummary = function () {
			console.log(this.temp);
			for(var x = 0; x < this.temp.work_summary.length; x++) {
				if(this.temp.work_summary[x].success == true) {
					$('#p_' + this.temp.work_summary[x].person_id).children('.people_id_check').slideUp();
					$('#p_' + this.temp.work_summary[x].person_id).children('.people_id_check').remove();
					
					//update production info
					$('#p_' + this.temp.work_summary[x].person_id).children('.work').replaceWith('<p class="worked">Worked</p>');
					$('#p_' + this.temp.work_summary[x].person_id).next().children().last().append('<div class="worker_worked">' +
										'<abbr title="Day: ' + this.temp.work_summary[x].day_number + ' ' + this.temp.work_summary[x].date + 
										' ' + this.temp.work_summary[x].time + '"><span class="fa fa-user"></span></abbr>' +
										'<abbr title="produced"><p>' + this.temp.work_summary[x].productivity + '</p></abbr>' +
										'</div>');
					$('#p_' + this.temp.work_summary[x].person_id).find('.person_worked_stat').removeClass('person_can_work');
					$('#p_' + this.temp.work_summary[x].person_id).find('.person_worked_stat').addClass('person_worked');
					$('#p_' + this.temp.work_summary[x].person_id).find('.person_worked_stat').parent().attr('title', 'Worked today');
					
					//update energy
					var energy = $('#p_' + this.temp.work_summary[x].person_id).find('.bar p').html();
					energy = energy.split("/");
					max_energy = energy[1];
					var energy_percent = (this.temp.work_summary[x].new_energy / max_energy) * 100;
					$('#p_' + this.temp.work_summary[x].person_id).find('.bar p').html(this.temp.work_summary[x].new_energy + '/' + max_energy);
					$('#p_' + this.temp.work_summary[x].person_id).find('.bar .progress').css('width', energy_percent + '%');
					
					//display summary
					$('#reply_info').append('<div class="work_summary_div">' +
											'<span class="glyphicon glyphicon-ok tiny_span"></span>' +
											'<p class="wsd_msg">' + this.temp.work_summary[x].msg + '</p>' +
											'<div class="icon_amount product_product_icon">' +
											'<img class="product_icon" src="../product_icons/' + this.temp.work_summary[x].product_icon + 
											'" alt="product">' +
											'<p class="amount">+' + this.temp.work_summary[x].productivity + '</p>' +
											'</div>' +
											
											'<div class="salary_summary">' +
											'<p class="ss_label">Salary</p>' +
											'<p class="ss_number">' + this.temp.work_summary[x].salary + 
											' ' + this.temp.work_summary[x].currency_abbr + '</p>' +
											'<p class="ss_label">Tax</p>' +
											'<p class="ss_number">-' + this.temp.work_summary[x].taxes + 
											' ' + this.temp.work_summary[x].currency_abbr + '</p>' +
											'<p class="line_break_salary">----------------------</p>' +
											'<p class="ss_label">Total</p>' +
											'<p class="ss_number">+' + this.temp.work_summary[x].after_tax_salary + 
											' ' + this.temp.work_summary[x].currency_abbr + '</p>' +
											'</div>' +
											
											'<div class="energy_cons_div">' +
											'<p class="ecd_lbl">Used energy</p>' +
											'<p class="energy_consumption">-' + this.temp.work_summary[x].energy_consumption + 
											'<i class="glyphicon glyphicon-flash"></i></p>' +
											'</div>' +
											
											'<div class="exp_gained_div">' +
											'<p class="egd_lbl">Person earned experience</p>' +
											'<p class="exp_gained">' + this.temp.work_summary[x].exp_for_person_work + 
											' <i class="fa fa-star" aria-hidden="true"></i></p>' +
											'</div>' +
											
											'<div class="exp_gained_div">' +
											'<p class="egd_lbl">User earned experience</p>' +
											'<p class="exp_gained">' + this.temp.work_summary[x].exp_for_user_work + 
											' <i class="fa fa-star" aria-hidden="true"></i></p>' +
											'</div>' +
											
											'<div class="bonus_div">' +
											'<p class="bd_heading">Productivity details</p>' +
											'<p class="bd_label">Base productivity</p>' +
											'<p class="bd_number">' + this.temp.work_summary[x].base_productivity + '</p>' +
											'<p class="bd_label">Experience bonus</p>' +
											'<p class="bd_number">' + this.temp.work_summary[x].persons_bonus + '</p>' +
											'<p class="bd_percentage">' + this.temp.work_summary[x].persons_bonus_perc + '%</p>' +
											'<p class="bd_label">Country bonus</p>' +
											'<p class="bd_number">' + this.temp.work_summary[x].country_bonus + '</p>' +
											'<p class="bd_percentage">' + this.temp.work_summary[x].country_bonus_perc + '%</p>' +
											'<p class="bd_label">User level bonus</p>' +
											'<p class="bd_number">' + this.temp.work_summary[x].user_bonus + '</p>' +
											'<p class="bd_percentage">' + this.temp.work_summary[x].user_bonus_perc + '%</p>' +
											'<p class="bd_label">Region road bonus</p>' +
											'<p class="bd_number">' + this.temp.work_summary[x].road_bonus + '</p>' +
											'<p class="bd_percentage">' + this.temp.work_summary[x].road_bonus_perc + '%</p>' +
											'<p class="bd_label">Tax on Production</p>' +
											'<p class="bd_number bd_prod_tax">-' + this.temp.work_summary[x].tax_from_productivity + 
											'</p>' +
											'<p class="bd_percentage bd_prod_tax">' + 
											this.temp.work_summary[x].tax_from_productivity_perc + '%</p>' +
											'<p class="bd_label">Total</p>' +
											'<p class="bd_number bd_total">' + this.temp.work_summary[x].taxed_productivity + '</p>' +
											'</div>' +
											'<p class="ws_break">-------------------------------------------------------------</p>' +
											'</div>');
				}
				else {
					$('#reply_info').append('<span class="fa fa-exclamation-triangle tiny_span error"></span>');
					$('#reply_info').append('<p class="wsd_msg">' + this.temp.work_summary[x].error + '</p>');
					$('#reply_info').append('<p class="ws_break">-------------------------------------------------------------</p>');
				}
			}
		};
	}
	
	//work
	$('.work').on('click', function() {
		var person_id = $(this).next().html();
		var data = new FormData();
		data.append('id', person_id);
		data.append('action', 'work');
		var url = "../etc/work";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				setNewExperience();
				
				var summary = new ProductivitySummary(temp);
				summary.displayProductivitySummary();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut();
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut();
	});
	
	$('#for_popups_pop').on('click', '#form_close', function() {
		$("#for_popups_pop").fadeOut();
	});
	
	$('#for_popups_pop').on('click', '#close', function() { //close details
		 $("#for_popups_pop").fadeOut();
	});
});