$(document).ready(function() {
	var corp_id = $('#corp_id').html()?$('#corp_id').html():'';
	var company_id = $('#company_id').html();
	
	/* rename company */
	$('#rename').on('click', function() {
		$("#for_popups_pop").empty();
		$("#for_popups_pop").fadeIn();
		$('#for_popups_pop').prepend('<div id="reply_info"></div>');
		$('#reply_info').append('<p id="msg">New company name:</p>');
		$('#reply_info').append('<input id="new_comp_name" type="text" maxlength="20">');
		$('#reply_info').append('<p class="button blue" id="rename_ok">Rename</p>');
		$('#reply_info').append('<p class="button red" id="cancel">' + lang.cancel + '</p>');
	});
	
	$('#for_popups_pop').on('click', '#rename_ok', function() {
		var company_name = $('#new_comp_name').val();
		var company_id = $('#company_id').html();
		var data = new FormData();
		data.append('company_name', company_name);
		data.append('corp_id', corp_id);
		data.append('co_id', company_id);
		data.append('action', 'rename_company');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#page_head').html(temp.company_name);
		
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	/* upgrade company */
	$('#upgrade').on('click', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'get_upgrade_info');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="up_down_grade_info"></div>');
			if(temp.success == true) {
				$('#up_down_grade_info').append('<p id="msg">' + temp.msg +'</p>');
				for(i = 0; i < temp.req_products.length; i++) {
				$('#up_down_grade_info').append('<div class="icon_amount"><abbr title="' + temp.req_products[i].product_name + '">' +
												'<img class="product_icon" src="../product_icons/' + temp.req_products[i].product_icon + 
												'" alt="' + temp.req_products[i].product_name + '"></abbr>' +
												'<p class="amount">' + temp.req_products[i].amount + '</p></div>');											
				}
			}
			else {
				$('#up_down_grade_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#up_down_grade_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#up_down_grade_info').append('<p class="button blue" id="co_ok">' + lang.upgrade + '</p>');
			$('#up_down_grade_info').append('<p class="button red" id="cancel">' + lang.cancel + '</p>');
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#co_ok', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'upgrade_company');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				
				$($('.workers').first()).append('<abbr title="Not Hired"><span class="fa fa-user not_hired"></span></abbr>');
				$($('.workers').get(1)).append('<abbr title="not used"><span class="fa fa-circle not_worked"></span></abbr>');
				$('#downgrade').html(lang.downgrade);
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
		}
		submitData(data, url, dataReply);
	});
	
	/* downgrade company */
	$('#downgrade').on('click', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'downgrade_company_info');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="up_down_grade_info"></div>');
			if(temp.success == true) {
				$('#up_down_grade_info').append('<p id="msg">' + temp.msg +'</p>');
				for(i = 0; i < temp.products.length; i++) {
					$('#up_down_grade_info').append('<div class="icon_amount"><abbr title="' + temp.products[i].product_name + '">' +
													'<img class="product_icon" src="../product_icons/' + temp.products[i].product_icon + 
													'" alt="' + temp.products[i].product_name + '"></abbr>' +
													'<p class="amount">' + temp.products[i].amount + '</p></div>');
				}
				$('#up_down_grade_info').append('<p class="button red" id="co_d_ok">' + temp.button_name + '</p>');
				$('#up_down_grade_info').append('<p class="button blue" id="cancel">' + lang.cancel + '</p>');
			}
			else {
				$('#up_down_grade_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#up_down_grade_info').append('<p id="msg">' + temp.error + '</p>');
				$('#up_down_grade_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);
	});

	$('#for_popups_pop').on('click', '#co_d_ok', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'downgrade_company');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				
				if(temp.status == 'downgraded') {
					$('#workers').last().remove();//remove one circle of downgraded
					
					$('.workers abbr:last-child').remove();
				}
				else if(temp.status == 'destroyed') {
					$('#container').empty();
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* upgrade/build warehouse */
	var ware_type = "";
	$('#ps_upgrade').on('click', function() {
		ware_type = "prod";
		upgradeWarehouse(ware_type);
	});
	
	$('#rs_upgrade').on('click', function() {
		ware_type = "rec";
		upgradeWarehouse(ware_type);
	});
	
	function upgradeWarehouse(ware_type) {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('ware_type', ware_type);
		data.append('corp_id', corp_id);
		data.append('action', 'get_upgrade_warehouse_info');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="up_down_grade_info"></div>');
			if(temp.success == true) {
				$('#up_down_grade_info').append('<p id="msg">' + temp.msg +'</p>');
				for(i = 0; i < temp.products.length; i++) {
				$('#up_down_grade_info').append('<div class="icon_amount"><abbr title="' + temp.products[i].product_name + '">' +
												'<img class="product_icon" src="../product_icons/' + temp.products[i].product_icon + 
												'" alt="' + temp.products[i].product_name + '"></abbr>' +
												'<p class="amount">' + temp.products[i].amount + '</p></div>');											
				}
			}
			else {
				$('#up_down_grade_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#up_down_grade_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#up_down_grade_info').append('<p class="button blue" id="up_ware_ok">' + lang.upgrade + '</p>');
			$('#up_down_grade_info').append('<p class="button red" id="cancel">' + lang.cancel + '</p>');
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);
	}
	
	$('#for_popups_pop').on('click', '#up_ware_ok', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('ware_type', ware_type);
		data.append('corp_id', corp_id);
		data.append('action', 'upgrade_warehouse');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				if(ware_type == 'prod') {
					var capacity = $('#ps_img p').html();
					var capacity_array = capacity.split("/");
					capacity_array[1] = parseFloat(capacity_array[1]) + temp.capacity_add;
					$('#ps_img p').html(capacity_array[0] + '/' + capacity_array[1]);
					$('#ps_upgrade').html(lang.upgrade);
				}
				else {
					var capacity = $('#rs_img p').html();
					var capacity_array = capacity.split("/");
					capacity_array[1] = parseFloat(capacity_array[1]) + temp.capacity_add;
					$('#rs_img p').html(capacity_array[0] + '/' + capacity_array[1]);
					$('#rs_upgrade').html(lang.upgrade);
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* downgrade warehouse */
	$('#ps_downgrade').on('click', function() {
		ware_type = 'prod';
		downgradeWarehouse(ware_type);
	});
	
	$('#rs_downgrade').on('click', function() {
		ware_type = 'rec';
		downgradeWarehouse(ware_type);
	});

	function downgradeWarehouse(ware_type) {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('ware_type', ware_type);
		data.append('corp_id', corp_id);
		data.append('action', 'downgrade_warehouse_info');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="up_down_grade_info"></div>');
			if(temp.success == true) {
				$('#up_down_grade_info').append('<p id="msg">' + temp.msg +'</p>');
				for(i = 0; i < temp.products.length; i++) {
					$('#up_down_grade_info').append('<div class="icon_amount"><abbr title="' + temp.products[i].product_name + '">' +
													'<img class="product_icon" src="../product_icons/' + temp.products[i].product_icon + 
													'" alt="' + temp.products[i].product_name + '"></abbr>' +
													'<p class="amount">' + temp.products[i].amount + '</p></div>');
				}
				$('#up_down_grade_info').append('<p class="button red" id="d_ware_ok">' + temp.button_name + '</p>');
				$('#up_down_grade_info').append('<p class="button blue" id="cancel">' + lang.cancel + '</p>');
			}
			else {
				$('#up_down_grade_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#up_down_grade_info').append('<p id="msg">' + temp.error + '</p>');
				$('#up_down_grade_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);
	}
	
	$('#for_popups_pop').on('click', '#d_ware_ok', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('ware_type', ware_type);
		data.append('corp_id', corp_id);
		data.append('action', 'downgrade_warehouse');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				if(ware_type == 'prod') {
					var capacity = $('#ps_img p').html();
					var capacity_array = capacity.split("/");
					capacity_array[1] = parseFloat(capacity_array[1]) - temp.capacity_add;
					$('#ps_img p').html(capacity_array[0] + '/' + capacity_array[1]);
					$('#ps_upgrade').html(lang.upgrade);
				}
				else {
					var capacity = $('#rs_img p').html();
					var capacity_array = capacity.split("/");
					capacity_array[1] = parseFloat(capacity_array[1]) - temp.capacity_add;
					$('#rs_img p').html(capacity_array[0] + '/' + capacity_array[1]);
					$('#rs_upgrade').html(lang.upgrade);
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);	
	});
	
	/* withdraw company product */
	$('#ps_withdraw').on('click', function() {
		var quantity = $('#ps_withdraw_input').val();
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('quantity', quantity);
		data.append('corp_id', corp_id);
		data.append('action', 'withdraw_product');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				
				var capacity = $('#ps_img p').html();
				quantity = temp.quantity;
				var capacity_array = capacity.split("/");
				capacity_array[0] = parseFloat(capacity_array[0]) - quantity;
				capacity_array[0] = capacity_array[0].toFixed(2);
				$('#ps_img p').html(capacity_array[0] + '/' + capacity_array[1]);
				$('#ps_withdraw_input').val('');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop').fadeIn(300);
		}
		submitData(data, url, dataReply);
	});
	
	/* withdraw resource product */
	$('.r_withdraw').on('click', function() {
		var e = this;
		var quantity = $(this).prev().prev().val();
		var product_id = $(this).prev().prev().attr('id');
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('quantity', quantity);
		data.append('corp_id', corp_id);
		data.append('product_id', product_id);
		data.append('action', 'withdraw_resource');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				
				var capacity = $(e).prev().prev().prev().html();
				quantity = temp.quantity;
				capacity = parseFloat(capacity) - parseFloat(quantity);	
				capacity = capacity.toFixed(2);
				$(e).prev().prev().prev().html(capacity);
				$(e).prev().prev().val('');
				var capacity = $('#rs_img p').html();
				var capacity_array = capacity.split("/");
				capacity_array[0] = parseFloat(capacity_array[0]) - parseFloat(quantity);
				capacity_array[0] = capacity_array[0].toFixed(2);
				$('#rs_img p').html(capacity_array[0] + '/' + capacity_array[1]);
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
	
	/* invest resource product */
	$('.r_invest').on('click', function() {
		var e = this;
		var quantity = $(this).prev().val();
		var product_id = $(this).prev().attr('id');
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('quantity', quantity);
		data.append('corp_id', corp_id);
		data.append('product_id', product_id);
		data.append('action', 'invest_resource');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				
				var capacity = $(e).prev().prev().html();
				quantity = temp.quantity;
				capacity = parseFloat(capacity) + parseFloat(quantity);	
				capacity = capacity.toFixed(2);
				$(e).prev().prev().html(capacity);
				$(e).prev().val('');
				var capacity = $('#rs_img p').html();
				var capacity_array = capacity.split("/");
				capacity_array[0] = parseFloat(capacity_array[0]) + parseFloat(quantity);
				capacity_array[0] = capacity_array[0].toFixed(2);
				$('#rs_img p').html(capacity_array[0] + '/' + capacity_array[1]);
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
	
	/* invest resource product */
	$('#n_invest').on('click', function() {
		var quantity = $(this).prev().val();
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('quantity', quantity);
		data.append('corp_id', corp_id);
		data.append('action', 'invest_n_resources');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg +'</p>');
				
				
				for(var x = 0; x < temp.summary.length; x++) {
					var e = '#res_prod_' + temp.summary[x].product_id;
					var capacity = $(e).children('.r_amount').html();
					capacity = parseFloat(capacity) + temp.summary[x].amount;	
					capacity = capacity.toFixed(2);
					$(e).children('.r_amount').html(capacity);
				}
				
				var capacity = $('#rs_img p').html();
				var capacity_array = capacity.split("/");
				capacity_array[0] = parseFloat(capacity_array[0]) + temp.total;
				capacity_array[0] = capacity_array[0].toFixed(2);
				$('#rs_img p').html(capacity_array[0] + '/' + capacity_array[1]);
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$('#for_popups_pop').fadeIn();
			
			if(temp.success == true) {
				Tutorials.isTutorialActivated('n_invest_button');
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* sell company */
	$('#sell').on('click', function() {
		var action =  $('#sell').next().html();
		$("#for_popups_pop").empty();
		$("#for_popups_pop").fadeIn();
		$('#for_popups_pop').prepend('<div id="up_down_grade_info"></div>');
		if(action == 'sell') {
			$('#up_down_grade_info').append('<p id="msg">' + lang.enter_price_for_the_company + ':</p>');
			$('#up_down_grade_info').append('<input id="sell_input" type="text" maxlength="12" placeholder="998.48">');
			$('#up_down_grade_info').append('<p class="button red" id="sell_ok">' + lang.sell + '</p>');
		}
		else {
			$('#up_down_grade_info').append('<p id="msg">' + lang.remove_company_from_company_market + '</p>');
			$('#up_down_grade_info').append('<p class="button red" id="remove_ok">' + lang.remove + '</p>');
		}
		$('#up_down_grade_info').append('<p class="button blue" id="cancel">' + lang.cancel + '</p>');
	});
	
	$('#for_popups_pop').on('click', '#sell_ok', function() {
		var price = $('#sell_input').val();
		var company_id = $('#company_id').html();
		var data = new FormData();
		data.append('price', price);
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'sell_company');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#sell').html(lang.selling);
				$('#sell').next().html("selling");
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#remove_ok', function() {
		var company_id = $('#company_id').html();
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'remove_com_market');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#sell').html(lang.sell);
				$('#sell').next().html("sell");
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn();
		}
		submitData(data, url, dataReply);
	});
	
	/* offer job */
	$('#hire').on('click', function() {
		var salary = $('#h_salary_input').val();
		var skill_lvl = $('#h_exp_input').val();
		var quantity = $('#h_quantity_input').val();
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('skill_lvl', skill_lvl);
		data.append('salary', salary);
		data.append('quantity', quantity);
		data.append('corp_id', corp_id);
		data.append('action', 'offer_job');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.msg + '</p>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
			
			if(temp.success == true) {
				Tutorials.isTutorialActivated('offer_job_button');
			}
		}
		submitData(data, url, dataReply);	
	});
	
	/* offered job */
	$('#offers').on('click', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'get_offered_jobs');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			$('#for_popups_pop').prepend('<div id="show_jobs"></div>');
			$('#show_jobs').append('<p id="sj_salary">' + lang.salary + '</p>' +
								   '<p id="sj_skill">' + lang.skill + '</p>');
			if(temp.success == true) {
				for(i = 0; i < temp.offered_jobs.length; i++) {
					$('#show_jobs').append('<div id="job_' + temp.offered_jobs[i].job_id + '">' +
										   '<p class="sjd_salary">' + temp.offered_jobs[i].salary + 
										   ' ' + temp.offered_jobs[i].currency_abbr + '</p>' +
										   '<p class="sjd_skill">' + temp.offered_jobs[i].skill_lvl + '</p>' +
										   '<p class="button red job_remove">' + lang.remove + '</p>' +
										   '<p hidden>' + temp.offered_jobs[i].job_id + '</p>' +
										   '<p class="button blue apply_for_job">' + lang.apply + '</p>' +
										   '</div>');
				}
			}
			$('#show_jobs').append('<p class="button" id="reply_ok">' + lang.close + '</p>');
			$("#for_popups_pop").fadeIn(300);
			
			if(temp.success == true) {
				Tutorials.isTutorialActivated('job_offers_button');
			}
		}
		submitData(data, url, dataReply);	
	});
	
	$('#for_popups_pop').on('click', '.job_remove', function() {
		var job_id = $(this).next().html();
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('job_id', job_id);
		data.append('corp_id', corp_id);
		data.append('action', 'remove_job');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			if(temp.success == true) {
				$('#job_' + job_id).slideUp();
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* hire from company */
	var job_id = '';
	$('#for_popups_pop').on('click', '.apply_for_job', function() {
		job_id = $(this).prev().html();
		var location = $(this).siblings('.region_name').html();
		var skill = $(this).siblings('.bonus').html();
		var salary = $(this).siblings('.salary').html();
		var data = new FormData();
		data.append("job_id", job_id);
		data.append('action', "get_workers");
		var url = "../etc/apply_for_job";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
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
			$('#reply_info').append('<p class="button blue" id="reply_ok">' + lang.cancel + '</p>');
			$("#for_popups_pop2").fadeIn(300);
			
			if(temp.success == true) {
				Tutorials.isTutorialActivated('view_available_people_button');
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop2').on('click', '.apply_btn',function() {
		var person_id = $(this).next().attr('id');
		var data = new FormData();
		data.append("job_id", job_id);
		data.append("person_id", person_id);
		data.append('action', "apply_for_job");
		var url = "../etc/apply_for_job";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				//remove person from list
				$('#reply_info').append('<span class="glyphicon glyphicon-ok" id="span_ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				var e = $('#' + job_id).get(0);
				$(e).parent().slideUp();
				
				//remove job from list
				$('#job_' + job_id).slideUp();
			
				//update page. display that worker was hired
				$('#workers span').each(function() {
					if($(this).hasClass('not_hired')) {
						$(this).replaceWith('<span class="fa fa-user not_worked"></span>');
						return false;
					}
				});
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
			
			if(temp.success == true) {
				Tutorials.isTutorialActivated('apply_for_job_button');
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* hired workers */
	$('#workers_btn').on('click', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'get_hired_workers');
		var url = "../etc/manage_company";
		$("#for_popups_pop").css('display', 'none');
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			
			$('#for_popups_pop').prepend('<div id="show_workers"></div>');
			$('#show_workers').append('<div id="sw_heads">' +
									  '<p>' + lang.user + '</p>' +
									  '<p>' + lang.person + '</p>' +
									  '<p>' + lang.skill + '</p>' +
									  '<p>' + lang.salary + '</p>' +
									  '</div>' +
									  '<p id="fire_all" class="button red">Fire All</p>' +
									  '<p id="workers_error_reply"></p>'
									 );
			if(temp.success == true) {
				for(i = 0; i < temp.hired.length; i++) {
					var work_history = '';
					for(x = 0; x < temp.hired[i].production.length; x++) {
						if(temp.hired[i].production[x].produced == "n/w") {
							var clas = "fa fa-user-o";
							var worker_summary_class = "worker_missed";
						}
						else {
							var clas = "fa fa-user";
							var worker_summary_class = "worker_worked";
						}
						
						work_history += '<div class="' + worker_summary_class + '">' +
										'<abbr title="' + lang.day + ': ' + temp.hired[i].production[x].day_number + 
										'"><span class="' + clas + 
										'"></span></abbr>' +
										'<abbr title="produced"><p class="wcdp1">' + temp.hired[i].production[x].produced + 
										'</p></abbr>' +
										'</div>';
					}

					$('#show_workers').append('<div id="jw_' + temp.hired[i].job_id + '" class="worker_container">' +
												'<img src="../user_images/' + temp.hired[i].user_image + '" alt="user image">' +
												'<a href="user_profile?id=' + temp.hired[i].profile_id + '" target="_blank">'  + 
												temp.hired[i].user_name + '</a>' +
												'<p id="mw_person_name">' + temp.hired[i].person_name + '</p>' +
												'<p id="mw_skill">' + temp.hired[i].skill + '</p>' +
												'<p>' + temp.hired[i].salary + ' ' + temp.hired[i].currency_abbr + '</p>' +
												'<p class="button red fire">' + lang.fire + '</p>' +
												'<p class="button blue edit">' + lang.edit + '</p>' +
												'<p hidden>' + temp.hired[i].job_id + '</p>' +
												'<div class="worker_container_div">' +
												work_history +
												'</div>' +
											   '</div>');
					
				}
			}
			$("#for_popups_pop").fadeIn(300);
			$('#show_workers').append('<p class="button close_workers" id="reply_ok">' + lang.close + '</p>');	
		}
		submitData(data, url, dataReply);
	});
	
	//edit salary
	$('#for_popups_pop').on('click', '.edit', function() {
		var salary = $(this).prev().prev().html();
		var currency = salary.replace(/[ .0-9]/g, "");
		salary = salary.replace(/[^.0-9]/g, "");
		$(this).prev().prev().replaceWith('<input type="text" maxlength="7"  value="' + salary + '">');
		$(this).prev().before('<p id="after_input">' + currency + '</p>');
		$(this).replaceWith('<p class="button green" id="apply">' + lang.apply + '</p>');
	});
	
	$('#for_popups_pop').on('click', '#apply', function() {
		var job_id = $(this).next().html();
		var salary = $('#after_input').prev().val();
		salary =  parseFloat(salary).toFixed(2);
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('salary', salary);
		data.append('job_id', job_id);
		data.append('corp_id', corp_id);
		data.append('action', 'change_salary');
		var url = "../etc/manage_company";
		var e = this;
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#workers_error_reply').empty();
			if(temp.success == true) {
				var currency = $('#after_input').html();
				$(e).prev().prev().prev().replaceWith('<p>' + temp.salary + ' ' + temp.currency_abbr + '</p>');
				$('#after_input').remove();
				$(e).replaceWith('<p class="button blue edit">' + lang.edit + '</p>');
				
				$('#workers_error_reply').html(temp.msg);
				$('#workers_error_reply').css('color', '#356841');
			}
			else {
				$('#workers_error_reply').html(temp.error);
				$('#workers_error_reply').css('color', '#f6665c');
			}
		}
		submitData(data, url, dataReply);
	});
	
	//fire worker
	$('#for_popups_pop').on('click', '.fire', function() {
		var job_id =  $(this).next().next().html();
		$("#for_popups_pop2").empty();
		$("#for_popups_pop2").fadeIn(300);
		$('#for_popups_pop2').prepend('<div id="pop_info"></div>');
		$('#pop_info').append('<p id="msg">' + lang.are_you_sure_you_want_to_fire_this_worker + '</p>');
		$('#pop_info').append('<p class="button red" id="fire">' + lang.fire + '</p>');
		$('#pop_info').append('<p hidden>' + job_id + '</p>');
		$('#pop_info').append('<p class="button blue" id="cancel">' + lang.cancel + '</p>');
	});
	
	$('#for_popups_pop2').on('click', '#fire', function() {
		var job_id = $(this).next().html();
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('job_id', job_id);
		data.append('corp_id', corp_id);
		data.append('action', 'fire_worker');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$("#for_popups_pop2").empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.msg + '</p>');
				$('#jw_' + job_id).slideUp();
				
				//update page. display that worker was fired
				var removed = false;
				$('#workers span').each(function() {
					if($(this).hasClass('not_hired')) {
						$(this).parent().prev().children().replaceWith('<span class="fa fa-user not_hired"></span>');
						removed = true;
						return false;
					}
				});
				if(!removed) {
					$('#workers span').last().replaceWith('<span class="fa fa-user not_hired"></span>');
				}
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.error + '</p>');
			}
			
			$('#reply_info').append('<p class="button" id="reply_ok">Ok</p>');
		}
		submitData(data, url, dataReply);
	});
	
	$('#for_popups_pop').on('click', '#fire_all', function() {
		var data = new FormData();
		data.append('co_id', company_id);
		data.append('corp_id', corp_id);
		data.append('action', 'fire_all_workers');
		var url = "../etc/manage_company";
		function dataReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop2').empty();
			$('#for_popups_pop2').prepend('<div id="reply_info"></div>');
			if(temp.success == true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.msg + '</p>');
				
				$('.worker_container').slideUp();
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg_reply">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button" id="reply_ok">Ok</p>');
			$("#for_popups_pop2").fadeIn(300);
		}
		submitData(data, url, dataReply);	
	});
	
	/* sell products */
	var tax_array = []; //store taxes for each country
	
	$('#ps_sell_product').on('click', function() {
		var data = new FormData();
		data.append('corp_id', corp_id);
		data.append('company_id', company_id);
		data.append('sell_for', 'company');
		data.append('action', 'get_info');
		var event = this;
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').empty();
			if(temp.success == true) {
				var product_element = $(event).parent().html();
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<select id="countries">');
				
				for(x = 0; x < temp.tax_info.length; x++) {
					tax_array[temp.tax_info[x].country_id] =  temp.tax_info[x].tax;
					$('#reply_info #countries').append('<option value="' + temp.tax_info[x].country_id + 
														'">' +  temp.tax_info[x].country_name + '</option>');
				}
				
				$('#reply_info').append('</select>');
				$('#reply_info').append('<input class="input" id="amount_input" type="text" placeholder="' + lang.quantity + '">');
				$('#reply_info').append('<input class="input" id="price_input" type="text" placeholder="' + lang.price + '">');
				$('#reply_info').append('<p id="tax">tax: ' + temp.tax_info[0].tax + '%</p>');
				$('#reply_info').append('<p class="button green" id="sell_product">' + lang.offer + '</p>');
				$('#reply_info').append('<p class="button red" id="cancel">' + lang.cancel + '</p>');
				$("#for_popups_pop").fadeIn(300);
			}
			else {
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error+ '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			}
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, proccessReply);
	});
	
	//change showed tax amount on country change
	$('#for_popups_pop').on('change', '#countries', function() {
		var id = $(this).children(':selected').val();
		var x = id;
		$('#tax').text('tax: ' + tax_array[x] + '%');
	});
	
	//sell ok
	$('#for_popups_pop').on('click', '#sell_product', function() {
		var company_id = $('#company_id').html();
		var country_id = $('#countries').children(':selected').val();
		var quantity = $('#amount_input').val();
		var price =  $('#price_input').val();
		var data = new FormData();
		data.append('corp_id', corp_id);
		data.append('company_id', company_id);
		data.append('country_id', country_id);
		data.append('quantity', quantity);
		data.append('price', price);
		data.append('sell_for', 'company');
		data.append('action', 'sell');
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			$('#for_popups_pop').html('');
			$('#for_popups_pop').prepend('<div id="reply_info"></div>');
			if(temp.success === true) {
				$('#reply_info').append('<span class="glyphicon glyphicon-ok"></span>');
				$('#reply_info').append('<p id="msg">' + temp.msg + '</p>');
				
				var amount = $('#ps_img').children().next().html().replace(/ /g,"");
				var amount = amount.split("/");
				amount[0] = parseFloat(amount[0]) - parseFloat(temp.quantity);
				$('#ps_img').children().next().html(numberFormat(amount[0], 2, '.', ' ') + "/" + numberFormat(amount[1], 0, '', ' '));
				
				$('#product_offers_div').append('<div class="product_on_sale">' +
												'<abbr title="' + temp.product_name + 
												'"><img class="pos_product_icon" src="../product_icons/' + 
												temp.product_icon + '" alt="' + temp.product_name + '"></abbr>' +
												'<img class="country_flag" alt="' + temp.product_name + '" src="../country_flags/' + 
												temp.flag + '">' +
												'<p class="pos_quantity">' + temp.quantity + '</p>' +
												'<p class="pos_price">' + numberFormat(temp.price, 2, '.', ' ') + '</p>' +
												'<p class="pos_currency">' + temp.currency_abbr + '</p>' +
												'<p class="pos_tax">-' + temp.sale_tax + '%</p>' +
												'<p class="pos_remove button red">' + lang.remove + '</p>' +
												'<p id="oi_' + temp.offer_id + '" hidden>' + temp.offer_id + '</p>' +
												'</div>');
			}
			else {
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
			}
			$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
			$("#for_popups_pop").fadeIn(300);
		}
		submitData(data, url, proccessReply);
	});
	
	/* remove offer */
	$('#product_offers_div').on('click', '.pos_remove', function() {
		var offer_id = $(this).next().html();
		var data = new FormData();
		data.append('corp_id', corp_id);
		data.append('offer_id', offer_id);
		data.append('action', 'remove_company_offer');
		var e = this;
		var url = "../etc/sell_products";
		function proccessReply(reply) {
			var temp = JSON.parse(reply);
			
			if(temp.success === true) {
				$(e).parent().fadeOut(300);
				
				var amount = $('#ps_img').children().next().html().replace(/ /g,"");
				var amount = amount.split("/");
				amount[0] = parseFloat(amount[0]) + parseFloat(temp.quantity);
				$('#ps_img').children().next().html(numberFormat(amount[0], 2, '.', ' ') + "/" + numberFormat(amount[1], 0, '', ' '));
			}
			else {
				$('#for_popups_pop').html('');
				$('#for_popups_pop').prepend('<div id="reply_info"></div>');
				$('#reply_info').append('<span id="error" class="fa fa-exclamation-triangle"></span>');
				$('#reply_info').append('<p id="msg">' + temp.error + '</p>');
				$('#reply_info').append('<p class="button blue" id="reply_ok">Ok</p>');
				$("#for_popups_pop").fadeIn(300);	
			}
		}
		submitData(data, url, proccessReply);
	});
	
	// When the user clicks anywhere outside of the popup_div, close it
	$('html').on('click', function(event) {
		var modal = $("#for_popups_pop").get(0);
		if(event.target == modal) {
			$(modal).fadeOut(250);
		}
		if(event.target == $("#for_popups_pop").get(0)) {
			$('#rpd_pass_tip').fadeOut(250);
		}
	});
	
	/* for Korean language */
	$("p:contains('다운그레이드')").css("fontSize", "16px");
	$("p:contains('다운그레이드')").css("height", "20px");
	
	/* cancel/ok btn */
	$('#for_popups_pop').on('click', '#cancel', function() {
		$("#for_popups_pop").fadeOut(300);
		$('#for_popups_pop').html('');
	});
	
	$('#for_popups_pop2').on('click', '#cancel', function() {
		$("#for_popups_pop2").fadeOut(300);
		$('#for_popups_pop2').html('');
	});
	
	$('#for_popups_pop').on('click', '#reply_ok', function() {
		$("#for_popups_pop").fadeOut(300);
		$('#for_popups_pop').html('');
	});
	
	$('#for_popups_pop2').on('click', '#reply_ok', function() {
		$("#for_popups_pop2").fadeOut(300);
		$('#for_popups_pop2').html('');
	});
});