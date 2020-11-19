<?php
include('class.scraper.php');
include('class.pawnd.php');

if (isset($_POST["email"])) {
	$email = $_POST["email"];
}

$email = "stefanvujic576@gmail.com";

$pawnd = new Pawnd($email);
$scraper = new Scraper($email);

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo json_encode(
		array(
			"data" => array(
				"have_i_been_pawned" => $pawnd->get_breaches()
			),	
			"scrapers" => $scraper->scrape()
		)
	);
}else {
	echo json_encode("bad email");
}