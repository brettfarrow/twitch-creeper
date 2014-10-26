<?php

require 'vendor/autoload.php';

Dotenv::load(__DIR__);

class TwitchCreeper {
	
	const BASE_URL = "https://api.twitch.tv/kraken/streams";
	
	public $twitchGame;
	public $resultLimit;
	public $firstRequestURL;
	
	public function __construct($game, $limit = 100) {
		$twitchGame = str_replace(" ", "+", $game); // Use plus signs instead of spaces in call.
		$resultLimit = $limit;
		$firstRequestURL = BASE_URL . "?limit=" . $limit . "&game=" . $twitchGame;
	}
	
	public function beginCreeping () {
		$streamInfo = getStreamInfo($firstRequestURL);
		$currentRequestURL = updateStreamDB($streamInfo);
		
		// The last request will contain another stream URL that when fetched, 
		// has zero entries. updateStreamDB() will check if that is the case
		// and will return 0 when it happens. So it makes one extra request by design.
		while (!empty($currentRequestURL)) {
			$streamInfo = getStreamInfo($currentRequestURL); // get the ball rolling.
			$currentRequestURL = updateStreamDB($streamInfo);
		}
	}
	
	private function getStreamInfo ($apiURL) {
		$clientIDHeader = "Client-ID: " . getenv('CLIENT_ID');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiURL);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($clientIDHeader));

		$data = curl_exec($ch);

		curl_close($ch);

		$streamData = json_decode($data, TRUE);
		
		return $streamData;
	}
	
	private function updateStreamDB ($streamList) {
		$pdoData = "mysql:host=localhost;dbname=" . getenv('DB_NAME');

		try {
			$db = new PDO($pdoData, getenv('DB_USER'), getenv('DB_PASS'), array(PDO::ATTR_PERSISTENT => true));
		} catch (PDOException $e) {
			echo "Error!" . $e->getMessage() . "\r\n";
			die();
		}

		$query = "INSERT INTO " . getenv('DB_TABLE') . " (twitch_name, display_name, viewers, time_gmt) VALUES(?, ?, ?, ?)";
		
		$insertStatement = $db->prepare($query);

		$insertionDate = gmdate(DATE_ISO8601);

		foreach ($streamList["streams"] as $streamInfo) {
			$insertStatement->execute(
				array(
					$streamInfo["channel"]["name"], 
					$streamInfo["channel"]["display_name"], 
					$streamInfo["viewers"], 
					$insertionDate
				)
			);
		}

		if (!empty($streamList["streams"])) {
			return $streamList["_links"]["next"]; // URL with offset
		} else {
			return; // Do nothing and end it already.
		}		
	}
}