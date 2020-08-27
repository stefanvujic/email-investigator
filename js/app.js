$(function() {
	$("#submit-main-form").on("click", function(){

		var html;
		html = "";
		var email = $("#email").val();
		var loader = "<div class='row' id='loader'><div class='col-sm-12 col-lg'><img src='/assets/loader.gif'/></div></div>";

		
		var capchaResponse = grecaptcha.getResponse();

		$("#error-message").empty();
		$("#main-result").empty();
		$('div#main-result').append(loader);
		$("div#submit-main-form").css({"pointer-events": "none"});

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
					$.each(controllerData.data.have_i_been_pawned, function(siteKey, siteName) {
						console.log(siteName);
					});
 
					$.each(controllerData.scrapers, function(scraperName, scraperData) {
						html += "<div class='row website-title'>";
							html += "<div class='col-sm'>" + scraperName + "</div>";
						html += "</div>";

						html += "<hr>";

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
					});
				}
				console.log(controllerData)
				$("div#main-result").append(html);	
			},

			complete: function(){
				$("#submit-main-form").css({"pointer-events": "auto"});
				$("#loader").hide();
			}
		});
	});
});