<?php

	include("../database.php");

	if (isset($_GET['location'])) {

		$json = null;
		$iCarCount = 0;
		$iparkCount = 0;
		$icrowdCount = 0;
		$address = urlencode($_GET['location']. " malaysia");
	 
		// google map geocode api url
		$url = "https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyAeXJjyp6YsAE58Ou5ezkplKLnvR2-liOw";
			
			
		// get the json response
		$resp_json = file_get_contents($url);

		 
		// decode the json
		$resp = json_decode($resp_json, true);

		// response status will be 'OK', if able to geocode given address 
		if($resp['status']=='OK'){

		    // get the important data
		    $lat = $resp['results'][0]['geometry']['location']['lat'];
		    $lon = $resp['results'][0]['geometry']['location']['lng'];

		    //print_r($lat);
			//print_r($lon);
		    // curl get nearby record
		    $devices = getDevicesRadius($lat, $lon);
		    for($i=0; $i<count($devices); $i++) {
				$stream = getStreamLatestData($devices[$i]["id"]);

				// data analysis
				// Hi - > 700
				// Medium - Between 300 to 700
				// Low - < 300
				

				switch($stream["stream"]) {
					case "car_count":
						$iCarCount = $iCarCount + $stream["latest_value"];
						break;
					case "parking":
						$iparkCount = $iparkCount + $stream["latest_value"];
						break;
					case "crowd":
						$icrowdCount = $icrowdCount + $stream["latest_value"];
						break;
					default:
						break;
				}
			}

			if($iCarCount < 50) 
				$json["Traffic"] = "Low";
			elseif($iCarCount > 180) 
				$json["Traffic"] = "High";
			else
				$json["Traffic"] = "Moderate";

			if($iparkCount < 20) 
				$json["Parking"] = "Low";
			elseif($iparkCount > 140) 
				$json["Parking"] = "High";
			else
				$json["Parking"] = "Moderate";
			
				$json["crowd"] = "Moderate";


		}
		// return back information to requestor
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		echo json_encode($json);
	}

	/***
	 * DEVICE
	 */
	if (isset($_GET['device'])) {

		$json = null;

		// search device
		$aObj["username"] = urlencode($_GET["device"]);

		$database = new Database();
		$database->connect();

		if($database->select(Database::R_DEVICE, $aObj, $aResult)) {

			if($aResult == null)
				$json["error"] = "Device not exists";
		} else {
			$json["error"] = $database->getError();
		}

		if($json == null) {
			$result = $aResult->fetch_assoc();
			$stream = getStreamLatestData($result["devid"]);
			
			if($stream["value"] > -1) {
				$json["status"] = "Active";
				$json["value"] = $stream["value"];
			}
		}

		// return back information to requestor
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		echo json_encode($json);
	}

	/***
	 *
	 */
	function getDevicesRadius($lat, $lon) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://api-m2x.att.com/v2/devices/catalog/search",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "{\"location\":{\"within_circle\":{\"center\":{\"latitude\":$lat,\"longitude\":$lon},\"radius\":{\"km\":1} } } }",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "content-type: application/json"
		  ),
		));

		$devices = null;

		if($response = curl_exec($curl)){
			$json = json_decode($response, true);
			$devices = $json["devices"];
		}

		curl_close($curl);

		return $devices;
	}

	/***
	 *
	 */
	// function getDeviceData($deviceId) {
	// 	$curl = curl_init();

	// 	curl_setopt_array($curl, array(
	// 	  CURLOPT_URL => "http://api-m2x.att.com/v2/devices/$deviceId",
	// 	  CURLOPT_RETURNTRANSFER => true,
	// 	  CURLOPT_ENCODING => "",
	// 	  CURLOPT_MAXREDIRS => 10,
	// 	  CURLOPT_TIMEOUT => 30,
	// 	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	// 	  CURLOPT_CUSTOMREQUEST => "GET",
	// 	  CURLOPT_HTTPHEADER => array(
	// 	    "cache-control: no-cache",
	// 	  ),
	// 	));

	// 	$response = curl_exec($curl);
	// 	if($response = curl_exec($curl)){
	// 		$json = json_decode($response);
	// 		getStreamLatestData($deviceId); 
	// 	}

	// 	curl_close($curl);
	// }

	/***
	 *
	 */
	function getStreamLatestData($deviceId) {

		//include_once 'reg_crud.php';

		if (!is_null($deviceId)) {
			 			
 			$aObj["devid"] = $deviceId;

 			$database = new Database();
			$database->connect();

 			if(!$database->select(Database::R_DEVICE, $aObj, $arrResult)) {
 				return false;
 			} 

 			if($arrResult == null) {
 				return false;
 			}

 			$aResult = $arrResult->fetch_assoc();
  		}

		$return = null;
		// find stream using deviceId, mysql!
		$return["stream"] = $aResult["stream"];

		// get latest stream data
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://api-m2x.att.com/v2/devices/$deviceId/streams/". $return["stream"],
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache"
		  ),
		));

		$response = curl_exec($curl);
		if($response = curl_exec($curl)){
			$json = json_decode($response, true); 

			$return["type"] = $json["name"];
			$return["value"] = $json["value"];
		}

		curl_close($curl);

		return $return;
	}
?>