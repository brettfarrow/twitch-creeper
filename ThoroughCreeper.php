<?php

require 'vendor/autoload.php';

Dotenv::load(__DIR__);

const BASE_URL = "https://api.twitch.tv/kraken/streams";

class ThoroughCreeper {
	
	public $resultLimit;
	public $requestURL;
	public $streamInfo;	
	
	public function __construct($limit) {
		$this->resultLimit = $limit;
		$initialURL = BASE_URL . "?limit=" . $this->resultLimit;
		$this->setRequestURL($initialURL);
	}
	
	// The last request will contain another stream URL that when fetched, 
	// has zero entries. updateStreamDB() will check if that is the case
	// and will return 0 when it happens. So it makes one extra request by design.
	public function beginCreeping () {
		while (!empty($this->getRequestURL())) {
			$streamInfo = $this->getStreamInfo($this->getRequestURL()); // get the ball rolling.
			$nextRequestURL = $this->updateStreamDB($streamInfo);
			$this->setRequestURL($nextRequestURL);
		}
	}
	
	public function getStreamInfo ($apiURL) {
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
	
	public function updateStreamDB ($streamList) {
		$pdoData = "mysql:host=localhost;dbname=" . getenv('DB_NAME');

		try {
			$db = new PDO($pdoData, getenv('DB_USER'), getenv('DB_PASS'), array(PDO::ATTR_PERSISTENT => true));
		} catch (PDOException $e) {
			echo "Error!" . $e->getMessage() . "\r\n";
			die();
		}

		$query = "INSERT INTO " . getenv('DB_TABLE') . " (display_name, game, viewers, time_gmt) VALUES(?, ?, ?, ?)";
		
		$insertStatement = $db->prepare($query);

		$insertionDate = gmdate(DATE_ISO8601);

		foreach ($streamList["streams"] as $stream) {
			$insertStatement->execute(
				array(
					$stream["channel"]["display_name"],
					$stream["game"],
					$stream["viewers"],
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

	public function getRequestURL () {
		return $this->requestURL;
	}
	
	public function setRequestURL ($target) {
		$this->requestURL = $target;
	}
}
