<?php 
/**
 * Class Weather
 * Get weather forecast for the region of client's IP address
 * @author Pedro Piedade 
 */
class weather
{
	/* HostIP API
	 * Get location from IP
 	 * API website: http://www.hostip.info/use.html
	 */
	const geobytesUrl = "http://www.geobytes.com/IpLocator.htm?GetLocation&template=json.txt";	
	/* Yahoo! Maps Web Services - Geocoding API
	 * Get location code - woeid - from (Latitude, Longitude)
	 * API website: http://developer.yahoo.com/maps/rest/V1/geocode.html
	 * API key needed. Get one here: https://developer.apps.yahoo.com/projects
	 */
	const geocodeUrl = "http://where.yahooapis.com/geocode?";
	const geocodeKey = "dj0yJmk9bGFWaUdVa0xjSkQ4JmQ9WVdrOU1FMU9URnBYTlRBbWNHbzlPRFUxTXpRMU1qWXkmcz1jb25zdW1lcnNlY3JldCZ4PWZj";
	
	/* Yahoo! Weather API
	 * Get forecast for the location code (woeid)
	 * API website: http://developer.yahoo.com/weather/
	 */ 
	const yahooWeatherUrl = 'http://weather.yahooapis.com/forecastjson?';
	
	// IP address
	private $ip = null;

	/**
	 * Get client's coordinates from its IP address
	 *
	 * @return Array Coordinates (latitude, longitude)
	 */
	private function getCoordinates()
	{
		// Check if we are overriding the default client's IP 
		$ipSetting = "";
		if($this->ip != null) {
			$ipSetting = "&IpAddress=" . $this->ip;
		}
		else {
			$ipSetting = "&IpAddress=" . $_SERVER["REMOTE_ADDR"];
		}		
		$url = self::geobytesUrl . $ipSetting;
		$result = $this->getUrl($url);
		$arr = json_decode($result, true);
		if($arr["geobytes"]["latitude"] == null || $arr["geobytes"]["longitude"] == null) {
			$latitude = 0;
			$longitude = 0;			
		}
		else {
			$latitude = $arr["geobytes"]["latitude"];
			$longitude = $arr["geobytes"]["longitude"];
		}
		return array("latitude"=>$latitude, "longitude"=>$longitude);
	}

	
	/**
	 * Get client's woeid - a Yahoo! geolocation code
	 *
	 * @return Int Woeid code
	 */
	private function getWoeid()
	{
		$mylocation = $this->getCoordinates();
		$result = $this->getUrl(self::geocodeUrl . "location=" . $mylocation["latitude"] . "," . $mylocation["longitude"] . "&flags=J&gflags=R&appid=" . self::geocodeKey);
		$result = json_decode($result, true);
		if($result["ResultSet"]["Found"] != 0) {
			$return = $result["ResultSet"]["Results"][0]["woeid"];
			return $return;
		}
		return 0;
	}
	
	/**
	 * Get weather information
	 * @param String $ip IP address to override default clients IP
	 * @param String $units Return units: "c" is for metric (default) or "f" for imperial
	 * @return String JSON with weather information
	 */
	public function getWeather($ip = null, $units = "c")
	{
		$this->ip = $ip;
		$woeid = $this->getWoeid();
		if($woeid != 0) {
			$result = $this->getUrl(self::yahooWeatherUrl . "w=" . $woeid . "&d=5&u=" . $units);
			return $result;
		}
		return json_encode(array("code"=>404, "status"=>"Unknown location"));
	}
	
	/**
	 * Retrieve data from URL
	 * The method tries to use cURL.
	 * In some configurations this extension is not installed
	 * so in these cases it uses the (slower) file_get_contents function.
	 * @return String Data
	 */
	private function getUrl($url) {
		if(in_array('curl', get_loaded_extensions())) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$data = curl_exec($ch);
			curl_close($ch);			
		}
		else {
			$data = file_get_contents($url);
		}
		return $data;
	}
}