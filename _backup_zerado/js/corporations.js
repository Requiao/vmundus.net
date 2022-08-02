$(document).ready(function() {
	/* switch windows */
	var menu = {"my_corporations": {"is_selected": false, "block_name": "#my_corps_div"},
				"corp_invitations": {"is_selected": false, "block_name": "#invitations_div"}
			   };
			   
	var selected = 'my_corporations';
	$('#' + selected).css('backgroundColor', 'rgb(255, 255, 255)');
	$('#' + selected).css('borderTop', '3px solid rgb(56, 75, 89)');
	$('#page_menu p').on('click', function() {
		var item = $(this).attr('id');
		$('#' + selected).css('borderTop', 'none');
		$('#' + selected).css('backgroundColor', '');
		menu[selected].is_selected = false;
		$(menu[selected].block_name).css('display', 'none');
		$(this).css('backgroundColor', 'rgb(255, 255, 255)');
		$(this).css('borderTop', '3px solid rgb(56, 75, 89)');
		$(menu[item].block_name).fadeIn(250);
		selected = item;
		menu[item].is_selected = true;
	});
	
	/* create new corporation */
	let create_new_corp_modal;
	$('#create_new').on('click', function() {
		create_new_corp_modal = new ModalBox('600px');

		create_new_corp_modal.setHeading('Create Corporation');

		create_new_corp_modal.appendToModal('<input id="ccd_corp_name" placeholder="Corporation Name" maxlength="15">');
		create_new_corp_modal.appendToModal('<input id="ccd_corp_abbr" placeholder="Abbreviation" maxlength="5">');

		create_new_corp_modal.appendCancelButton('Cancel');
		create_new_corp_modal.appendSubmitButton('Create');
		create_new_corp_modal.setSubmitButtonAction(onCreateCorporation);

		create_new_corp_modal.displayModal();
	});
	
	let onCreateCorporation =  function() {
		var corp_name = $('#ccd_corp_name').val();
		var corp_abbr = $('#ccd_corp_abbr').val();
		var data = new FormData();
		data.append('corp_name', corp_name);
		data.append('corp_abbr', corp_abbr);
		data.append('action', 'create_new_corp');
		var url = "../etc/corporations";
		create_new_corp_modal.setLoadingSubmitBtn(true);
		function dataReply(reply) {
			console.log(reply);
			create_new_corp_modal.setLoadingSubmitBtn(false);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$('#my_corps_div').append(
					generateCorporationDetails(temp.corp_info)
				);

				create_new_corp_modal.setSuccessModal(temp.msg);
			}
			else {
				create_new_corp_modal.setErrorMsg(temp.error);
			}
		}
		submitData(data, url, dataReply, false);
	}
	
	/* collect products */
	let collect_products_modal;
	$('body').on('click', '.ccd_collect_prod', function() {	
		var corp_id = $(this).attr('corporation_id');
		var data = new FormData();
		data.append('corp_id', corp_id);
		data.append('action', 'collect');
		var url = "../etc/corporations";
		serverRequest(data, url).then(reply => {
			var temp = JSON.parse(reply);
			collect_products_modal = new ModalBox('800px');
			if(temp.success === true) {
				for(var x = 0; x < temp.products.length; x++) {
					collect_products_modal.appendToModal('<div class="icon_amount">' +
						'<abbr title="' + temp.products[x].product_name + 
						'"><img class="product_icon" src="../product_icons/' + temp.products[x].product_icon + 
						'" alt="' + temp.products[x].product_name + '"></abbr>' +
						'<p class="amount">' + temp.products[x].quantity + '</p>' +
						'<p class="collect_corp_products" corporation_id="' + corp_id + '">Collect</p>' +
						'<p id="pi_' + temp.products[x].product_id + '" hidden="">' + temp.products[x].product_id + '</p>' +
						'</div>');
				}

				collect_products_modal.setHeading('Collect products');
				collect_products_modal.appendCancelButton('Done');
			}
			else {
				collect_products_modal.setErrorModal(temp.error);
			}
			collect_products_modal.displayModal();
		});
	});
	
	$('body').on('click', '.collect_corp_products', function() {
		let product_id = $(this).next().html();
		let corp_id = $(this).attr('corporation_id');
		let data = new FormData();
		data.append('corp_id', corp_id);
		data.append('product_id', product_id);
		data.append('action', 'collect_ok');
		let url = "../etc/corporations";
		serverRequest(data, url).then(reply => {
			let temp = JSON.parse(reply);
			if(temp.success === true) {
				if(temp.left == 0) {
					$(this).parent().remove();
				}
				else {
					$(this).parent().children('.amount').html(numberFormat(temp.left, 2, '.', ' '));
				}

				let total = parseFloat($('#left_to_collect_' + corp_id).html());
				total -= temp.collected;
				$('#left_to_collect_' + corp_id).html(numberFormat(total, 2, '.', ' '));
			}
			else {
				collect_products_modal.setErrorMsg(temp.error);
			}
		});
	});

	/* MANAGE MEMBERS */
	$('body').on('click', '.ccd_view_members', function() {	
		let corp_id = $(this).attr('corporation_id');
		let data = new FormData();
		data.append('corp_id', corp_id);
		data.append('action', 'view_members');
		let url = "../etc/corporations";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			let temp = JSON.parse(reply);
			let modal = new ModalBox('800px', '500px');
			if(temp.success === true) {
				modal.appendToModal(
					'<div id="icpi_labels_div">' +
						'<p id="icpi_label">Colors meaning:</p>' +
						'<p id="icpi_invested_label">Invested</p>' +
						'<p id="icpi_earned_label">Earned</p>' +
					'</div>'
				);

				if(temp.is_manager) {
					modal.appendToModal(
						'<div id="invite_corp_member_div">' +
							'<p id="icm_label">Invite player:</p>' +
							'<input id="icm_player_name" placeholder="Player Name" maxlength="15">' +
							'<p id="icm_invite_member" class="button blue" corporation_id="' + corp_id + '">Invite</p>' +
						'</div>'
					);
				}

				temp.members.map(members => {
					let products_info_html = '';
					members.products_info.map(products_info => {
						products_info_html += 
							'<div class="inv_rec_product_info">' +
								'<abbr title="' + products_info.product_name + '">' +
									'<img class="icpi_product_icon" src="../product_icons/' + products_info.product_icon + 
									'" alt="' + products_info.product_name + '"></abbr>' +
								'<p class="invested">' + products_info.invested + '</p>' +
								'<p class="earned">' + products_info.earned + '</p>' +
							'</div>'
						;
					});
					modal.appendToModal(
						'<div class="corp_members_div">' +
							'<a class="cmd_member_name" href="user_profile?id=' + members.member_id + 
							'">' + members.member_name + '</a>' + 
							'<img class="cmd_member_img" src="../user_images/' + members.member_image + '"></img>' +
							'<div class="inv_rec_product_div">' +
								products_info_html +
							'</div>' +
							(temp.is_manager ? '<p class="expel_corp_member button red" member_id="' + members.member_id + 
							'" corporation_id="' + corp_id + '">Expel</p>': '') +
						'</div>'
					);
				});
				modal.setHeading('Corporation members');
				modal.appendCancelButton('Done');
			}
			else {
				modal.setErrorModal(temp.error);
			}
			modal.displayModal();
		});
	});

	//invite
	$('body').on('click', '#icm_invite_member', function() {
		let corp_id = $(this).attr('corporation_id');
		let player_name = $('#icm_player_name').val();
		let data = new FormData();
		data.append('corp_id', corp_id);
		data.append('player_name', player_name);
		data.append('action', 'invite_new_member');
		let url = "../etc/corporations";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);
			let modal = new ModalBox('800px', '500px');
			if(temp.success === true) {
				modal.setSuccessModal(temp.msg);
			}
			else {
				modal.setErrorModal(temp.error);
			}
			modal.displayModal();
		});
	});

	//join
	$('body').on('click', '.cdd_join_corp', function() {
		let corp_id = $(this).attr('corporation_id');
		let data = new FormData();
		data.append('corp_id', corp_id);
		data.append('action', 'join_corporation');
		let url = "../etc/corporations";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);
			let modal = new ModalBox('800px', '500px');
			if(temp.success === true) {
				modal.setSuccessModal(temp.msg);
				$(this).parent().slideUp(() => $(this).remove());

				$('#my_corps_div').append(
					generateCorporationDetails(temp.corp_info)
				);
			}
			else {
				modal.setErrorModal(temp.error);
			}
			modal.displayModal();
		});
	});

	//reject
	$('body').on('click', '.cdd_reject_invite', function() {
		let corp_id = $(this).attr('corporation_id');
		let data = new FormData();
		data.append('corp_id', corp_id);
		data.append('action', 'reject_invitation');
		let url = "../etc/corporations";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);
			let modal = new ModalBox('800px', '500px');
			if(temp.success === true) {
				modal.setSuccessModal(temp.msg);
				$(this).parent().slideUp(() => $(this).remove());
			}
			else {
				modal.setErrorModal(temp.error);
			}
		});
	});

	//expel_corp_member
	let confirm_expel_member_modal;
	$('body').on('click', '.expel_corp_member', function() {
		let corporation_id = $(this).attr('corporation_id');
		let member_id = $(this).attr('member_id');

		confirm_expel_member_modal = new ModalBox('600px');

		confirm_expel_member_modal.setHeading('Confirmation');
		confirm_expel_member_modal.appendToModal(
			'<p>Are you sure you want expel this member from the corporation?</p>'
		);
		confirm_expel_member_modal.appendCancelButton('Cancel');
		confirm_expel_member_modal.appendSubmitButton('Yes');
		confirm_expel_member_modal.setSubmitButtonAction(expelMember, [corporation_id, member_id, this]);

		confirm_expel_member_modal.displayModal();
	});

	let expelMember = function(corporation_id, member_id, e) {
		let data = new FormData();
		data.append('corp_id', corporation_id);
		data.append('member_id', member_id);
		data.append('action', 'expel_corp_member');
		let url = "../etc/corporations";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				confirm_expel_member_modal.setSuccessModal(temp.msg);
				$(e).parent().slideUp(() => $(e).remove());
			}
			else {
				confirm_expel_member_modal.setErrorModal(temp.error);
			}
		});
	};

	/* LEAVE/DISBAND CORPORATION */
	let leave_corp_modal;
	$('body').on('click', '.cdd_leave_corp', function() {	
		let leave_corp_id = $(this).attr('corporation_id');
		leave_corp_modal = new ModalBox('600px');

		leave_corp_modal.setHeading('Confirmation');
		leave_corp_modal.appendToModal(
			'<p>Are you sure you want to leave this corporation?</p>'
		);
		leave_corp_modal.appendCancelButton('Cancel');
		leave_corp_modal.appendSubmitButton('Yes');
		leave_corp_modal.setSubmitButtonAction(leaveCorp, [leave_corp_id, this]);

		leave_corp_modal.displayModal();
	});
	leaveCorp = function(leave_corp_id, e) {	
		var data = new FormData();
		data.append('corp_id', leave_corp_id);
		data.append('action', 'leave_corp_info');
		var url = "../etc/corporations";
		serverRequest(data, url).then(reply => {
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				$(e).parent().slideUp(() => $(e).parent().remove());
				leave_corp_modal.removeModal();
			}
			else {
				leave_corp_modal.setErrorModal(temp.error);
			}
		});
	};

	let getCorporations =  function() {
		var data = new FormData();
		data.append('action', 'get_corporations');
		var url = "../etc/corporations";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			if(temp.success === true) {
				temp.corporations.map(item => {
					$('#my_corps_div').append(
						generateCorporationDetails(item)
					);
				});
				
			}
			else {
				let modal = new ModalBox();
				modal.setErrorModal(temp.error);
			}
		}
		submitData(data, url, dataReply, false);
	}
	getCorporations();

	function generateCorporationDetails(corp_info) {
		return '<div class="corp_det_div">' +
			'<p class="cdd_abbr">' + corp_info.corporation_abbr + '</p>' +
			'<p class="cdd_corp_name">' + corp_info.corporation_name + '</p>' +
				'<a class="cdd_manager_name" href="user_profile?id=' + corp_info.manager_id +
			'">' + corp_info.manager_name + '</a>' +
			'<img class="cdd_manager_img" src="../user_images/' + corp_info.manager_img + '">' +
			
			'<div class="cdd_corp_det_divs">' +
				'<div class="ccd_corp_det">' +
					'<i class="fa fa-archive" aria-hidden="true"></i>' +
					'<div class="ccd_description">' +
						'<p class="ccd_head">Available Products</p>' +
						'<p class="ccd_desc">Total amount of products that can be collected</p>' +
					'</div>' +
					'<p class="ccd_info total_prods" id="left_to_collect_' + corp_info.corporation_id + 
					'">' + corp_info.available_prod + '</p>' +
					'<p class="ccd_collect_prod" corporation_id="' + corp_info.corporation_id + '">Details</p>' +
				'</div>' +
				
				'<div class="ccd_corp_det">' +
					'<i class="fa fa-users" aria-hidden="true"></i>' +
					'<div class="ccd_description">' +
						'<p class="ccd_head">Members</p>' +
						'<p class="ccd_desc">Corporation members</p>' +
					'</div>' +
					'<p class="ccd_info">' + corp_info.total_members + '</p>' +
					'<p class="ccd_view_members" corporation_id="' + corp_info.corporation_id + '">Members</p>' +
				'</div>' +
				
				'<div class="ccd_corp_det">' +
					'<i class="fa fa-area-chart" aria-hidden="true"></i>' +
					'<div class="ccd_description">' +
						'<p class="ccd_head">Invested</p>' +
						'<p class="ccd_desc">Total amount of products invested into the corporation calculated' +
						' in gold</p>' +
					'</div>' +
					'<p class="ccd_info">' + corp_info.total_invested + '</p>' +
				'</div>' +
				
				'<div class="ccd_corp_det">' +
					'<i class="fa fa-line-chart" aria-hidden="true"></i>' +
					'<div class="ccd_description">' +
						'<p class="ccd_head">Earned</p>' +
						'<p class="ccd_desc">Total amount of products received from the corporation calculated' +
						' in gold</p>' +
					'</div>' +
					'<p class="ccd_info">' + corp_info.total_earned + '</p>' +
				'</div>' +
			'</div>' +

			'<a href="corporation_info?corp_id=' + corp_info.corporation_id + 
			'" class="cdd_view_corp button">View</a>' +
			(corp_info.is_manager 
				? ''		
				: '<p class="cdd_leave_corp button" corporation_id="' + corp_info.corporation_id + '">Leave</p>'
			) +
		'</div>';
	}
});