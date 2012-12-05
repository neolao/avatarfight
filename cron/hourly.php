<?php
include_once dirname(__FILE__).'/../inc/cli.php';

$configurationPath = dirname(__FILE__).'/../config/avatars.ini';
$avatars = parse_ini_file($configurationPath, true);

function requestPost($url, $content, $cookie = null)
{
    $options = array();
    $http = array(
        'method'    => 'POST',
        'header'    => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content'   => $content
    );

    if (!is_null($cookie)) {
        $http['header'] .= 'Cookie: '.$cookie."\r\n";
    }

    $options['http'] = $http;
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $result = json_decode($result);

    return array(
        'headers' => $http_response_header,
        'result' => $result
    );
}




foreach ($avatars as $login => $avatarConfig) {
    $password = $avatarConfig['password'];

    // Login
    $content    = 'CTT={"TS":1354497168845,"VER":"4.1","NAME":"'.$login.'","OS":4,"PWD":"'.$password.'","DEV":"b52ebb81b1394be3dbbd99c3c9667f36b0f76de9","LANG":"en"}&CRC=1f9831a33682e24bbe5e05fc1f632628';
    $response   = requestPost('http://avafight.appspot.com/login?ACT=1', $content);
    $headers    = $response['headers'];
    $result     = $response['result'];

    // Get the cookie
    // ex. Set-Cookie: JSESSIONID=kfAu4l9ilKFtKBkBWloBIw;Path=/
    $cookie = '';
    foreach ($headers as $header) {
        if (preg_match('/JSESSIONID=([^;]+)/', $header, $matches)) {
            $cookie = $matches[1];
        }
    }

    // Variables
    $TS = $result->MAIN->TS;

    // Display informations about the avatar
    $avatar = $result->MAIN->AVA;
    echo getColoredString($avatar->NAME, 'yellow');
    echo ', Level ', getColoredString($avatar->LV, 'yellow');
    echo ', Heart Point ', getColoredString($avatar->HP, 'yellow');
    echo ', Agility ', getColoredString($avatar->AGL, 'yellow');
    echo ', Strengh ', getColoredString($avatar->STR, 'yellow');
    echo ', Speed ', getColoredString($avatar->SPD, 'yellow');
    echo ', Energy ', getColoredString($avatar->ENGY, 'yellow');
    echo "\n";
    echo 'JSESSIONID: ', getColoredString($cookie, 'yellow'), "\n";


    // Get rivals
    $content    = 'CTT={"CNT":24,"TS":1354738337619}&CRC=38f6e657bc9ea3dbc1384d3b850ffaef';
    $response   = requestPost('http://avafight.appspot.com/list_rival?ACT=0', $content, 'JSESSIONID='.$cookie);
    $headers    = $response['headers'];
    $result     = $response['result'];
    $rivals     = $result->MAIN->RVL;

    // Fight rivals
    echo "\n";
    echo 'Rivals', "\n";
    echo '------', "\n";
    $energy = $avatar->ENGY;
    if ($energy > 0) {
        foreach ($rivals as $rival) {
            echo getColoredString($rival->NAME, 'yellow'), ', Level ', getColoredString($rival->LV, 'yellow');
            echo ' ... ';

            $content    = 'CTT={"RSLT":{"ATKDNM":"","DEFHP":["297"],"DEF":["'.$rival->KEY.'"],"DEFNM":"'.$rival->NAME.'","DSKN":1,"DEFEX":[],"ASKN":5,"ATKHP":["358","97","260","326"],"DEFT":"","WIN":true,"ATKNM":"'.$login.'","LST":[{"R":"a_0","A":20},{"R":"a_0","A":2,"P":"10"},{"R":"a_1","A":20},{"R":"a_2","A":20},{"R":"a_3","A":20},{"R":"d_0","A":20},{"R":"a_0","A":4,"P":"d_0"},{"R":"d_0","A":10,"P":"51"},{"R":"a_0","A":9},{"R":"a_3","A":4,"P":"d_0"},{"R":"d_0","A":10,"P":"45"},{"R":"a_3","A":9},{"R":"a_1","A":4,"P":"d_0"},{"R":"d_0","A":10,"P":"7"},{"R":"a_1","A":9},{"R":"a_2","A":4,"P":"d_0"},{"R":"d_0","A":10,"P":"36"},{"R":"a_2","A":9},{"R":"d_0","A":4,"P":"a_0"},{"R":"a_0","A":5},{"R":"d_0","A":9},{"R":"a_0","A":120,"P":"aa_0"},{"R":"aa_0","A":20,"P":"neolao7"},{"R":"a_2","A":4,"P":"d_0"},{"R":"d_0","A":10,"P":"38"},{"R":"a_2","A":9},{"R":"d_0","A":4,"P":"a_0"},{"R":"a_0","A":10,"P":"41"},{"R":"a_0","A":22,"P":"10"},{"R":"d_0","A":9},{"R":"aa_0","A":2,"P":"1"},{"R":"aa_0","A":107,"P":"d_0"},{"R":"d_0","A":10,"P":"120"},{"R":"d_0","A":12}],"ATKT":"","ATKEX":[{"HP":359,"TYPE":3,"SKN":5,"NAME":"neolao7","KEY":"agpzfmF2YWZpZ2h0chALEgZBdmF0YXIY9uaeuwMM"}],"DEFDNM":"","ATK":["agpzfmF2YWZpZ2h0chALEgZBdmF0YXIY_sGuuwMM","2","4","9"],"RTYPE":1},"TS":1354740644108}&CRC=bfcdd82c4b869a9878d22af6b99ee5bc';
            $response   = requestPost('http://avafight.appspot.com/upload_rcd?ACT=2', $content, 'JSESSIONID='.$cookie);
            $headers    = $response['headers'];
            $result     = $response['result'];

            file_put_contents(dirname(__FILE__).'/../logs/'.$rival->NAME.'.txt', var_export($rival, true));
            file_put_contents(dirname(__FILE__).'/../logs/'.$rival->NAME.'_fight.txt', var_export($result, true));


            // Result
            $info = $result->MAIN->RSLT;
            echo 'ID ', getColoredString($info->ID, 'yellow');
            echo ', EXP ', getColoredString($info->EXP, 'yellow');
            echo ', GOLD ', getColoredString($info->GLD, 'yellow');
            echo ', TAX ', getColoredString($info->TAX, 'yellow');

            echo "\n";

            $energy--;
            if ($energy <= 0) {
                break;
            }
        }
    } else {
        echo getColoredString('No energy', 'yellow'), "\n";
    }
}

echo "\n";
