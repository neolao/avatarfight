<?php
$configurationPath = dirname(__FILE__).'/../config/avatars.ini';
$avatars = parse_ini_file($configurationPath, true);

foreach ($avatars as $login => $avatarConfig) {
    $password = $avatarConfig['password'];

    // Login
    $options = array(
        'http'=>array(
            'method'    => 'POST',
            'header'    => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content'   => 'CTT={"TS":1354497168845,"VER":"4.1","NAME":"'.$login.'","OS":4,"PWD":"'.$password.'","DEV":"b52ebb81b1394be3dbbd99c3c9667f36b0f76de9","LANG":"en"}&CRC=1f9831a33682e24bbe5e05fc1f632628'
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents('http://avafight.appspot.com/login?ACT=1', false, $context);
    $result = json_decode($result);

    // Display informations about the avatar
    $avatar = $result->MAIN->AVA;
    echo $avatar->NAME, ', Level ', $avatar->LV;
    echo "\n";
}
