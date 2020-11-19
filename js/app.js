$(function() {
	$("#submit-main-form").on("click", function(){

		var email = $("#email").val();
		var loader = "<div class='row' id='loader'><div class='col-sm-12 col-lg'><img src='/assets/loader.gif'/></div></div>";

		var capchaResponse = grecaptcha.getResponse();

		$("#error-message").empty();
		$("#main-result").empty();
		$('div#main-result').append(loader);
		$("div#submit-main-form").css({"pointer-events": "none"});
		$("div.tab").css({"background-color": "#212529", "box-shadow": "0px 0px 0px 0px #00000042", "border-radius": "6px"});

		$.ajax({
			type: "POST",
			url: "http://email-investigator.local/ajax_controller.php",
			dataType:"json",
			data: ({email : email, googleCapcha : capchaResponse}),
			cache: false,

			success: function(controllerData){
				if (controllerData == "bad email") {
					$("#loader").hide();
					$("#submit-main-form").css({"pointer-events": "auto"});
					$("#error-message").append("Please enter a valid email");
				}else {
					if (controllerData == "bad captcha") {
						$("#loader").hide();
						$("#submit-main-form").css({"pointer-events": "auto"});					
						$("#error-message").append("Please verify recapcha");
					}else {
						html = renderResult(controllerData);
					}	
				}

				$("div#main-result").append(html);
				$(".pawned-company-description").hide();

				grecaptcha.reset();	

			},

			complete: function(){
				$("div.tab").css({"background-color": "#333333", "box-shadow": "1px -2px 20px 0px #00000042", "border-radius": "6px"});
				$("#submit-main-form").css({"pointer-events": "auto"});
				$("#loader").hide();
                                
                $(".pawned-company-name").on("click", function(){
                    $("." + $(this).attr("id")).slideToggle();
                });
			}
		});
	});
});

function renderResult(controllerData) {
	var html;

	html = "";	
	html += "<div class='tab pawnd-tab'>";
	html += "<div class='row website-title'>";
		html += "<div class='col-sm site-title-text'>Security</div>";
	html += "</div>";

	if (controllerData.other.have_i_been_pawned.length == false) {
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
                
        html += "<div class='pawnd-list-container'>";
            $.each(controllerData.other.have_i_been_pawned, function(companyKey, company) {
                    html += "<div id='" + company.Name + "' class='row pawned-row pawned-company-name align-items-center'>";
                        html += "<div class='col-sm col-lg record pawnd-record'>" + company.Name + "</div>";
                    html += "</div>";

                    html += "<div class='row pawned-row pawned-company-description align-items-center " + company.Name + "'>";
                            html += "<div class='col-sm col-lg record pawnd-record'><hr class='between-name-desc'>" + company.Description + "</div>";
                    html += "</div>";			
            });
        html += "</div>";
	}

	html += "</div>";

	$.each(controllerData.scrapers, function(scraperName, scraperData) {
		if (scraperData.length == false || controllerData.scrapers.bing[1].length == false) {

			html += "<div class='tab " + scraperName + "-tab'>";
			html += "<div class='row website-title'>";
				html += "<div class='col-sm site-title-text'>" + scraperName.charAt(0).toUpperCase() + scraperName.slice(1) + "</div>";
			html += "</div>";

			html += "<div class='col-sm col-lg empty-message'>Nothing found</div>";
			html += "</div>";

		}else {

			html += "<div class='tab " + scraperName + "-tab'>";
			html += "<div class='row website-title'>";
				html += "<div class='col-sm site-title-text'>" + scraperName.charAt(0).toUpperCase() + scraperName.slice(1) + "</div>";
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