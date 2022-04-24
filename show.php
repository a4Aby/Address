<?php
error_reporting(0);
define('GMAPKEY','AIzaSyCZBqufMxxIezFkSLBCK5j2HCInF0BEvOk');
Class Geocode {
	public function __construct(){
		
	}
	public function getOSMdata($q){
		$response = $this->getCurl($q);
		$response = json_decode($response);
		$tr = '';
		if(!empty($response)){
			foreach($response as $key => $row){
				$tr .= '<tr>';
				$tr .= '<td>'.($key+1).'</td>';
				$tr .= '<td>'.$row->display_name.'</td>';
				$tr .= '<td>'.$row->lat.'</td>';
				$tr .= '<td>'.$row->lon.'</td>';
				$tr .='</tr>';
			}
			return $tr;
		}else{
			$this->errorMessage('No data found');
		}
	}
	public function getGmapData($q){
		$url = "https://maps.google.com/maps/api/geocode/json?address=$q&key=".GMAPKEY;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
		$responseJson = curl_exec($ch);
		curl_close($ch);
		$responseJson = json_decode($responseJson);
		$tr = '';
		if(!empty($responseJson)){
			$results = $responseJson->results;
			foreach($results as $key => $row){
				$address = isset($row->formatted_address)? $row->formatted_address : '';
				$geometry = isset($row->geometry)? $row->geometry : '';
				$location = isset($geometry->location)? $geometry->location : '';
				$lat = $location->lat;
				$lng = $location->lng;
				$tr .= '<tr>';
				$tr .= '<td>'.($key+1).'</td>';
				$tr .= '<td>'.$address.'</td>';
				$tr .= '<td>'.$lat.'</td>';
				$tr .= '<td>'.$lng.'</td>';
				$tr .= '</tr>';
			}
			return $tr;
		}else{
			$this->errorMessage('No data found');
		}
	}
	function getData($q){
		$osmData = $this->getOSMdata($q);
		$GmapData = $this->getGmapData($q);
		echo json_encode(array(
			'status' => true,
			'osmData' => $osmData,
			'gmapData' => $GmapData,
			'message' => ''
		));
	}
	public function errorMessage($message){
		echo json_encode(array(
			'status' => false,
			'osmData' => '',
			'message' => $message
		));
		die;
	}
	
	
	function getCurl($q){
		$q = urlencode($q);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://nominatim.openstreetmap.org/search?q=$q&format=json&polygon=1&addressdetails=1&email=deepak@trawex.com",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 60,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}
	
}

$obj = new Geocode();

$q = $_POST['address'];
if($_POST['address'] == ''){
	$obj->errorMessage('Address cannot be NULL');
}
$response = $obj->getData($q);
?>