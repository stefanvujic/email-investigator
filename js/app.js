$(function() {
	$("#submit-main-form").on("click", function(){

		var email = $("#email").val();
		var loader = "<div class='row' id='loader'><div class='col-sm-12 col-lg'><img src='/assets/loader.gif'/></div></div>";

		var capchaResponse = grecaptcha.getResponse();

		$("#error-message").empty();
		$("#main-result").empty();
		$('div#main-result').append(loader);
		$("div#submit-main-form").css({"pointer-events": "none"});
		$("div.tab").css({"background-color": "#212529", "box-shadow": "beige", "box-shadow": "0px 0px 0px 0px #00000042", "border-radius": "6px"});

		$.ajax({
			type: "POST",
			url: "http://localhost/ajax_controller.php",
			dataType:"json",
			data: ({email : email, googleCapcha : capchaResponse}),
			cache: false,

			success: function(controllerData){
				if (controllerData == "bad email") {
					$("#error-message").append("Please enter a valid email");
				}else {
					html = renderResult(controllerData);
				}

				console.log(controllerData);

				$("div#main-result").append(html);

				$("#request-pawnd-info").on("click", function(){

					$(".pawned-row-button").replaceWith("<div class='row' id='small-loader'><div class='col-sm-12 col-lg'><img src='/assets/loader.gif'/></div></div>");
					var email = $("#email").val();

					$.ajax({
						type: "POST",
						url: "http://localhost/ajax_controller.php",
						dataType:"json",
						data: ({email : email, morePawndInfo : 1}),
						cache: false,

						success: function(pawndData){
							console.log(pawndData);
						},

						complete: function(){
							$("#small-loader").hide();
						}
					});					
				});
			},

			complete: function(){
				$("div.tab").css({"background-color": "#333333", "box-shadow": "beige", "box-shadow": "1px -2px 20px 0px #00000042", "border-radius": "6px"});
				$("#submit-main-form").css({"pointer-events": "auto"});
				$("#loader").hide();
			}
		});
	});
});

function renderResult(controllerData) {
	var html;
	html = "";	
	html += "<div class='tab pawnd-tab'>";
	html += "<div class='row website-title'>";
		html += "<div class='col-sm site-title-text'>Has your email been compromised?</div>";
	html += "</div>";

	if (controllerData.data.have_i_been_pawned.length == false) {
		html += "<div class='row pawnd-email-ok-message-row false-pawned'>";
			html += "<div class='col-sm'>"
				html += "<h5>Email Safe</h5><img src='assets/tick.svg'></img><div class='col-sm pawned-text-false'>Your email has not been found in any known data leaks</div>"
			html += "</div>";
		html += "</div>";
	}else {

		html += "<div class='row pawned-row'>";
			html += "<div class='col-sm'><h5>Email Compromised!</h5><img src='assets/cross.svg'></img></div>";
		html += "</div>";

		html += "<div class='row pawned-row pawned-message'>";
			html += "<div class='col-sm'>Your email has been found in the following data dumps</div>";	
		html += "</div>";

		$.each(controllerData.data.have_i_been_pawned, function(siteKey, siteName) {
			html += "<div class='row pawned-row align-items-center'>";
				html += "<div class='col-sm col-lg record pawnd-record'>" + siteName + "</div>";
			html += "</div>";
		});

		html += "<div class='row pawned-row-button'>";
			html += "<div class='col-sm'><div id='request-pawnd-info' class='btn btn-primary'>Find Out More</div></div>";
		html += "</div>";						
	}

	html += "</div>";

	$.each(controllerData.scrapers, function(scraperName, scraperData) {
		if (scraperData.length == false || controllerData.scrapers.bing[1].length == false) {

			html += "<div class='tab " + scraperName + "-tab'>";
			html += "<div class='row website-title'>";
				html += "<div class='col-sm site-title-text'>" + scraperName + "</div>";
			html += "</div>";

			html += "<div class='col-sm col-lg empty-message'>Nothing found</div>";
			html += "</div>";

		}else {

			html += "<div class='tab " + scraperName + "-tab'>";
			html += "<div class='row website-title'>";
				html += "<div class='col-sm site-title-text'>" + scraperName + "</div>";
			html += "</div>";

			$.each(scraperData, function(pageIndex, pageData) {

				$.each(pageData, function(recordIndex, record) {
					html += "<div class='row record-row'>";
						html += "<div class='col-sm-12 col-lg record " + scraperName + "-website-url website-url'>" + record.website + "</div>";
						html += "<div class='col-sm-12 col-lg record " + scraperName + "-title'>" + record.title + "</div>";
						html += "<div class='col-sm-12 col-lg record " + scraperName + "-url'><a href='" + record.url + "' target='_blank'>" + record.url.substring(0,35) + "...</a></div>";
					html += "</div>";

					html += "<hr>";
				});

			});
			html += "</div>";
		}
	});
	return html;
}