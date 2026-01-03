<?php
    $hour = date('H');

    switch($hour) {
        case 1: $hour_string = 'one'; break;
        case 2: $hour_string = 'two'; break;
        case 3: $hour_string = 'three'; break;
        case 4: $hour_string = 'four'; break;
        case 5: $hour_string = 'five'; break;
        case 6: $hour_string = 'six'; break;
        case 7: $hour_string = 'seven'; break;
        case 8: $hour_string = 'eight'; break;
        case 9: $hour_string = 'nine'; break;
        case 10: $hour_string = 'ten'; break;
        case 11: $hour_string = 'eleven'; break;
        case 12: $hour_string = 'twelve';break;
        case 13: $hour_string = 'thirteen'; break;
        case 14: $hour_string = 'fourteen'; break;
        case 15: $hour_string = 'fifteen'; break;
        case 16: $hour_string = 'sixteen'; break;
        case 17: $hour_string = 'seventeen'; break;
        case 18: $hour_string = 'eighteen'; break;
        case 19: $hour_string = 'nineteen'; break;
        case 20: $hour_string = 'twenty'; break;
        case 21: $hour_string = 'twenty_one'; break;
        case 22: $hour_string = 'twenty_two'; break;
        case 23: $hour_string = 'twenty_three'; break;
    }

    $mysqli = new mysqli("127.0.0.1", "u2401573_default", "P00YqU1Vbh52dfME", "u2401573_default"); // PROD
    //$mysqli = new mysqli("185.9.145.150", "w347048_site", "123qwe123", "w347048_site"); // TEST
    $mysqli->set_charset("utf8");
    $ip = $_SERVER['REMOTE_ADDR'];
    $hwid = $_GET['hwid'];

    $date = date('Y-m-d');

    if ($mysqli->connect_errno){
        exit(json_encode(array('error' => 1, 'message' => 'Ошибка подключения к БД')));
    } else {
        if (isset($_GET['key'])) {
            if (isset($_GET['hwid'])) {
                $query = "SELECT * FROM `qwe_keys` WHERE `key`='".$_GET['key']."'";
                $sql = mysqli_query($mysqli,$query) or die(mysqli_error());
                $myrow = mysqli_fetch_array($sql);

                $queryewq = "SELECT * FROM qwe_users WHERE `id`='".$myrow['acc_id']."'";
                $sqlewq = mysqli_query($mysqli,$queryewq) or die(mysqli_error());
                $myrowewq = mysqli_fetch_array($sqlewq);

                if (mysqli_num_rows($sql) > 0) {
                    $sql = $mysqli->query("SELECT * FROM `qwe_stats` WHERE `date`='{$date}'");

                    if ($sql->num_rows == 0) {
                        $sql = mysqli_query($mysqli,"INSERT INTO `qwe_stats`(`date`) VALUES ('{$date}')");
                    }
                    if($myrow['date_last_join_ymd'] != $date) { $sql = mysqli_query($mysqli,"UPDATE `qwe_stats` SET `unique_activations` = `unique_activations` + 1 WHERE `date` = '$date'"); }
                    if($myrow['hour_last_join'] != $hour) { $sql = mysqli_query($mysqli,"UPDATE `qwe_stats` SET `$hour_string` = `$hour_string` + 1 WHERE `date` = '$date'"); }
                    if($myrow['hwid'] == 'Not defined') { $sql = mysqli_query($mysqli,"UPDATE `qwe_keys` SET `hwid` = '".$hwid."' WHERE `key` = '".$_GET['key']."' LIMIT 1");
                    } else if($hwid != $myrow['hwid']) {
                        $sql = mysqli_query($mysqli,"INSERT INTO `qwe_logs`(`user`, `text`, `ip_address`) VALUES ('".$myrowewq['name']."', 'Ключ <strong>".$_GET['key']."</strong><br />&nbsp;Пытается авторизоваться под другим HWID: $hwid','$ip')");
                        die(json_encode(array('error' => 3, 'message' => 'Данный ключ привязан к другому HWID')));
                    }
                    
                    if($myrow['activated'] == '0') { $sql = mysqli_query($mysqli,"UPDATE `qwe_keys` SET `activated` = '1' WHERE `key` = '".$_GET['key']."' LIMIT 1"); }
                    
                    $sql = "INSERT INTO `qwe_logs`(`user`, `text`, `ip_address`) VALUES ('".$myrowewq['name']."', 'Ключ&nbsp;<strong>".$_GET['key']."</strong>&nbsp;авторизовался в скрипт.<br />','{$ip}');
                            UPDATE `qwe_keys` SET `hour_last_join` = '{$hour}', `date_last_join`=now(),`date_last_join_ymd`='{$date}', `nickname`='".$_GET['nickname']."', `hostname`='".$_GET['hostname']."' WHERE `key` = '".$_GET['key']."' LIMIT 1; ";
                    $mysqli->multi_query($sql);
                    echo json_encode(array('error' => 0, 'message' => 'Успешно подключено'));
                } else {
                    echo json_encode(array('error' => 2, 'message' => 'Данный ключ не существует'));
                }
            }
        }
    }
?>
