<?php
function scrape_google($email) {
	$result = array();
	for ($page = 0; $page < 2; $page++) { 
		$curl_response = curl_response("https://www.google.com/search?q=", $email . "&start=" . $page . 0);
		$dom = new simple_html_dom();
		$dom->load($curl_response);

		$ctr = 0;
		$google_records = array();
		foreach($dom->find("div.ZINbbc.xpd.O9g5cc.uUPGi") as $result_container) {

			$website_string = $result_container->find("div.BNeawe.UPmit.AP7Wnd", 0)->plaintext;
			$website = explode(" ", $website_string)[0];

			$title = $result_container->first_child()->plaintext;
			$excerpt = $result_container->children(2)->plaintext;
			$url = $result_container->find("a", 0)->href;

			$google_records[$ctr]["website"] = $website;
			$google_records[$ctr]["title"] = $title;
			$google_records[$ctr]["excerpt"] = $excerpt;
			$google_records[$ctr]["url"] = $url;

			$ctr++;
		}

		foreach ($google_records as $key => $google_record) {

			if (strpos($google_record["title"], $email) !== false || strpos($google_record["excerpt"], $email) !== false) {
				$result[$page][$key]["website"] = $google_record["website"];
				$result[$page][$key]["title"] = $google_record["title"];
				$result[$page][$key]["excerpt"] = $google_record["excerpt"];
				$result[$page][$key]["url"] = str_replace("/url?q=", "", $google_record["url"]);
			}
		}
	}
	$result = mb_convert_encoding($result, "UTF-8", "UTF-8");

	return $result;
}