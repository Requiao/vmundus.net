<?php
    function rememberMeCookies ($conn, $user_id, $ip) {
        $rm_token = hash('sha512', uniqid() . mt_rand(1000, 9999) . mt_rand(10000, 99999));
        $time_token = md5(uniqid());
        
        $query = "DELETE FROM remember_me WHERE user_id = '$user_id'";
        $conn->query($query);
        
        $query = "INSERT INTO remember_me VALUES ('$user_id', '$ip', '$rm_token', '$time_token', 
                  TRUE, CURRENT_DATE, CURRENT_TIME)";
        if(!$conn->query($query)) {
            return false;
        }

        setcookie("rm_token", $rm_token, time() + (86400 * 3), "/"); // 86400 = 1 day
        setcookie("t_token", $time_token, time() + (86400 * 3), "/"); // 86400 = 1 day
        setcookie("user", $user_id, time() + (86400 * 3), "/"); // 86400 = 1 day

        return true;
    }
?>