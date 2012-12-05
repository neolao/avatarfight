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
    foreach ($rivals as $rival) {
        echo getColoredString($rival->NAME, 'yellow'), ', Level ', getColoredString($rival->LV, 'yellow');
        echo ' ... ';

        /*
        $content    = 'CTT={"RSLT":{"ATKDNM":"","DEFHP":["'.$rival->HP.'"],"DEF":["'.$rival->KEY.'"],"DEFNM":"'.$rival->NAME.'","DSKN":1,"DEFEX":[],"ASKN":5,"ATKHP":[],"DEFT":"","WIN":true,"ATKNM":"'.$login.'","LST":[],"ATKT":"","ATKEX":[],"DEFDNM":"","ATK":["'.$avatar->KEY.'","2","4","9"],"RTYPE":1},"TS":'.$TS.'}&CRC=bfcdd82c4b869a9878d22af6b99ee5bc';
        $content    = 'CTT={"RSLT":{"ATKDNM":"","DEFHP":["297"],"DEF":["agpzfmF2YWZpZ2h0chALEgZBdmF0YXIYr77C9wIM"],"DEFNM":"zel-ay","DSKN":1,"DEFEX":[],"ASKN":5,"ATKHP":["358","97","260","326"],"DEFT":"","WIN":true,"ATKNM":"neolao10","LST":[],"ATKT":"","ATKEX":[],"DEFDNM":"","ATK":["agpzfmF2YWZpZ2h0chALEgZBdmF0YXIY_sGuuwMM","2","4","9"],"RTYPE":1},"TS":1354740644108}&CRC=bfcdd82c4b869a9878d22af6b99ee5bc';
        $response   = requestPost('http://avafight.appspot.com/upload_rcd?ACT=2', $content, 'JSESSIONID='.$cookie);
        $headers    = $response['headers'];
        $result     = $response['result'];

        var_dump($result);
        */

        echo "\n";
    }
}
