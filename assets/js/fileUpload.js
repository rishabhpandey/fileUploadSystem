$(document).ready(function(){
	
	var fileUploadForm = $("#fileUpload");
	
	var validator = fileUploadForm.validate({
		
		rules:{
			userFile :{ required : true },
		},
		messages:{
			userFile :{ required : "This field is required" },			
		}
	});
	
	//To fade flashdata 
	var timeout = 3000; // in miliseconds (3*1000)

  	$('.alert').delay(timeout).fadeOut(300);
});