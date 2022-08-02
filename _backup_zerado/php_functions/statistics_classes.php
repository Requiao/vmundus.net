<?php
	
	
	#==============================#
	#==============================#
	class BarGraph {
		private $stat_width = 0;
		private $stat_hight = 0;
		private $levels = 0;//steps
		private $data = array();
		private $space_from_bottom = 20;
		private $space_from_top = 20;
		private $graph_background_color = '#fff';
		private $space_between_bars = 0;
		private $bars_fill = '#a5a5a5';
		private $bar_signs_font_size = 17;
		private $bars_width = 0;
		private $bars_signs_font_family = 'Space Mono, monospace';
		private $bar_signs_color = '#000';
		private $markup_color = '#a5a5a5';
		private $markup_font_size = 17;
		private $murkup_font_family = 'Enriqueta';
		private $svg_class = '';
		private $svg_id = '';
		
		public function __construct($stat_width, $stat_hight, $data, $levels) {
			$this->stat_width = $stat_width;
			$this->stat_hight = $stat_hight;
			$this->data = $data;
			$this->levels = $levels;
		}
		
		public function generateGraph() {
			$max_value = $this->getMaxValue();
			$space_from_left = $this->getSpaceFromLeft();
			$graph_width = $this->stat_width - $space_from_left - 5;
			$graph_height = $this->stat_hight - $this->space_from_bottom;
			$graph_from_left = $this->stat_width - $graph_width;

			echo '<svg class="' . $this->svg_class . '" id="' . $this->svg_id .
				 '" width="' . $this->stat_width . '" height="' . $this->stat_hight . '">' .
				 '<rect x="' . $graph_from_left . '" width="' . $graph_width . 
				 '" height="' . $graph_height . '" style="fill:' . $this->graph_background_color . ';stroke: rgb(0,0,0);"/>';
			
			$step = ($graph_height - $this->space_from_top) / $this->levels;//step for horizontal lines and numbers on the left
			$number_step = $max_value / $this->levels;//num step of numbers on the left
			
			$start_y = $graph_height - $step;
			$text_step = $graph_height + 5 - $step;//+5 to center horizontal relatively to horiz lines
			$display_value = $number_step;
			
			for($x = 0; $x < $this->levels; $x++) {
				$digits = strlen(round($display_value));
				$text_x = $space_from_left - (8 * $digits);
				
				echo '<text x="' . $text_x . '" y="' . $text_step . '" fill="' . $this->markup_color . 
					 '" font-family="' . $this->murkup_font_family . '" font-size="' . $this->markup_font_size . 
					 '">' . round($display_value) . '</text>';
			
				echo '<line x1="' . $space_from_left . '" y1="' . $start_y  . '" x2="' . $this->stat_width . '" y2="' . $start_y  . '"' .
					 'style="stroke:' . $this->markup_color . ';stroke-width:1""/>';
				
				$start_y -= $step;
				$text_step -= $step;
				$display_value += $number_step;
			}
			
			//display bars
			$items_in_data = $this->getNumberOfItems();
			$default_perc_of_bar_width = 0.3; //30%
			
			if($this->bars_width == 0) {//calc default bar width if not set
				$this->bars_width = ($graph_width - ($graph_width * $default_perc_of_bar_width)) / $items_in_data;
			}
			
			if($this->space_between_bars == 0) {
				$total_space_between_bars = $graph_width - ($this->bars_width * $items_in_data);
				$this->space_between_bars = $total_space_between_bars / $items_in_data;
			}
			
			$max_bar_height = $graph_height - $this->space_from_top;
			if($max_value > 0) {
				$step = round($max_bar_height / $max_value, 10);
			}
			else {//no data
				$step = 0;
			}
			
			$from_left = ($this->space_between_bars / 2) + $graph_from_left;
			for($x = 0; $x < $items_in_data; $x++) {
				if($this->data[$x]['value'] == 0) {
					$height = 0;
				}
				else {
					$height = round($this->data[$x]['value']  * $step, 10); //find height of the char
				}
				$from_top = $graph_height - $height;
				
				$text_from_left = $from_left + (($this->bars_width - (strlen($this->data[$x]['value']) * 8)) /2);
				
				//bar signs
				echo '<text x="' . $text_from_left . '" y="' . ($from_top - 5) . 
					 '" fill="' . $this->bar_signs_color . '" font-family="' . $this->bars_signs_font_family . 
					 '" font-size="' . $this->bar_signs_font_size . '">' . $this->data[$x]['value'] . '</text>';
				
				//bar murkup name
				$name_from_left = $from_left + (($this->bars_width - (strlen($this->data[$x]['name']) * 8)) /2);
				echo '<text x="' . $name_from_left . '" y="' . ($this->stat_hight - 5) . 
					 '" fill="' . $this->markup_color . '" font-family="' . $this->murkup_font_family . 
					 '" font-size="' . $this->markup_font_size . '">' . $this->data[$x]['name'] . '</text>';
				
				//bar
				echo '<rect width="' . $this->bars_width . '" height="' . $height . '" x="' . $from_left . '" y="' . $from_top . '" 
					  style="fill:' . $this->bars_fill . ';"/>';
				
				$from_left += $this->space_between_bars + $this->bars_width;
			}
			
			echo "\n\t\t" . '</svg>';
		}
		
		private function getMaxValue() {
			$max_value = 0;
			foreach($this->data as $value) {
				if($value['value'] > $max_value) {
					$max_value = $value['value'];
				}
			}
			
			return $max_value;
		}
		
		private function getSpaceFromLeft () {
			return strlen($this->getMaxValue()) * 8;//8px per char
		}
		
		private function getNumberOfItems() {
			return count($this->data);
		}
		
		public function setGraphBackgroundColor($color) {
			$this->graph_background_color = $color;
		}
		
		public function setBarsBackgroundColor($color) {
			$this->bars_fill = $color;
		}
		
		public function setMarkupFontSize($font_size) {
			$this->markup_font_size = $font_size;
		}
		
		public function setSignsFontSize($font_size) {
			$this->bar_signs_font_size = $font_size;
		}
		
		public function setBarsSignFontFamily($font_family) {
			$this->bars_signs_font_family = $font_family;
		}
		
		public function setMurkupFontFamily($font_family) {
			$this->murkup_font_family = $font_family;
		}
		
		public function setBarsWidth($bars_width) {
			$this->bars_width = $bars_width;
		}
		
		public function setSpaceBetweenBars($space_between_bars) {
			$this->space_between_bars = $space_between_bars;
		}
		
		public function setSvgClass($name) {
			$this->svg_class = $name;
		}
		
		public function setSvgId($name) {
			$this->svg_id = $name;
		}
	}
	
	
	#==============================#
	#==============================#
	class HorizontalBarGraph {
		private $stat_width = 0;
		private $stat_hight = 0;
		private $levels = 0;//steps
		private $data = array();
		private $space_from_bottom = 20;
		private $graph_background_color = '#fff';
		private $space_between_bars = 0;
		private $bars_fill = '#a5a5a5';
		private $bar_signs_font_size = 17;
		private $bars_width = 0;
		private $bars_signs_font_family = 'Space Mono, monospace';
		private $bar_signs_color = '#000';
		private $markup_color = '#a5a5a5';
		private $markup_font_size = 17;
		private $murkup_font_family = 'Enriqueta';
		private $svg_class = '';
		private $svg_id = '';
		
		public function __construct($stat_width, $stat_hight, $data, $levels) {
			$this->stat_width = $stat_width;
			$this->stat_hight = $stat_hight;
			$this->data = $data;
			$this->levels = $levels;
		}
		
		public function generateGraph() {
			$max_value = $this->getMaxValue();
			$graph_from_left = $this->getSpaceFromLeft();
			$graph_width = $this->stat_width - $graph_from_left;
			$graph_height = $this->stat_hight - $this->space_from_bottom;
			$space_from_right = $this->getSpaceFromRight() + 20;//+20 space for extra words padding
			
			echo '<svg class="' . $this->svg_class . '" id="' . $this->svg_id . 
				 '" width="' . $this->stat_width . '" height="' . $this->stat_hight . '">' .
				 '<rect x="' . $graph_from_left . '" width="' . $graph_width . 
				 '" height="' . $graph_height . '" style="fill:' . $this->graph_background_color . ';stroke: rgb(0,0,0);"/>';
			
			//display bars
			$items_in_data = $this->getNumberOfItems();
			$default_perc_of_bar_width = 0.3; //30%
			
			if($this->bars_width == 0) {//calc default bar width if not set
				$this->bars_width = ($graph_height - ($graph_height * $default_perc_of_bar_width)) / $items_in_data;
			}
			
			if($this->space_between_bars == 0) {
				$total_space_between_bars = $graph_height - ($this->bars_width * $items_in_data);
				$this->space_between_bars = $total_space_between_bars / $items_in_data;
			}
			
			$max_bar_height = $graph_width - $space_from_right;//give extra 10 space for text
			if($max_value > 0) {
				$step = round($max_bar_height / $max_value, 10);
			}
			else {//no data
				$step = 0;
			}
			
			$from_top = $graph_height - ($this->space_between_bars / 2) - $this->bars_width;
			for($x = 0; $x < $items_in_data; $x++) {
				if($this->data[$x]['value'] == 0) {
					$height = 0;
				}
				else {
					$height = round($this->data[$x]['value']  * $step, 10);
				}
				
				$text_from_top = $from_top + (($this->bars_width + 10) / 2);//10 is the default text height
				//bar signs
				echo '<text x="' . ($height + $graph_from_left + 10) . '" y="' . $text_from_top . 
					 '" fill="' . $this->bar_signs_color . '" font-family="' . $this->bars_signs_font_family . 
					 '" font-size="' . $this->bar_signs_font_size . '">' . $this->data[$x]['value'] . '</text>';
				
				
				//bar murkup name
				$name_from_left = $graph_from_left - (strlen($this->data[$x]['name']) * 8);
				echo '<text x="' . $name_from_left . '" y="' . $text_from_top . 
					 '" fill="' . $this->markup_color . '" font-family="' . $this->murkup_font_family . 
					 '" font-size="' . $this->markup_font_size . '">' . $this->data[$x]['name'] . '</text>';
				
				
				//bar
				echo '<rect width="' . $height . '" height="' . $this->bars_width . '" x="' . $graph_from_left . 
					 '" y="' . $from_top . '" style="fill:' . $this->bars_fill . ';"/>';
				
				$from_top = $from_top - $this->bars_width - $this->space_between_bars;
			}
			
			//display vertical lines and markup from left
			$step = ($graph_width - $space_from_right) / $this->levels;//step for vertical lines and numbers on the buttom
			$number_step = $max_value / $this->levels;//num step of numbers on the left
			
			$start_x = $graph_from_left + $step;

			$display_value = $number_step;
			$text_x = $start_x;
			
			for($x = 0; $x < $this->levels; $x++) {
				echo '<text x="' . ($text_x - ((8 * strlen(round($display_value))) / 2)) . 
					 '" y="' . ($this->stat_hight - 5) . '" fill="' . $this->markup_color . 
					 '" font-family="' . $this->murkup_font_family . '" font-size="' . $this->markup_font_size . 
					 '">' . round($display_value) . '</text>';
			
				echo '<line x1="' . $start_x . '" y1="0" x2="' . $start_x . '" y2="' . $graph_height . '"' .
					 'style="stroke:' . $this->markup_color . ';stroke-width:1""/>';
				
				$start_x += $step;
				$text_x += $step;
				$display_value += $number_step;
			}
			
			echo "\n\t\t" . '</svg>';
		}
		
		private function getMaxValue() {
			$max_value = 0;
			foreach($this->data as $value) {
				if($value['value'] > $max_value) {
					$max_value = $value['value'];
				}
			}
			
			return $max_value;
		}

		private function getSpaceFromRight() {
			return strlen($this->getMaxValue()) * 8;//8px per char
		}
		
		private function getSpaceFromLeft() {
			$max_chars = 0;
			foreach($this->data as $value) {
				if(strlen($value['name']) > $max_chars) {
					$max_chars = strlen($value['name']);
				}
			}
			return $max_chars * 8;//8px per char
		}
		
		private function getNumberOfItems() {
			return count($this->data);
		}
		
		public function setGraphBackgroundColor($color) {
			$this->graph_background_color = $color;
		}
		
		public function setBarsBackgroundColor($color) {
			$this->bars_fill = $color;
		}
		
		public function setMarkupFontSize($font_size) {
			$this->markup_font_size = $font_size;
		}
		
		public function setSignsFontSize($font_size) {
			$this->bar_signs_font_size = $font_size;
		}
		
		public function setBarsSignFontFamily($font_family) {
			$this->bars_signs_font_family = $font_family;
		}
		
		public function setMurkupFontFamily($font_family) {
			$this->murkup_font_family = $font_family;
		}
		
		public function setBarsWidth($bars_width) {
			$this->bars_width = $bars_width;
		}
		
		public function setSpaceBetweenBars($space_between_bars) {
			$this->space_between_bars = $space_between_bars;
		}
		
		public function setSvgClass($name) {
			$this->svg_class = $name;
		}
		
		public function setSvgId($name) {
			$this->svg_id = $name;
		}
	}
	
	
	#==============================#
	#==============================#
	class LineGraph {
		private $stat_width = 0;
		private $stat_hight = 0;
		private $levels = 0;//steps
		private $data = array();
		private $space_from_bottom = 50;
		private $space_from_top = 20;
		private $graph_background_color = '#fff';
		private $space_between_dots = 0;
		private $signs_font_size = 17;
		private $signs_font_family = 'Space Mono, monospace';
		private $markup_color = '#a5a5a5';
		private $markup_font_size = 17;
		private $murkup_font_family = 'Enriqueta';
		private $svg_class = '';
		private $svg_id = '';
		private $signs_lines_colors = array();
		private $description = array();
		
		public function __construct($stat_width, $stat_hight, $data, $levels, $description) {
			$this->stat_width = $stat_width;
			$this->stat_hight = $stat_hight;
			$this->data = $data;
			$this->levels = $levels;
			$this->description = $description;
			$this->SetDefaultSignsLinesColors();
		}
		
		public function generateGraph() {
			$max_value = $this->getMaxValue();
			$space_from_left = $this->getSpaceFromLeft();
			$graph_width = $this->stat_width - $space_from_left - 5;
			$graph_height = $this->stat_hight - $this->space_from_bottom;
			$graph_from_left = $this->stat_width - $graph_width;

			echo '<svg class="' . $this->svg_class . '" id="' . $this->svg_id .
				 '" width="' . $this->stat_width . '" height="' . $this->stat_hight . '">' .
				 '<rect x="' . $graph_from_left . '" width="' . $graph_width . 
				 '" height="' . $graph_height . '" style="fill:' . $this->graph_background_color . ';stroke: rgb(0,0,0);"/>';
			
			//display horizontal line break and quantity break
			$step = ($graph_height - $this->space_from_top) / $this->levels;//step for horizontal lines and numbers on the left
			$number_step = $max_value / $this->levels;//num step of numbers on the left
			
			$start_y = $graph_height - $step;
			$text_step = $graph_height + 5 - $step;//+5 to center horizontal relatively to horiz lines
			$display_value = $number_step;
			
			for($x = 0; $x < $this->levels; $x++) {
				$digits = strlen(round($display_value));
				$text_x = $space_from_left - (8 * $digits);
				
				echo '<text x="' . $text_x . '" y="' . $text_step . '" fill="' . $this->markup_color . 
					 '" font-family="' . $this->murkup_font_family . '" font-size="' . $this->markup_font_size . 
					 '">' . round($display_value) . '</text>';
			
				echo '<line x1="' . $space_from_left . '" y1="' . $start_y  . '" x2="' . $this->stat_width . '" y2="' . $start_y  . '"' .
					 'style="stroke:' . $this->markup_color . ';stroke-width:1""/>';
				
				$start_y -= $step;
				$text_step -= $step;
				$display_value += $number_step;
			}
			
			//calculate default space between dots
			$items_in_data = $this->getNumberOfItems();
			
			if($this->space_between_dots == 0) {
				$this->space_between_dots = $graph_width / $items_in_data;
			}

			//display murkup names
			$from_left = (($this->space_between_dots / 2) + $graph_from_left);
			for($x = 0; $x < count($this->data['name']); $x++) {
				echo '<text x="' . ($from_left - ((strlen($this->data['name'][$x]) * 8) / 2)) . '" y="' . ($graph_height + 20) . 
					 '" fill="' . $this->markup_color . '" font-family="' . $this->murkup_font_family . 
					 '" font-size="' . $this->markup_font_size . '">' . $this->data['name'][$x] . '</text>';
					  
				$from_left += $this->space_between_dots;
			}
			
			//display dots & lines
			$max_dot_height = $graph_height - $this->space_from_top;
			
			$max_dot_height = $graph_height - $this->space_from_top;
			if($max_value > 0) {
				$step = round($max_dot_height / $max_value, 10);
			}
			else {//no data
				$step = 0;
			}
			
			for($x = 0; $x < $this->getNumberOfSubarrays(); $x++) {
				$polyline_coords = '';
				$from_left = ($this->space_between_dots / 2) + $graph_from_left;
				for($u = 0; $u < count($this->data[0]); $u++) {//polyline
					if($this->data[$x][$u] > 1000) {//round to reduce space consumption
						$this->data[$x][$u] = round($this->data[$x][$u]);
					}
				
					if($this->data[$x][$u] == 0) {
						$height = 0;
					}
					else {
						$height = round($this->data[$x][$u]  * $step, 10); //find height of the char
					}
					
					$from_top = $graph_height - $height;
					
					//dot signs
					$text_from_left = $from_left + ((strlen($this->data[$x][$u]) * 8) / 2);
					
					echo '<text x="' . $text_from_left . '" y="' . ($from_top - 5) . 
						 '" fill="' . $this->signs_lines_colors[$x] . '" font-family="' . $this->signs_font_family . 
						 '" font-size="' . $this->signs_font_size . '">' . $this->data[$x][$u] . '</text>';				  
				
					echo '<circle cx="' .  $from_left . '" cy="' . $from_top . '" r="3" fill="' . $this->signs_lines_colors[$x] . '"/>';	
					
					$polyline_coords .= "$from_left,$from_top ";
					$from_left += $this->space_between_dots;
				}
				echo '<polyline points="' . $polyline_coords . '" style="fill:none;stroke:' . 
					  $this->signs_lines_colors[$x] . ';stroke-width:2"/>';
			}
			
			//description
			$rect_width = 50;
			$margin = 30;
			
			$rect_from_left = $graph_from_left;
			$text_from_left = $rect_from_left + $rect_width + 5;//5 space between rext & text
			$from_top = $this->stat_hight - 15;
			
			for($x = 0; $x < $this->getNumberOfSubarrays(); $x++) {
				echo '<rect x="' . $rect_from_left . '" y="' . $from_top . '" width="' . $rect_width . 
					 '" height="10" fill="' . $this->signs_lines_colors[$x] . '"/>' .
					 
					 '<text x="' . $text_from_left . '" y="' . ($from_top + 10) . 
					 '" fill="' . $this->markup_color . '" font-family="' . $this->murkup_font_family . 
					 '" font-size="' . $this->markup_font_size . '"> - ' . $this->description[$x] . '</text>';
				
				$step = (strlen($this->description[$x]) * 8) + $rect_width + $margin;
				$rect_from_left += $step;
				$text_from_left += $step;
			}
			echo "\n\t\t" . '</svg>';
		}
		
		private function getNumberOfSubarrays() {//w/o name subarray
			return count($this->data) - 1;
		}
		
		private function getMaxValue() {
			$max_value = 0;
			for($x = 0; $x < $this->getNumberOfSubarrays(); $x++) {//- 1 is for the name subarray
				for($u = 0; $u < count($this->data[$x]); $u++) {
					if($this->data[$x][$u] > $max_value) {
						$max_value = $this->data[$x][$u];
					}
				}
			}
			
			return $max_value;
		}
		
		private function getSpaceFromLeft () {
			return strlen($this->getMaxValue()) * 8;//8px per char
		}
		
		private function getNumberOfItems() {
			return count($this->data['name']);
		}
		
		public function setGraphBackgroundColor($color) {
			$this->graph_background_color = $color;
		}
		
		public function setLinesColor($color) {
			$this->lines_color = $color;
		}
		
		public function setMarkupFontSize($font_size) {
			$this->markup_font_size = $font_size;
		}
		
		public function setSignsFontSize($font_size) {
			$this->signs_font_size = $font_size;
		}
		
		public function setSignFontFamily($font_family) {
			$this->signs_font_family = $font_family;
		}
		
		public function setMurkupFontFamily($font_family) {
			$this->$murkup_font_family = $font_family;
		}
		
		public function setSpaceBetweenDots($space_between_dots) {
			$this->space_between_dots = $space_between_dots;
		}
		
		public function setSvgClass($name) {
			$this->svg_class = $name;
		}
		
		public function setSvgId($name) {
			$this->svg_id = $name;
		}
		
		public function setSignsLinesColors($colors) {
			$this->signs_lines_colors = $colors;
		}
		
		private function SetDefaultSignsLinesColors() {
			$default_color = 'black';
			for($x = 0; $x < $this->getNumberOfSubarrays(); $x++) {
				$this->signs_lines_colors[$x] = $default_color;
			}
		}
	}
	/* array built for the class
	$data = array(
					'name'=>array("Day 1", "Day 2", "Day 3", "Day 4", "Day 5"), 
					array(10, 25, 11, 27, 17), 
					array(1, 2.5, 1.1, 2.7, 1.7), 
					array(0.5, 5, 2, 1.5, 2.4)
				);
	$colors[0] = '#4a674a';
	$colors[1] = '#a93434';
	$colors[2] = '#0074d9';
	
	$description[0] = 'Current Population';
	$description[1] = 'Passed Away People';
	$description[2] = 'New People';
	*/
?>