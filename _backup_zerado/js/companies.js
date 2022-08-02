$(document).ready(function() {
	
	/* invest resource product */
	$('#invest_resources').on('click', function() {
		var data = new FormData();
		data.append('action', 'invest_resources');
		var url = "../etc/companies";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				for(var x = 0; x < temp.summary.length; x++) {
					if(temp.summary[x].success == true) {
						$('#reply_info').append('<div class="invest_summary_div">' +
												'<span class="glyphicon glyphicon-ok"></span>' +
												'<p class="isd_company_name">' + lang.company_name +
												': <a href="company_manage?id=' + temp.summary[x].company_id + 
												'">' + temp.summary[x].company_name + '</a>' +
												'</p>' +
												'<p class="msg">' + temp.summary[x].msg + '</a>' +
												'</div>');
						
						//update bar details
						if(temp.summary[x].resource_storage_fill <= 25 && temp.summary[x].resource_storage_fill > 10) {
							var background_color = 'rgb(223, 163, 58)';
						}
						else if(temp.summary[x].resource_storage_fill <= 10) {
							var background_color = 'rgb(255, 68, 0)';
						}
						else {
							var background_color = 'rgb(128, 182, 109)';
						}
					
						$('#comp_' + temp.summary[x].company_id + ' .cd_resources_bar .progress').animate({width: + 
													temp.summary[x].resource_storage_fill + '%'});
						$('#comp_' + temp.summary[x].company_id + ' .cd_resources_bar .progress').css(
													'backgroundColor', background_color);
						$('#comp_' + temp.summary[x].company_id + ' .cd_resources_bar p').html(
													temp.summary[x].resource_storage_fill + '%');
						
						//update Available resources for N working cycles
						$('#comp_' + temp.summary[x].company_id + ' .cd_resource_cycles_div .cd_cycles_info').remove();
						
						for(var u = temp.summary[x].resources_working_cycles.length - 1; u >= 0 ; u--) {
							if(temp.summary[x].resources_working_cycles[u].cycles <= 5) {
								var color = 'cd_red';
							}
							else if(temp.summary[x].resources_working_cycles[u].cycles <= 10) {
								var color = 'cd_orange';
							}
							else {
								var color = 'cd_black';
							}
							
							$('#comp_' + temp.summary[x].company_id + ' .cd_resource_cycles_div .cd_cycles_head').after(
										'<p class="cd_cycles_info">' + temp.summary[x].resources_working_cycles[u].product_name + 
										': <span class="' + color + '">' + temp.summary[x].resources_working_cycles[u].cycles + 
										'</span></p>'
										);
						}
					}
					else {
						$('#reply_info').append('<div class="invest_summary_div">' +
												'<span id="error" class="fa fa-exclamation-triangle"></span>' +
												'<p class="isd_company_name">' + lang.company_name +
												': <a href="company_manage?id=' + temp.summary[x].company_id + 
												'">' + temp.summary[x].company_name + '</a>' +
												'</p>' +
												'<p class="error_msg">' + temp.summary[x].error + '</p>' +
												'</div>');
					}
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop').fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	/* collect products */
	$('#collect_products').on('click', function() {
		var data = new FormData();
		data.append('action', 'collect_products');
		var url = "../etc/companies";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$("#for_popups_pop").empty();
			$("#for_popups_pop").append('<div id="reply_info"></div>');
			if(temp.success == true) {
				$("#reply_info").append('<div id="ri_head"><span id="form_close" class="glyphicon glyphicon-remove-circle"></span></div>');
				$("#reply_info").append('<p id="msg">' + temp.msg + '</p>');
				
				for (i = 0; i < temp.summary.length; i++) {
					$("#reply_info").append('<div class="icon_amount">' +
											 '<abbr title="' + temp.summary[i].product_name + '">' +
											 '<img class="product_icon" src="../product_icons/' + temp.summary[i].product_icon + '"' +
											 ' alt="' + temp.summary[i].product_icon + '"></abbr>' +
											 '<p class="amount">' + temp.summary[i].amount + '</p>' +
											 '</div>');
					
					//reset bar
					$('#comp_' + temp.summary[i].company_id).find('.cd_products_bar .progress').animate({width: "0%"});
					$('#comp_' + temp.summary[i].company_id).find('.cd_products_bar p').html('0.00%');
				}
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
	
	/* create company */
	$('#create_company').on('click', function() {
		var data = new FormData();
		data.append('action', 'get_available_countries');
		var url = "../etc/companies";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				$('#reply_info').append('<div id="country_list">' +
										'<div id="country">' + 
										'<p>Chose country</p>' + 
										'</div>' + 
										'<p id="get_country_id" hidden></p>' +  
										'<span class="glyphicon glyphicon-menu-down"></span>' + 
										'</div>' +
										'<div id="countries_div"></div>');
				for (var x = 0; x < temp.countries.length; x++) {
					$('#reply_info #countries_div').append('<div class="country">' +
														   '<img src="../country_flags/' + temp.countries[x].flag + '">' +
														   '<p>' + temp.countries[x].country_name + '</p>' +
														   '<p class="c_country_id" hidden>' + temp.countries[x].country_id + '</p>' + 
															'</div>');
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="get_companies_btn">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
			
			Tutorials.isTutorialActivated('create_company_button');
		}
		submitData(data, url, dataReply);
	});
	
	//display country list
	$('#for_popups_pop').on('click', '#country_list', function() {
		$('#countries_div').slideToggle(250);
	});
	$('#for_popups_pop').on('mouseleave', '#countries_div', function() {
		$('#countries_div').slideUp(250);
	});
	
	$('#for_popups_pop').on('click', '.country', function() {
		$('#country_list').html('<span class="glyphicon glyphicon-menu-down"></span>');
		var country = $(this).html();
		var country_id = $(this).children('.c_country_id').html();
		$('#country_list').append('<div id="country">' + country + '</div>');
		$('#country_list').append('<p id="get_country_id" hidden>' + country_id + '</p>');
		$('#countries_div').slideUp(250);
		$('#region_list').html('<div class="region"><img><p>All</p></div><span class="glyphicon glyphicon-menu-down"></span>');
		
		Tutorials.isTutorialActivated('chose_country_button');
	});
	
	//close form
	$('#create_company_div').on('click', '#create_form_close', function() {
		$("#create_company_div").fadeOut(300);
		$("#company_data").fadeOut(300);
		$('#create_company_div').empty();
	});
	
	// show company list
	var country_id = '';
	$('#for_popups_pop').on('click', '#get_companies_btn', function() {
		country_id = $('#get_country_id').html();
		var data = new FormData();
		data.append('country_id', country_id);
		data.append('action', 'get_available_companies');
		var url = "../etc/companies";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').fadeOut();
			
			$('#create_company_div').html('<div id="form">' +
										  '<div id="form_headings">' +
										  '<span id="create_form_close" class="glyphicon glyphicon-remove-circle"></span>' +
										  '<p id="n_heading">' + lang.building_name + '</p>' +
										  '<p id="p_heading">' + lang.product_name + '</p>' +
										  '<p id="price_heading">' + lang.price + '</p>' +
										  '</div>' +
										  '</div>');
											  
			if(temp.success == true) {
				//generate class of avilable products
				var user_product = []; //store taxes for each country
				for(x = 0; x < temp.user_product.length; x++) {
					user_product[temp.user_product[x].product_id] =  temp.user_product[x].amount;
				}
				
				var req_product = '';
				var total_req = 0;
				var user_prod = 0;
				var avail_str = '';
				for (i = 0; i < temp.companies.length; i++) {
					req_product = '';
					for (x = 0; x < temp.companies[i].resources.length; x++) {
						total_req = parseFloat(temp.companies[i].resources[x].amount);
						user_prod = parseFloat(user_product[temp.companies[i].resources[x].product_id]);
						if(total_req <= user_prod) {
							avail_str = '<p class="available">' + total_req + "/" + numberFormat(user_prod, 2, '.', ' ') + '</p>';
						}
						else {
							avail_str = '<p class="available not_enough_prod">' + total_req + "/" + numberFormat(user_prod, 2, '.', ' ') + '</p>';
						}
						req_product += '<div class="icon_amount">' +
									   '<abbr title="' + temp.companies[i].resources[x].req_product_name + '">' +
									   '<img class="product_icon" src="../product_icons/' + 
									   temp.companies[i].resources[x].product_icon + '" alt="product icon">' +
									   '</abbr>' +
									   '<p class="amount">' + temp.companies[i].resources[x].amount + '</p>' +
									   avail_str +
									   '</div>';
					}
					
					$('#form').append('<div class="company_info">' + 
									  '<img class="building_icon" src="../building_icons/' + temp.companies[i].building_icon + 
									  '" alt="company image">' +
									  '<p class="building_name">' + temp.companies[i].building_name + '</p>' +
									  '<p class="product_name">' + temp.companies[i].product_name + '</p>' +
									  '<div class="building_price_div">' +
									  '<p>' + temp.companies[i].price + '</p>' + 
									  '<img src="../img/gold.png">' +
									  '</div>' +
									  '<p class="button create" id="' + temp.companies[i].building_id + '">' + lang.create + '</p>' +
									  '<div class="lablels">' +
									  '<abbr title="' + lang.Products_required_to_build_the_company + '"><p>' + lang.required + 
									  '</p></abbr>' +
									  '<abbr title="' + lang.total_required_products_out_of_available_products_warehouse + '">' +
									  '<p>Req/Avail</p></abbr>' +
									  '</div>' +
									  '<div class="product_div">' +
									  req_product +
									  '</div>' +
									  '</div>');
				}
			}
			else {
				$('#form').append('<p>' + temp.error + '</p>'); 
			}
			$("#create_company_div").fadeIn(300);
			
			Tutorials.isTutorialActivated('get_companies_button');
		}
		submitData(data, url, dataReply);
	});
	
	//prompt to chose location and company name
	var building_id;
	$('#create_company_div').on('click', '.create', function() {
		building_id = $(this).attr('id');
		var company_name = $(this).prev().prev().prev().html();
		var data = new FormData();
		data.append('country_id', country_id);
		data.append('action', 'company_data');
		var url = "../etc/companies";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#form').prepend('<div id="company_data"></div>');
			$("#company_data").fadeIn(300);
			var info = $('<p id="info"></p>').html('<i>' + company_name + '</i> ' + lang.will_be_located_in_the + 
												   ' <i>' + temp.country_name + '</i>. ' + lang.companies_are_not_movable + '.');
			var name = $('<p id="name_head"></p>').html(lang.company_name + ':');
			var error = $('<p id="cd_error"></p>').html('');
			var in_name = $('<input id="input_name" type="text" maxlength="20">').html('');
			var region = $('<p id="region_head"></p>').html(lang.region + ':');
			var in_region = $('<select id="region_id"></select>').html('');
			var create = $('<p class="button blue" id="create"></p>').html(lang.create);
			var cancel = $('<p class="button red" id="cancel"></p>').html(lang.cancel);
			$('#company_data').append(info, name, error, in_name, region, in_region, create, cancel);
			
			for (i = 0; i < temp.regions.length; i++) {
				$('#region_id').append('<option id="' + temp.regions[i].region_id + '">' +  temp.regions[i].region_name + '</option>');
			}
			
			Tutorials.isTutorialActivated('create_company_details');
		}
		submitData(data, url, dataReply);
	});
	
	$('#create_company_div').on('click', '#cancel', function() {
		$('#company_data').remove();
	});
	
	// create company
	$('#create_company_div').on('click', '#create', function() {
		var comp_name = $('#input_name').val();
		var reg_id = $('#region_id').children(":selected").attr("id");
		var data = new FormData();
		data.append('country_id', country_id);
		data.append('building_id', building_id);
		data.append('comp_name', comp_name);
		data.append('reg_id', reg_id);
		data.append('action', 'create_company');
		var url = "../etc/companies";
		
		$('#cd_error').empty();
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('#company_data').remove();
				$('#for_popups_pop').empty();
				$('#for_popups_pop').append('<div id="reply_info"></div>');
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);
				
				var resources_cycles = '';
				for(var x = 0; x < temp.companies.resources_prod.length; x++) {
					resources_cycles += '<p class="cd_cycles_info">' + temp.companies.resources_prod[x].product_name + 
										': <span class="cd_red">' + temp.companies.resources_prod[x].cycles +  
										'</span></p>';
				}
				
				$('#companies').append(
					'<div class="company_div">' +
					'<img class="building_icon" src="../building_icons/' + temp.companies.building_icon + '">' +
					'<div class="cd_info">' +
					'<p class="ch_name">' + lang.name + '</p>' +
					'<p class="cd_comp_name">' + temp.companies.company_name + '</p>' +
					'<p class="ch_product">' + lang.product + '</p>' +
					'<p class="cd_product_name">' + temp.companies.product_name + '</p>' +
					'<p class="ch_region">' + lang.region + '</p>' +
					'<p class="cd_region_name">' + temp.companies.region_name + '</p>' +
					'<p class="ch_country">' + lang.country + '</p>' +
					'<p class="cd_country_name">' + temp.companies.country_name + '</p>' +
					'</div>' +
					'<div class="cd_bars">' +
					'<p class="ch_storage">' + lang.storage_fill + '</p>' +
					'<div class="cd_products_bar">' +
					'<div class="bar"><div class="progress" style="width: ' + temp.companies.product_storage + 
					'%; background-color:rgb(128, 182, 109)"></div><p>' + temp.companies.product_storage + 
					'%</p></div>' +
					'</div>' +
					'<p class="ch_resources">' + lang.resources_fill + '</p>' +
					'<div class="cd_resources_bar">' +
					'<div class="bar"><div class="progress"  style="width: ' + temp.companies.resource_storage + 
					'%; background-color:rgb(128, 182, 109)"></div><p>' + temp.companies.resource_storage + 
					'%</p></div>' +
					'</div>' +
					'</div>' +
					'<div class="cd_resource_cycles_div">' +
					'<p class="cd_cycles_head">' + lang.available_resources_for_working_cycles + '</p>' +
					resources_cycles +
					'</div>' +
					'<div class="cd_workers_div">' +
					'<p class="cd_workers_head">' + lang.workers_details + '</p>' +
					'<abbr title="' + lang.not_hired + '"><span class="fa fa-user not_hired"></span></abbr>' +
					'</div>' +
					'<div class="cd_workers_div">' +
					'<p class="cd_workers_head">Working cycles</p>' +
					'<abbr title="not used"><span class="fa fa-circle not_worked"></span></abbr>' +
					'</div>' +
					'<a class="button manage" href="company_manage?id=' + temp.companies.company_id + '">Manage</a>' +
					'</div>');
			}
			else {
				$('#cd_error').html(temp.error);
			}
			Tutorials.isTutorialActivated('create_company_ok_button');
		}
		submitData(data, url, dataReply);
	});
	
	$('#create_company_div').on('click', '#ok', function() {
		$('#company_created').remove();
	});
	
	/* sort companies */
	$('#sort').change(function(){
		var order_by = $(this).val()
		var data = new FormData();
		data.append('order_by', order_by);
		data.append('action', 'sort_companies');
		var url = "../etc/companies";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#companies').children().remove();
			if(temp.success == true) {
				for(var i = 0; i < temp.companies.length; i++) {
					
					//bar color
					if(temp.companies[i].product_storage >= 75 && temp.companies[i].product_storage < 90) {
						var prod_background_color = 'rgb(223, 163, 58)';
					}
					else if(temp.companies[i].product_storage >= 90) {
						var prod_background_color = 'rgb(246, 120, 74)';
					}
					else {
						var prod_background_color = 'rgb(128, 182, 109)';
					}
					
					//resource bar color
					if(temp.companies[i].resource_storage <= 25 && temp.companies[i].resource_storage > 10) {
						var rec_background_color = 'rgb(223, 163, 58)';
					}
					else if(temp.companies[i].resource_storage <= 10) {
						var rec_background_color = 'rgb(255, 68, 0)';
					}
					else {
						var rec_background_color = 'rgb(128, 182, 109)';
					}
					
					var resources_cycles = '';
					for(var x = 0; x < temp.companies[i].resources_prod.length; x++) {
						if(temp.companies[i].resources_prod[x].cycles <= 5) {
							var color = 'cd_red';
						}
						else if(temp.companies[i].resources_prod[x].cycles <= 10) {
							var color = 'cd_orange';
						}
						else {
							var color = 'cd_black';
						}
						resources_cycles += '<p class="cd_cycles_info">' + temp.companies[i].resources_prod[x].product_name + 
											': <span class="' + color + '">' + temp.companies[i].resources_prod[x].cycles +  
											'</span></p>';
					}
					
					var workers_info = '';
					for(var x = 0; x < temp.companies[i].workers; x++) {
						if(temp.companies[i].workers_worked > 0) {
							workers_info += '<abbr title="' + lang.worked + '"><span class="fa fa-user worked"></span></abbr>';
							temp.companies[i].workers_worked--;
						}
						else if (temp.companies[i].hired_workers > 0) {
							workers_info += '<abbr title="' + lang.not_worked + '"><span class="fa fa-user not_worked"></span></abbr>';
							temp.companies[i].hired_workers--;
						}
						else {
							workers_info += '<abbr title="' + lang.not_hired + '"><span class="fa fa-user not_hired"></span></abbr>';
						}
					}
					
					var cycles_info = '';
					for(var x = 0; x < temp.companies[i].workers; x++) {
						if(temp.companies[i].cycles_worked > 0) {
							cycles_info += '<abbr title="used"><span class="fa fa-circle circle worked"></span></abbr>';
							temp.companies[i].cycles_worked--;
						}
						else {
							cycles_info += '<abbr title="not used"><span class="fa fa-circle not_worked"></span></abbr>';
						}
					}
					
					$('#companies').append(
						'<div class="company_div">' +
						'<img class="building_icon" src="../building_icons/' + temp.companies[i].building_icon + '">' +
						'<div class="cd_info">' +
						'<p class="ch_name">' + lang.name + '</p>' +
						'<p class="cd_comp_name">' + temp.companies[i].company_name + '</p>' +
						'<p class="ch_product">' + lang.product + '</p>' +
						'<p class="cd_product_name">' + temp.companies[i].product_name + '</p>' +
						'<p class="ch_region">' + lang.region + '</p>' +
						'<p class="cd_region_name">' + temp.companies[i].region_name + '</p>' +
						'<p class="ch_country">' + lang.country + '</p>' +
						'<p class="cd_country_name">' + temp.companies[i].country_name + '</p>' +
						'</div>' +
						'<div class="cd_bars">' +
						'<p class="ch_storage">' + lang.storage_fill + '</p>' +
						'<div class="cd_products_bar">' +
						'<div class="bar"><div class="progress" style="width: ' + temp.companies[i].product_storage + 
						'%; background-color:' + prod_background_color + '"></div><p>' + temp.companies[i].product_storage + 
						'%</p></div>' +
						'</div>' +
						'<p class="ch_resources">' + lang.resources_fill + '</p>' +
						'<div class="cd_resources_bar">' +
						'<div class="bar"><div class="progress"  style="width: ' + temp.companies[i].resource_storage + 
						'%; background-color:' + rec_background_color + '"></div><p>' + temp.companies[i].resource_storage + 
						'%</p></div>' +
						'</div>' +
						'</div>' +
						'<div class="cd_resource_cycles_div">' +
						'<p class="cd_cycles_head">' + lang.available_resources_for_working_cycles + '</p>' +
						resources_cycles +
						'</div>' +
						'<div class="cd_workers_div">' +
						'<p class="cd_workers_head">' + lang.workers_details + '</p>' +
						workers_info +
						'</div>' +
						'<div class="cd_workers_div">' +
						'<p class="cd_workers_head">Working cycles</p>' +
						cycles_info +
						'</div>' +
						'<a class="button manage" href="company_manage?id=' + temp.companies[i].company_id + '">' + lang.manage + '</a>' +
						'</div>');
				}
			}
			else {
				$('#companies').append('<p>' + temp.error + '</p>');
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#form_close', function() {
		$("#for_popups_pop").fadeOut(300);
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
		
		Tutorials.isTutorialActivated('company_created');
	});
});