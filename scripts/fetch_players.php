<?php
$steam_api_url = 'https://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1/?format=json&appid=40';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $steam_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
		echo 'Error occurred while fetching data from Steam API';
} else {
		echo $response;
}
?>
