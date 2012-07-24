/**
 * options page functions
 * - slider for pazpar2 max_records
 * - affilation drop down management
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */
$(document).ready(function(){

    /* fetch latest max_records value */
    var old_max = $("#old-max-records").attr('value');

    old_max = logslider(old_max, true);

    /* initiate the slider */
    $("#slider").slider({min:0, max:100});
    $("#slider").slider( "option", "value", old_max );


    /* submit the new slider value */
    $("#max_records_form").submit(function () {
	var max_records = logslider( $("#slider").slider( "option", "value" ), false );
        $("#new-max-records").val(max_records);
        // now let the original submit continue..	
    });

    // manage the affiliation drop downs (may not be present)
    $("select#affiliation").change(function() {
        $.ajax(
        { 
            url: "pazpar2/ajaxgetroles",
            data: "format=json&affiliation=" + $('#affiliation').val(),
            cache: false,
            datatype: "json",
	    success: function (data) {
		// if affiliation removed hide role selection
		if (data.affiliation == ''){
			$('select#role').css('visibility', 'hidden');
			$('#submit-affiliation').css('visibility', 'hidden');
		} else {
			var options = '<option value="">Select role</option>';
			for (var key in data.roles){
			           options += '<option value="' + key + '">' + data.roles[key] + '</option>';
		        }
			$("select#role").html(options);
			$('select#role').css('visibility', 'visible');
			$('#submit-affiliation').css('visibility', 'visible');
			$('#submit-affiliation').attr("disabled", "true");
		}
	    },
	    error: function(e, xhr){
		alert( "System error: unable to select affiliation");	    
	    }
	});
    });

    // only allow submit if role selected
    $("select#role").change(function() {
	var role = $('select#role').val();
	if (role == '')
		$('#submit-affiliation').attr("disabled", "true");
    	else {
		$('#readable_role').val($("select#role :selected").text());
		$('#readable_affiliation').val($("select#affiliation :selected").text());

		$('#submit-affiliation').removeAttr('disabled');
	}
    });
});  


function logslider(value, inverse) {
  //position must be between 0 and 100
  var minp = 0;
  var maxp = 100;
	  
  // The result should be between 100 an 50000
  var minv = Math.log(100);
  var maxv = Math.log(50000);

  // calculate adjustment factor
  var scale = (maxv-minv) / (maxp-minp);

  if (inverse)
	return (Math.log(value)-minv) / scale + minp;
  else
  	return Math.floor( Math.exp(minv + scale*(value-minp)) );
}

