/**
 * pazpar2 pinger
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */
$(document).ready(function(){

    var session = $('#pz2session').data('value');

    var pinger = setInterval(function()
    {
        $.ajax(
        { 
            url: "pazpar2/ajaxping",
            data: "session=" + session,
            cache: false,
            datatype: "html",
            success: function(data)
            {
                var data = JSON.parse(data);
                // carry on pinging unless session dead
                if (data['live'] == 'false')
                {
                    alert("Debug: session died");
                    clearInterval(pinger);
                }
            },
            error: function(e, xhr)
            {
                // no point in pinging if comms down
                clearInterval(pinger);
                alert('Session timed out - about to restart');
                var url = '/';
                window.location = url;
            }
        }) 
    }, 50000); // 50 seconds default between pings 
});  
 
