function getPostResponse(url, parameters) {
	var response;
	$.post({
		type: "POST",
		url: url,
		dataType:"json",
		data: (parameters),
		async: false,
		success: function(data) {
			response =  data;
		}
	});
	return response;
}

$(document).ready(function() {
	$("#submit-main-form").on("click", function(){

		var email = $("#email").val();
    	var callController = getPostResponse("http://localhost/ajax_controller.php", {email : email});

    	var html;
		$.each(callController, function(scraperName, scraperData) {

			$.each(scraperData, function(pageIndex, pageData) {

				$.each(pageData, function(recordIndex, record) {

					html += "<li><a href='" + record.title + "' target=_blank>" + record.title + "</li>";

				});

			});

		});

    	$(".google-results-list").append(html);
    	// console.log(callController);
	});
});