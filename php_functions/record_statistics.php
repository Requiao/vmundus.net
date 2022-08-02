<?php
	function countryDailyIncome($country_id, $currency_id, $tax) {
		global $conn;
		$hour = date('G');
		$query = "SELECT * FROM country_daily_income WHERE country_id = '$country_id' AND currency_id = '$currency_id' 
				  AND date = CURRENT_DATE AND HOUR(time) = '$hour'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE country_daily_income SET amount = (SELECT amount FROM
					 (SELECT amount FROM country_daily_income WHERE country_id = '$country_id' AND currency_id = '$currency_id' 
					  AND date = CURRENT_DATE AND HOUR(time) = '$hour') AS old_amount) + '$tax' 
					  WHERE country_id = '$country_id' AND currency_id = '$currency_id' 
					  AND date = CURRENT_DATE AND HOUR(time) = '$hour'";
		}
		else {
			$query = "INSERT INTO country_daily_income VALUES('$country_id', '$currency_id', '$tax', CURRENT_DATE, CURRENT_TIME)";
		}
		$conn->query($query);
	}
	
	function countryDailyProductIncome($region_id, $product_id, $tax) {
		global $conn;
		$hour = date('G');
		$current_time = "$hour:00:00";
		$query = "SELECT * FROM country_product_income WHERE region_id = '$region_id' AND product_id = '$product_id' 
				  AND date = CURRENT_DATE AND HOUR(time) = '$hour'";
		$result = $conn->query($query);
		if($result->num_rows == 1) {
			$query = "UPDATE country_product_income SET amount = (SELECT * FROM (SELECT amount FROM country_product_income WHERE
					  region_id = '$region_id' AND product_id = '$product_id' AND date = CURRENT_DATE AND HOUR(time) = '$hour') 
					  AS temp) + $tax
					  WHERE region_id = '$region_id' AND product_id = '$product_id'
					  AND date = CURRENT_DATE AND HOUR(time) = '$hour'";
		}
		else {
			$query = "INSERT INTO country_product_income VALUES ('$region_id', '$product_id', '$tax',
					  CURRENT_DATE, '$current_time')";
		}
		$conn->query($query);
	}
	
	function countryPeopleBorn($person_id, $country_id) {
		global $conn;
		$hour = date('G');
		$current_time = "$hour:00:00";
		//country_id is the country of the user's citizesnhip
		$query = "INSERT INTO country_people_born VALUES ('$person_id', '$country_id', CURRENT_DATE, '$current_time')";
		$conn->query($query);
	}
	
	function countryPeopleDie($person_id, $country_id, $years) {
		global $conn;
		$hour = date('G');
		$current_time = "$hour:00:00";
		//country_id is the country of the user's citizesnhip
		$query = "INSERT INTO country_people_die VALUES ('$person_id', '$country_id', '$years', CURRENT_DATE, '$current_time')";
		$conn->query($query);
	}
	
	function recordCountryPopulation($country_id, $population) {
		global $conn;
		//country_id is the country of the user's citizesnhip
		$query = "INSERT INTO country_current_population VALUES ('$country_id', '$population', CURRENT_DATE, CURRENT_TIME)";
		$conn->query($query);
	}
	
	class CountryDailyProductivity {
		private $conn;
		private $region_id;
		private $product_id;
		private $productivity;
		
		public function __construct($region_id, $product_id, $productivity) {
			global $conn;
			$this->conn = $conn;
			$this->region_id = $region_id;
			$this->product_id = $product_id;
			$this->productivity = $productivity;
		}
		
		public function recordCountryDailyProductivity () {
			$hour = date('G');
			$current_time = "$hour:00:00";
			$query = "SELECT * FROM region_product_productivity WHERE region_id = '$this->region_id' AND product_id = '$this->product_id' 
					  AND date = CURRENT_DATE AND HOUR(time) = '$hour'";
			$result = $this->conn->query($query);
			if($result->num_rows == 1) {
				$query = "UPDATE region_product_productivity SET productivity = (SELECT * FROM (SELECT productivity FROM region_product_productivity
						  WHERE region_id = '$this->region_id' AND product_id = '$this->product_id' AND date = CURRENT_DATE AND HOUR(time) = '$hour') 
						  AS temp) + '$this->productivity'
						  WHERE region_id = '$this->region_id' AND product_id = '$this->product_id'
						  AND date = CURRENT_DATE AND HOUR(time) = '$hour'";
			}
			else {
				$query = "INSERT INTO region_product_productivity VALUES ('$this->region_id', '$this->product_id', '$this->productivity', 
						  CURRENT_DATE, '$current_time')";
			}
			$this->conn->query($query);
		}
	}
?>