<?php
function scrape_google($email, $page_count) {
	$result = array();
	$headers = array();
	for ($page = 0; $page < $page_count; $page++) { 
		$curl_response = curl_response("https://www.google.com/search?q=", $email . "&start=" . $page . 0, $headers);
		print_r($curl_response);
		$dom = new simple_html_dom();
		$dom->load($curl_response);

		$ctr = 0;
		$google_records = array();
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
				$google_records[$ctr]["website"] = $website;
				$google_records[$ctr]["title"] = $title;
				$google_records[$ctr]["excerpt"] = $excerpt;
				$google_records[$ctr]["url"] = $url;
			}

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
