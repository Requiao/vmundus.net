<?php
    function findSimilarColors($base_color, $colors) {
        foreach($colors AS $color) {
            //check if color is allowed
            $pattern = '/[rgb\(\) ]?/';
            $rgb_color = preg_split('/,/', preg_replace($pattern, '', $color));
            $rgb_base_color = preg_split('/,/', preg_replace($pattern, '', $base_color));

            $match = 0;
            for($x = 0; $x < 3; $x++) {
                if($rgb_color[$x] <= $rgb_base_color[$x] + 10 && $rgb_color[$x] >= $rgb_base_color[$x] - 10) {
                    $match++;
                }
            }
            if($match >= 3) {
                return false;
            }
        }
        return true;
    }
?>