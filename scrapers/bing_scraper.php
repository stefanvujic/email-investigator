<?php

function scrape_bing($email) {
	$headers = array();
	$curl_response = curl_response("https://www.bing.com/search?q=", $email, $headers);
	$dom = new simple_html_dom();
	$dom->load($curl_response);

	$ctr = 0;
	$bing_records = array();
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

		if (!empty($website) && !empty($title) && !empty($excerpt) && !empty($url)) {
			$bing_records[$ctr]["website"] = $website;
			$bing_records[$ctr]["title"] = $title;
			$bing_records[$ctr]["excerpt"] = $excerpt;
			$bing_records[$ctr]["url"] = $url;
		}

		$ctr++;
	}

	$results = array('1' => $bing_records);

	return $results;
}

