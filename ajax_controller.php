<?php

function curl_response($url, $email) {
	include('simple_html_dom.php');

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url . $email);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($curl);
	curl_close($curl);

	return $result;
}

function scrape_google($email) {
	$curl_response = curl_response("https://www.google.com/search?q=", $email);
	$dom = new simple_html_dom();
	$dom->load($curl_response);
	$urls_and_plaintext = array();

	$ctr = 0;
	$google_records = array();
	foreach($dom->find("div.ZINbbc.xpd.O9g5cc.uUPGi") as $result_container) {

		$title = $result_container->first_child()->plaintext;
		$excerpt = $result_container->children(2)->plaintext;
		$url = $result_container->find("a", 0)->href;

		$google_records[$ctr]["title"] = $title;
		$google_records[$ctr]["excerpt"] = $excerpt;
		$google_records[$ctr]["url"] = $url;

		$ctr++;
	}

	$result = array();
	foreach ($google_records as $key => $google_record) {

		if (strpos($google_record["title"], $email) !== false || strpos($google_record["excerpt"], $email) !== false) {
			$result[$key]["title"] = $google_record["title"];
			$result[$key]["excerpt"] = $google_record["excerpt"];
			$result[$key]["url"] = str_replace("/url?q=", "", $google_record["url"]);
		}
	}
	// echo '<pre>',print_r($result,1),'</pre>';

	$result = mb_convert_encoding($result, "UTF-8", "UTF-8");

	return $result;
}

if ($_POST["email"]) {
	echo json_encode(scrape_google($_POST["email"]));
}