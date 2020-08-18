<?php

function scrape_bing($email) {
	$curl_response = curl_response("https://www.bing.com/search?q=", $email);
	$dom = new simple_html_dom();
	$dom->load($curl_response);

	$ctr = 0;
	$bing_records = array();
	foreach($dom->find("li.b_algo") as $result_container) {

		$website_string = $result_container->find("div.b_attribution", 0)->plaintext;
		$website = explode("/", $website_string);
		$website = $website[0] . "//" . $website[2];

		$title = $result_container->find("h2", 0)->plaintext;
		$excerpt = $result_container->plaintext;
		$url = $result_container->find("a", 0)->href;

		$bing_records[$ctr]["website"] = $website;
		$bing_records[$ctr]["title"] = $title;
		$bing_records[$ctr]["excerpt"] = $excerpt;
		$bing_records[$ctr]["url"] = $url;

		$ctr++;
	}

	$results = array('1' => $bing_records);

	return $results;
}
?>