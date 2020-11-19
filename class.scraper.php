<?php
include('simple_html_dom.php');

class Scraper
{
	protected $headers;
	protected $url;
	protected $email;
	protected $page_count;
	protected $result;

	protected $google_records;
	protected $bing_records;

	function __construct($email)
	{
		$this->email = $email;
		$this->headers = array();
		$this->headers = array();
		$this->result = array();

		$this->google_records = array();
		$this->bing_records = array();
	}

	protected function curl_response()
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
		$result = curl_exec($curl);
		curl_close($curl);

		return $result;
	}

	protected function scrape_google() 
	{
		$this->page_count = 2;

		for ($page = 0; $page < $this->page_count; $page++) {
			$this->url = "https://www.google.com/search?q=" . $this->email . "&start=" . $page . 0;
			$curl_response = $this->curl_response();
			$dom = new simple_html_dom();
			$dom->load($curl_response);

			$ctr = 0;
			foreach($dom->find("div.ZINbbc.xpd.O9g5cc.uUPGi") as $result_container) {

				if ($result_container->find("div.BNeawe.UPmit.AP7Wnd", 0)) {
					$website_string = $result_container->find("div.BNeawe.UPmit.AP7Wnd", 0)->plaintext;
				}
				if (!empty($website_string)) {
					$website = explode(" ", $website_string)[0];
				}

				if(!empty($result_container->first_child())) {
					$title = $result_container->first_child()->plaintext;
				}

				if(!empty($result_container->children(2))) {
					$excerpt = $result_container->children(2)->plaintext;
				}

				if(!empty($result_container->find("a", 0)->href)) {
					$url = $result_container->find("a", 0)->href;
				}

				if (!empty($website) && !empty($title) && !empty($excerpt) && !empty($url)) {
					$this->google_records[$ctr]["website"] = $website;
					$this->google_records[$ctr]["title"] = $title;
					$this->google_records[$ctr]["excerpt"] = $excerpt;
					$this->google_records[$ctr]["url"] = $url;
				}

				$ctr++;
			}

			foreach ($this->google_records as $key => $google_record) {
				if ($key < 3) {
					$this->result[$page][$key]["website"] = $google_record["website"];
					$this->result[$page][$key]["title"] = $google_record["title"];
					$this->result[$page][$key]["excerpt"] = $google_record["excerpt"];
					$this->result[$page][$key]["url"] = str_replace("/url?q=", "", $google_record["url"]);
				}else {
					if (strpos($google_record["title"], $this->email) !== false || strpos($google_record["excerpt"], $this->email) !== false) {
						$this->result[$page][$key]["website"] = $google_record["website"];
						$this->result[$page][$key]["title"] = $google_record["title"];
						$this->result[$page][$key]["excerpt"] = $google_record["excerpt"];
						$this->result[$page][$key]["url"] = str_replace("/url?q=", "", $google_record["url"]);
					}
				}	
			}
		}

		$this->result = mb_convert_encoding($this->result, "UTF-8", "UTF-8");

		return $this->result;
	}

	protected function scrape_bing() 
	{
		$this->url = "https://www.bing.com/search?q=" . $this->email;
		$curl_response = $this->curl_response();
		$dom = new simple_html_dom();
		$dom->load($curl_response);

		$ctr = 0;
		foreach($dom->find("li.b_algo") as $result_container) {

			if ($result_container->find("div.b_attribution", 0)) {
				$website_string = $result_container->find("div.b_attribution", 0)->plaintext;
			}
			if ($website_string) {
				$website = explode("/", $website_string);
				if (!empty($website[0]) && !empty($website[2])) {
					$website = $website[0] . "//" . $website[2];
				}
			}

			if ($result_container->find("h2", 0)) {
				$title = $result_container->find("h2", 0)->plaintext;
			}

			if ($result_container) {
				$excerpt = $result_container->plaintext;
			}

			if ($result_container->find("a", 0)->href) {
				$url = $result_container->find("a", 0)->href;
			}

			if ($ctr < 3) {
				if (!empty($website) && !empty($title) && !empty($excerpt) && !empty($url)) {
					$this->bing_records[$ctr]["website"] = $website;
					$this->bing_records[$ctr]["title"] = $title;
					$this->bing_records[$ctr]["excerpt"] = $excerpt;
					$this->bing_records[$ctr]["url"] = $url;
				}
			}else {
				if (strpos($bing_records["title"], $this->email) !== false || strpos($bing_records["excerpt"], $this->email) !== false) {
					if (!empty($website) && !empty($title) && !empty($excerpt) && !empty($url)) {
						$this->bing_records[$ctr]["website"] = $website;
						$this->bing_records[$ctr]["title"] = $title;
						$this->bing_records[$ctr]["excerpt"] = $excerpt;
						$this->bing_records[$ctr]["url"] = $url;
					}
				}	
			}

			$ctr++;
		}

		$this->result = array('1' => $this->bing_records);

		return $this->result;
	}

	function scrape()
	{
		return array(
				"google" => $this->scrape_google(),
				"bing" => $this->scrape_bing()
			);
	}
}