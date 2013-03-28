<?php

class Openweathermap_Map {
	
	private $latitude 	= 	null;
	private $longitude	= 	null;
	
	
	/**
	 * Set the output lang. According to
	 * http://openweathermap.org/wiki/API/JSON_API
	 * its 
	 * Language [ru, en, de, fr, es, it] if is posible 
	 *
	 * @var String
	 */
	private $lang 		= 	"en";
	
	private $curl_options = array(
		CURLOPT_RETURNTRANSFER => 1
	);
	
	private $openweathermap_cityurl = "http://api.openweathermap.org/data/2.1/find/city?lat=%s&lon=%s&cnt=10";
	private $openweathermap_forecast = "http://api.openweathermap.org/data/2.1/forecast/city/%s";
	private $openweathermap_dailyforecast = "http://api.openweathermap.org/data/2.2/forecast/city/%s?mode=daily_compact";
	
	private $icon_mapping = array(
		"01d" => "sunny.gif",
		"01n" => "sunny.gif",
		"02d" => "partly_cloudy.gif",
		"02n" => "partly_cloudy.gif",
		"03d" => "mostly_cloudy.gif",
		"03n" => "mostly_cloudy.gif",
		"04d" => "cloudy.gif",
		"04n" => "cloudy.gif",
		"09d" => "showers.gif",
		"09n" => "showers.gif",
		"10d" => "rain.gif",
		"10n" => "rain.gif",
		"11d" => "thunderstorm.gif",
		"11n" => "thunderstorm.gif",
		"13d" => "snow.gif",
		"13n" => "snow.gif",
		"50d" => "mist.gif",
		"50n" => "mist.gif",
	);
		
	public function getGoogleWeatherResponse() {
		if(is_null($this->latitude) || is_null($this->longitude)) {
			throw new Openweathermap_Exception(Openweathermap_Exception::OPENWEATHERMAP_EXCEPTION_NO_LATLON_MSG , Openweathermap_Exception::OPENWEATHERMAP_EXCEPTION_NO_LATLON_CODE);
		}
		$json_today = $this->getJsonContents(sprintf($this->openweathermap_cityurl, $this->latitude, $this->longitude));
		if(!isset($json_today->list[0]->id)) {
			throw new Openweathermap_Exception(Openweathermap_Exception::OPENWEATHERMAP_EXCEPTION_NO_CITYID_MSG , Openweathermap_Exception::OPENWEATHERMAP_EXCEPTION_NO_CITYID_CODE );
		} else {
			$json_forecast = $this->getJsonContents(sprintf($this->openweathermap_dailyforecast, $json_today->list[0]->id));
			$api = new SimpleXMLElement("<xml_api_reply version=\"1\" />");
			$xml = $api->addChild("weather");
			$this->addForecastInformationAttributes($xml, $json_forecast);
			
			
			foreach ($json_forecast->list as $key => $forecast_list_element) {
				if($key == 0) {
					$this->addCurrentConditions($xml, $forecast_list_element);
				} else {
					$this->addForecastConditions($xml, $forecast_list_element);
				}
				
			}
			return $api->asXML();
			
		}
	}
	
	private function addForecastConditions($xml, $forecast_list_element) {
		$elem = $xml->addChild("forecast_conditions");
		$day_of_the_week = $elem->addChild("day_of_week");
		$day_of_the_week->addAttribute("data",date('D',$forecast_list_element->dt));
		$high = $elem->addChild("high");
		$tmp = $forecast_list_element->temp - 273.15;
		$high->addAttribute("data",round($tmp,0));
		$low = $elem->addChild("low");
		
		$tmp = $forecast_list_element->night - 273.15;
		$low->addAttribute("data",round($tmp));
		$condition = $elem->addChild("condition");
		$condition->addAttribute("data", ucfirst($forecast_list_element->weather[0]->description));
		$icon = $elem->addChild("icon");
		$icon->addAttribute("data","/ig/images/weather/".$this->icon_mapping[$forecast_list_element->weather[0]->icon]);
	}
	
	private function addCurrentConditions($xml, $forecast_list_element) {
		$elem = $xml->addChild("current_conditions");
		$condition = $elem->addChild("condition");
		$condition->addAttribute("data", ucfirst($forecast_list_element->weather[0]->description));
		$tmp = $forecast_list_element->temp - 273.15;
		$tmp_f = round((9/5)*$tmp + 32,0);
		
		$temp_f = $elem->addChild("temp_f");
		$temp_f->addAttribute("data",$tmp_f);
		$temp_c = $elem->addChild("temp_c");
		$temp_c->addAttribute("data",$tmp);
		$icon = $elem->addChild("icon");
		$icon->addAttribute("data","/ig/images/weather/".$this->icon_mapping[$forecast_list_element->weather[0]->icon]);
		$wind_condition = $elem->addChild("wind_condition");
		$wind_condition->addAttribute("data","Wind: ".$forecast_list_element->deg."° at ".round($forecast_list_element->speed)." mph");
		$humidity = $elem->addChild("humidity");
		$humidity->addAttribute("data","Humidity: ".$forecast_list_element->humidity."%");
	}
	
	private function addForecastInformationAttributes($xml, $json_forecast) {
		$forecast_information = $xml->addChild("forecast_information");
		$city = $forecast_information->addChild("city");
		$city->addAttribute("data", $json_forecast->city->name.", ".$json_forecast->city->country);
		$postal_code = $forecast_information->addChild("postal_code");
		$postal_code->addAttribute("data",$json_forecast->city->name);
		$latitude = $forecast_information->addChild("latitude_e6");
		$latitude->addAttribute("data",$json_forecast->city->coord->lat);
		$longitude = $forecast_information->addChild("llongitude_e6");
		$longitude->addAttribute("data",$json_forecast->city->coord->lon);
		
		$forecast_date = $forecast_information->addChild("forecast_date");
		
		$forecast_date->addAttribute("data",date("Y-m-d", $json_forecast->list[0]->dt));
		$current_date = $forecast_information->addChild("current_date");
		$current_date->addAttribute("data", date("Y-m-d H:j:s O"));
	}
	
	public function setLatLon($latitude, $longitude) {
		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}
	
	private function getJsonContents($url, $retry = 0) {
		$ch = curl_init();
		curl_setopt_array($ch, $this->curl_options);
		curl_setopt($ch, CURLOPT_URL,$url);
		$response = curl_exec($ch);
		if(strlen($response) == 0 || !($json_response = json_decode($response))) {
			if($retry < 3) {
				$retry++;
				sleep(3);
				return $this->getJsonContents($url, $retry);
			} else {
				throw new Openweathermap_Exception(Openweathermap_Exception::OPENWEATHERMAP_EXCEPTION_NO_RESPONSE_MSG , Openweathermap_Exception::OPENWEATHERMAP_EXCEPTION_NO_RESPONSE_CODE );
			}
		} else {
			return $json_response;
		}
	}
	
	
	
}