<?php

define("HIBP_KEY", "356caf020183406f88b0a67e4c445867");

class Pawnd
{
	protected $email;
	protected $url;
	protected $headers;
	protected $breaches;

	function __construct($email)
	{
		$this->email = $email;
		$this->headers = array();
	}

	protected function curl_response() {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
		$result = curl_exec($curl);
		curl_close($curl);

		return $result;
	}

	function get_breaches() {
		$this->headers = array(
	    	"hibp-api-key: " . HIBP_KEY,
	    	"user-agent: email_investigator",
		);

		$this->url = "https://haveibeenpwned.com/api/v3/breachedaccount/". $this->email . "?truncateResponse=false";

		$this->breaches = $this->curl_response($this->url);
		
		if (!empty($this->breaches)) {
			$this->breaches = json_decode($this->breaches);
		}

		return $this->breaches;
	}	
}