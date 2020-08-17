function get_post_response($url, parameters) {
	var response;
	$.post({
		type: "POST",
		url: $url,
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
    	var call_controller = get_post_response("http://localhost/ajax_controller.php", {email : email});

    	var html;
		$.each(call_controller, function(key, value) {
			// html += "<li>" + value.title + "</li>"; 
			// html += "<li>" + value.excerpt + "</li>";
			html += "<li><a href='" + value.url + "'>" + value.url + "</li>";			
		});    	
    	$(".google-results-list").append(html);
    	console.log(call_controller);
	});
});