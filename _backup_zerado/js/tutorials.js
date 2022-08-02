$(document).ready(function() {
	Tutorials = {
		active_tutorial: null,
		
		//activate tutorial
		newTutorial: function(tutorial) {
			this.endTutorial();
			
			document.cookie = 'active_tutorial=' + tutorial + '; path=/';
			this.tutorials[tutorial].active = true;
			
			this.active_tutorial = tutorial;
			
			this.runTutorial('new_tutorial');
			
			Tutorials.displayEndTutorialButton();
		},
		
		//get activated tutorial
		getTutorial: function() {
			return this.active_tutorial = getCookie("active_tutorial");
		},
		
		//end tutorial button
		displayEndTutorialButton: function() {
			if(this.getTutorial()) {
				$('body').append('<p id="end_tutorial" class="button red" ' +
								 'style="position: fixed; left: 20px; width: 170px; bottom: 50px;">End Tutorial</p>');
			}
		},
		
		endTutorial: function() {
			this.deactivateAll();
			this.removeTutorialArrow();
			this.removeTutorialMsg();
			$('#end_tutorial').remove();
		},
		
		//deactivate all tutorials
		deactivateAll: function() {
			for (var item in this.tutorials) {
			  this.tutorials[item].active = false;
			}
			document.cookie = 'active_tutorial=; path=/';
		},

		//check if tutorial activated
		isTutorialActivated: function (event) {
			this.active_tutorial = this.getTutorial();
			
			if(this.active_tutorial) {
				this.tutorials[this.active_tutorial].active = true;
				
				this.runTutorial(event);
			}
		},
		
		
		// tutorials
		tutorials: {
			1: {
				"name": "Create a company",
				"active": false,
				"description": "In this tutorial, you will learn how to build your own company. " +
							   "This tutorial expects that you haven't spent your resources given you after your first login " +
							   "into the game. Please read the game wiki for more tutorials and details about this game."
			
			},
			
			2: {
				"name": "Make a job offer",
				"active": false,
				"description": "In this tutorial you will learn how to make your own job offer."
			
			},
			
			3: {
				"name": "Get a job",
				"active": false,
				"description": "This tutorial will walk you trough on how to apply for your own job. " +
							   "There are two ways how you can do this, you can go to the Economy->Job offers and find your job " +
							   "there, or follow these steps and apply from the company page."
			
			},
			
			4: {
				"name": "Work",
				"active": false,
				"description": "In this tutorial you will command your People to work."
			}
		},
		
		//reward for the tutorial
		rewardForTutorial: function(tutorial) {
			var data = new FormData();
			data.append('tutorial', tutorial);
			data.append('action', 'reward_for_tutorial');
			var url = "../etc/user_rewards";
			function dataReply(reply) {
				temp = JSON.parse(reply);
			}
			submitData(data, url, dataReply);
		},

		//tutorials
		runTutorial: function (event) {
			//display tutorial description
			if(event == 'new_tutorial') {
				this.displayPopUp(this.tutorials[this.active_tutorial].description, '100px', '30%', true, '550px');
			}
		
			//build a company
			if(this.tutorials[1].active == true && (event == 'continue_button' || event == 'index_page')) {
				var msg = 'Click on the Economy';
				var coords = this.getElementCoords('#economy');
				
				$('body').append(this.tutorialArrow(coords));
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[1].active == true && (event == 'economy_menu' && this.getPageLocation() != 'company_manage')) {
				var msg = 'Go to Companies page';
				var coords = this.getElementCoords('#economy ul a:nth-child(4) li');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[1].active == true && event == 'companies_page') {
				var msg = 'Click on the create company button';
				var coords = this.getElementCoords('#create_company');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[1].active == true && event == 'create_company_button') {
				var msg = 'Choose the country where you want to build your company. ' +
						  'You can build companies in countries that gave permission for citizens ' +
						  'of your country to build companies, in your own country or in the country ' +
						  'that has occupied at least one region of your country.';
				var coords = this.getElementCoords('#country_list');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[1].active == true && event == 'chose_country_button') {
				var msg = 'Click Ok';
				var coords = this.getElementCoords('#get_companies_btn');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[1].active == true && event == 'get_companies_button') {
				var msg = 'Chose a company from the list that you want to build and click Create. ' +
						  'It is important to build Plantation in order to produce food.';
				var coords = this.getElementCoords('.create');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[1].active == true && event == 'create_company_details') {
				var msg = 'Enter the company name and choose any region you would like your company to be located ' +
						  'at and then click Create.';
				var coords = this.getElementCoords('#create');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[1].active == true && event == 'create_company_ok_button') {
				var msg = 'Scroll down if needed to find your new company.';
				var coords = this.getElementCoords('#reply_ok');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[1].active == true && event == 'company_created') {
				this.rewardForTutorial(1);
				var msg = 'Congratulations! You are done with the tutorial. If this is your first time, ' +
						  'then you received a reward which you can collect on the homepage.';
						  
				var coords = this.getElementCoords('#reply_ok');
				
				this.removeTutorialArrow();
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			//make job offer
			if(this.tutorials[2].active == true && (event == 'continue_button' || event == 'index_page')) {
				var msg = 'Click on the Economy';
				var coords = this.getElementCoords('#economy');
				
				$('body').append(this.tutorialArrow(coords));
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[2].active == true && (event == 'economy_menu' && this.getPageLocation() != 'company_manage')) {
				var msg = 'Go to Companies page';
				var coords = this.getElementCoords('#economy ul a:nth-child(4) li');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[2].active == true && event == 'companies_page') {
				var msg = 'Click on the Manage button in order to open company page and manage the company.';
				var coords = this.getElementCoords($('.manage').first(), true);
				
				this.tutorialArrow(coords, true);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', (coords['left'] - 400) + 'px', false, '300px', true);
			}
			
			if(this.tutorials[2].active == true && event == 'company_manage_page') {
				var msg = 'Make the job offer. For skill level input 1 (skill level determines how skilled a worker should be, ' +
						  'each skill level gives +1% bonus to productivity) and for salary input 1. For Quantity leave default value. ' +
						  'After filling out the form click Offer.';
				var coords = this.getElementCoords($('#hire').last(), true);
				
				this.tutorialArrow(coords, true);
				this.displayPopUp(msg, coords['top'] - 200 + 'px', (coords['left'] - 400) + 'px', false, '300px', true);
			}
			
			if(this.tutorials[2].active == true && event == 'offer_job_button') {
				var msg = 'Good. Now you need to invest some resources in the company that is required for production. ' +
						  'Scroll down and find an Invest button that an arrow points to. Input 1 or more. And click Invest.';
				var coords = this.getElementCoords('#n_invest');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, '300px', '30%', false, '550px');
			}
			
			if(this.tutorials[2].active == true && event == 'n_invest_button') {
				this.rewardForTutorial(2);
				var msg = 'Congratulations! You are done with the tutorial. If this is your first time, ' +
						  'then you received a reward which you can collect on the homepage.';
						  
				var coords = this.getElementCoords('#reply_ok');
				
				this.removeTutorialArrow();
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			//get a job
			if(this.tutorials[3].active == true && (event == 'continue_button' || event == 'index_page')) {
				var msg = 'Click on the Economy';
				var coords = this.getElementCoords('#economy');
				
				$('body').append(this.tutorialArrow(coords));
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[3].active == true && (event == 'economy_menu' && this.getPageLocation() != 'company_manage')) {
				var msg = 'Go to Companies page';
				var coords = this.getElementCoords('#economy ul a:nth-child(4) li');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[3].active == true && event == 'companies_page') {
				var msg = 'Click on the Manage button in order to open company page and manage the company.';
				var coords = this.getElementCoords($('.manage').first(), true);
				
				this.tutorialArrow(coords, true);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', (coords['left'] - 400) + 'px', false, '300px', true);
			}
			
			if(this.tutorials[3].active == true && event == 'company_manage_page') {
				var msg = 'Click on the Offers button in order to view jobs that this company is offering.';
				var coords = this.getElementCoords('#offers', true);
				
				this.tutorialArrow(coords, true);
				this.displayPopUp(msg, coords['top'] + 'px', (coords['left'] - 400) + 'px', false, '300px', true);
			}
			
			if(this.tutorials[3].active == true && event == 'job_offers_button') {
				var msg = 'Click Apply to view available people for work.';
				var coords = this.getElementCoords($('.apply_for_job').first());
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[3].active == true && event == 'view_available_people_button') {
				var msg = 'Chose what Person you want to hire and then click Apply.';
				var coords = this.getElementCoords($('.apply_btn').first(), true);
				
				this.tutorialArrow(coords, true);
				this.displayPopUp(msg, coords['top'] - 100 + 'px', (coords['left'] - 350) + 'px', false, '300px', true);
			}
			
			if(this.tutorials[3].active == true && event == 'apply_for_job_button') {
				this.rewardForTutorial(3);
				var msg = 'Congratulations! You are done with the tutorial. If this is your first time, ' +
						  'then you received a reward which you can collect on the homepage.';
				
				var coords = this.getElementCoords('#reply_ok');
				
				this.removeTutorialArrow();
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			//work
			if(this.tutorials[4].active == true && (event == 'continue_button' || event == 'index_page')) {
				var msg = 'Click on the Economy';
				var coords = this.getElementCoords('#economy');
				
				$('body').append(this.tutorialArrow(coords));
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[4].active == true && (event == 'economy_menu' && this.getPageLocation() != 'work')) {
				var msg = 'Go to Work page';
				var coords = this.getElementCoords('#economy ul a:nth-child(14) li');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[4].active == true && event == 'work_page') {
				var msg = 'Click on the Work All button';
				var coords = this.getElementCoords('#work_all');
				
				this.tutorialArrow(coords);
				this.displayPopUp(msg, coords['top'] + 100 + 'px', coords['left'] + 'px', false, 'auto');
			}
			
			if(this.tutorials[4].active == true && event == 'work_all_button') {
				this.rewardForTutorial(4);
				var msg = 'Congratulations! You are done with the tutorial. If this is your first time, ' +
						  'then you received a reward which you can collect on the homepage.';
				
				this.removeTutorialArrow();
				this.displayPopUp(msg, '70px', '30%', false, '500px');
			}
		},
		
		getElementCoords: function(element, reverse = false) {
			var coords = $(element).offset();
			var width = $(element).css('width').replace(/\D/g, '');
			var height = $(element).css('height').replace(/\D/g, '');
			coords['top'] += parseFloat(height);
			if(!reverse) {
				coords['left'] += parseFloat(width);
			}
			
			return coords;
		},
		
		removeTutorialMsg: function() {
			$("#tutorial_popup_div").remove();//remove old
		},
		
		displayPopUp: function(description, top, left, ok_btn, width, reverse = false) {
			$("#tutorial_popup_div").remove();//remove old

			var tutorial_popup_div_css = 'width: ' + width + ';' +
										 'background-color: #6db0d0f2;' +
										 'box-shadow: 0px 2px 40px 4px #345879;' +
										 'font-family: Enriqueta;' +
										 'font-size: 20px;' +
										 'overflow: auto;' +
										 'border-radius: 10px;' +
										 'color: white;' +
										 'position: absolute;' +
										 'top: ' + top + ';' +
										 'left: ' + left + ';' +
										 'z-index: 3;'
										 ;
			
			var tutorial_popup_msg_css = 'padding: 5px;';
			
			var tpd_continue_cc = 'margin-left: 220px;' +
								  'margin-bottom: 10px';
			
			var btn = '';
			if(ok_btn) {
				btn = '<p class="button blue" id="tpd_continue" style="' + tpd_continue_cc + '">Ok</p>';
			}
			
			$('body').append('<div id="tutorial_popup_div" style="' + tutorial_popup_div_css + '">' +
							 '<p id="tutorial_popup_msg" style="' + tutorial_popup_msg_css + '">' + description + '</p>' +
							  btn +
							 '</div>'
							);
				
		},
		
		removeTutorialArrow: function() {
			$('#tutorial_arrow').remove();//remove old
		},
		
		tutorialArrow: function(coords, reverse = false) {
			var coords = coords;
			var left = coords['left'];
			var top = coords['top'];
			
			this.removeTutorialArrow();
			
			if(!reverse) {
				$('body').append('<svg width="100" height="100" style="position:absolute; z-index: 3; left:' + left + 
				   'px; top:' + top + 'px" id="tutorial_arrow">' +
				   '<line x1="10" y1="50" x2="4" y2="1" style="stroke:rgb(231, 115, 106);stroke-width:8" />' +
				   '<line x1="4" y1="4" x2="80" y2="80" style="stroke:rgb(231, 115, 106);stroke-width:8" />' +
				   '<line x1="1" y1="4" x2="50" y2="10" style="stroke:rgb(231, 115, 106);stroke-width:8" />' +
				   '</svg>');
			}
			else {
				$('body').append('<svg width="100" height="100" style="position:absolute; z-index: 3; left:' + (left - 100) + 
				   'px; top:' + top + 'px" id="tutorial_arrow">' +
				   '<line x1="90" y1="50" x2="98" y2="1" style="stroke:rgb(231, 115, 106);stroke-width:8"/>' +
				   '<line x1="98" y1="2" x2="20" y2="80" style="stroke:rgb(231, 115, 106);stroke-width:8" />' +
				   '<line x1="50" y1="10" x2="99" y2="2" style="stroke:rgb(231, 115, 106);stroke-width:8" />' +
				   '</svg>');
			}
		},
		
		getPageLocation: function() {
			if(document.location.href.match(/[^\/]+(?=\?)/)) {
				return document.location.href.match(/[^\/]+(?=\?)/);//with parameters
			}
			else {
				return document.location.href.match(/[^\/]+$/);
			}
		}
	};
	
	//activate tutorial
	$('.td_tutorial').on('click', function() {
		var tutorial = $(this).next().html();
		Tutorials.newTutorial(tutorial);
	});
	
	Tutorials.displayEndTutorialButton();
	$('body').on('click', '#end_tutorial', function() {
		Tutorials.endTutorial();
	});
	
	
	//general event
	$('body').on('click', '#tpd_continue', function() {
		Tutorials.isTutorialActivated('continue_button');
	});
	
	//events for create compant tutorial
	$('#economy').on('mouseover', function() {
		Tutorials.isTutorialActivated('economy_menu');
	});
	
	if(Tutorials.getPageLocation() == 'companies') {
		Tutorials.isTutorialActivated('companies_page');
	}
	
	if(Tutorials.getPageLocation() == 'index') {
		Tutorials.isTutorialActivated('index_page');
	}
	
	if(Tutorials.getPageLocation() == 'company_manage') {
		Tutorials.isTutorialActivated('company_manage_page');
	}
	
	if(Tutorials.getPageLocation() == 'work') {
		Tutorials.isTutorialActivated('work_page');
	}
});