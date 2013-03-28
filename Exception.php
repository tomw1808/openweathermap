<?php

class Openweathermap_Exception extends Exception {
	
	const OPENWEATHERMAP_EXCEPTION_NO_LATLON_MSG = "No Latitude/Longitude specified";
	const OPENWEATHERMAP_EXCEPTION_NO_LATLON_CODE = 1;
	const OPENWEATHERMAP_EXCEPTION_NO_RESPONSE_MSG = "No response from Openweathermap-Server";
	const OPENWEATHERMAP_EXCEPTION_NO_RESPONSE_CODE = 2;
	const OPENWEATHERMAP_EXCEPTION_NO_CITYID_MSG = "No City-ID found";
	const OPENWEATHERMAP_EXCEPTION_NO_CITYID_CODE = 3;
	
	
	public function __construct($msg = '', $code = 0)
	{
		if (!is_int($code)) {
			$code = (int) $code;
		}
		parent::__construct($msg, $code);
	}
}