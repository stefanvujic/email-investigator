<?php
include('simple_html_dom.php');
include_once('class.verifyEmail.php');

function curl_response($url, $email) {

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url . $email);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($curl);
	curl_close($curl);

	return $result;
}

function get_email_status($email) {

	$vmail = new verifyEmail();
	$vmail->setStreamTimeoutWait(20);
	$vmail->Debug= TRUE;
	$vmail->Debugoutput= 'html';

	$vmail->setEmailFrom('localhost@admin.com');

	if ($vmail->check($email)) {
		$email_exists = true;
	}else {
		$email_exists = false;
	}

	return $email_exists;
}

function scrape_google($email) {
	$result = array();
	for ($page = 0; $page < 2; $page++) { 
		$curl_response = curl_response("https://www.google.com/search?q=", $email . "&start=" . $page . 0);
		$dom = new simple_html_dom();
		$dom->load($curl_response);

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

		foreach ($google_records as $key => $google_record) {

			if (strpos($google_record["title"], $email) !== false || strpos($google_record["excerpt"], $email) !== false) {
				$result[$page][$key]["title"] = $google_record["title"];
				$result[$page][$key]["excerpt"] = $google_record["excerpt"];
				$result[$page][$key]["url"] = str_replace("/url?q=", "", $google_record["url"]);
			}
		}
	}

	$result = mb_convert_encoding($result, "UTF-8", "UTF-8");

	return $result;
}

function scrape_bing($email) {
	$curl_response = curl_response("https://www.bing.com/search?q=", $email);
	$dom = new simple_html_dom();
	$dom->load($curl_response);

	$ctr = 0;
	$bing_records = array();
	foreach($dom->find("li.b_algo") as $result_container) {

		$title = $result_container->find("h2", 0)->plaintext;
		$excerpt = $result_container->plaintext;
		$url = $result_container->find("a", 0)->href;

		$bing_records[$ctr]["title"] = $title;
		$bing_records[$ctr]["excerpt"] = $excerpt;
		$bing_records[$ctr]["url"] = $url;

		$ctr++;
	}

	$results = array('1' => $bing_records);

	return $results;
}

if ($_POST["email"]) {
	echo json_encode(array(
		"google" => scrape_google($_POST["email"]),
		"bing" => scrape_bing($_POST["email"]),
	));
}