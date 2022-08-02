$(document).ready( function() {
	$('#calculate').on('click', function() {
		var salary = $("#salary_input").val();
		var skill = $("#skill_input").val();
		var revenue = $("#revenue_input").val();
		if(salary < 0 || isNaN(salary) || salary == '') {
			salary = parseFloat(0);
			$("#salary_input").val(salary);
		}
		if(skill <= 0 || isNaN(skill) || skill > 25 || skill == '') {
			skill = parseFloat(1);
			$("#skill_input").val(skill);
		}
		if(revenue < 0 || isNaN(revenue) || revenue == '') {
			revenue = parseFloat(0);
			$("#revenue_input").val(revenue);
		}
		CalculateProductPrice.setSalary(parseFloat(salary));
		CalculateProductPrice.setSkill(parseFloat(skill));
		CalculateProductPrice.setRevenue(parseFloat(revenue));
		CalculateProductPrice.reset();
		
		//set user prices
		var price = 0;
		$('.price_input').each(function() {
			var parent_id = $(this).next().next().attr('id');
			price = $(this).val();
			if(price >= 0 && !isNaN(salary) && price != '') {
				CalculateProductPrice.setUserPrices(parent_id, price);
			}
		});
		
		//CalculateProductPrice.salary = salary;
		CalculateProductPrice.calculatePrice();
	});
	
	//set energy type
	$('.type_menu').on('click', function() {
		var type = $(this).next().html();
		//reset
		$('#energy_types_menu_div .type_menu').css('backgroundColor', '#7a3232');
		for(var en_type in energy_types) {
			energy_types[en_type].checked = false;
		}
		
		//set
		energy_types["_" + type].checked = true;
		$(this).css('backgroundColor', '#327a37');
		CalculateProductPrice.setEnergyType("_" + type);
	});

	var CalculateProductPrice = {
		salary: 0, //default
		revenue: 0, //default
		price: 0,//store price
		prod_price_id: '',//store of product that depends from
		set_price: true,//if all products that depends from have price set, then set price for itself too
		run_again: false,//if at least 1 prod price cannot be calculated, run function again
		is_price_calculated: false, //if price was not calculated in loop1 for at least 1 product, then there's not enough data.
		energy_type: '_28',//default, coal power plant
		skill: '_1', //worker's skill
		
		calculatePrice: function () {
			this.run_again = false;
			this.is_price_calculated = false;
			
			loop1:
			for(var product_info in obj) {
				if(obj[product_info].info.calculated) {//if price is calculated, continue
					continue;
				}
				this.set_price = true;//reset for new product
				this.price = 0;//reset price for new product
				
				loop2:
				for(var product in obj[product_info]) {
					if(obj[product_info][product].amount) {
						if(obj[product_info][product].energy) {//if energy, check if it's chosen by user/default
							if(this.energy_type != obj[product_info][product].product_id) {
								continue;//skipp if not
							}
						}
						this.prod_price_id = obj[product_info][product].product_id;
						if(obj[this.prod_price_id].info.set) {//user price is in priority
							this.price += obj[product_info][product].amount * obj[this.prod_price_id].info.user_price;
						}
						else if(obj[this.prod_price_id].info.calculated) {
							this.price += obj[product_info][product].amount * obj[this.prod_price_id].info.calculated_price;
						}
						else {//not all products that depends have set price. cannot calculate price. break
							this.set_price = false;//cannot set price, not enough data
							this.run_again = true;//if at least 1 prod price cannot be calculated, run function again
							break loop2;
						}
					}
				}
				if(this.set_price) {//set price
					//set new productivity
					var new_productivity = (obj[product_info].info.production * (skills_bonus[this.skill].bonus / 100)) +
											obj[product_info].info.production;
					$('#' + obj[product_info].info.productivity_id).html(parseFloat(new_productivity.toFixed(2)).toString());
					
					this.price += this.salary + this.revenue;
					this.price /= (obj[product_info].info.production * (skills_bonus[this.skill].bonus / 100)) +
								   obj[product_info].info.production;
					this.price = this.price.toFixed(2);
					obj[product_info].info.calculated_price = this.price;
					obj[product_info].info.calculated = true;
					$('#' + obj[product_info].info.id).html(this.price);
					this.is_price_calculated = true;//calculated for at least one product.
					
					
				}
			}

			if(this.run_again && this.is_price_calculated) {//runs func again if at least 1 prod failed to calculate due to missing parameters.
				this.calculatePrice();
			}
			else if(!this.is_price_calculated) {//if not calculated for at least 1 product. not enough data. display missing data.
				this.displayMissingData();
			}
		},
		
		displayMissingData: function() {
			loop1:
			for(var product_info in obj) {
				if(!obj[product_info].info.calculated) {//if price is not calculated, display error msg.
					$('#' + obj[product_info].info.id).html('Not enough data');
					
					//mark on prod icons missing data
					if(!obj[product_info].info.set) {
						//if energy and default/user defined energy is set, skipp
						if(!obj[product_info].info.energy || !obj[this.energy_type].info.set) {
							$('.' + obj[product_info].info.icon_class + ' i').css('display', 'block');
						}
					}
				}
			}
		},
		
		reset: function() {
			salary = 0;
			for(var product_info in obj) {
				obj[product_info].info.set = false;
				obj[product_info].info.calculated = false;
				$('#' + obj[product_info].info.id).empty();
				$('.' + obj[product_info].info.icon_class + ' i').css('display', 'none');
			}
		},
		
		setUserPrices: function(parent_id, price) {
			obj[parent_id].info.user_price = price;
			obj[parent_id].info.set = true;
		},
		
		setSalary: function(salary) {
			this.salary = salary;
		},
		
		setEnergyType: function (type) {
			this.energy_type = type;
		},
		
		setSkill: function (skill) {
			this.skill = "_" + skill;
		},
		
		setRevenue: function(revenue) {
			this.revenue = revenue;
		}
	};
});