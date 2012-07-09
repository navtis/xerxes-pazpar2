/**
 * status page communication with pazpar2
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */
$(document).ready(function(){

    // end the search early if requested by user
    $("#terminator").click(function() {
        $.ajax(
        { 
            url: "pazpar2/ajaxterminate",
            data: "session=" + $('#pz2session').data('value'),
            cache: false,
            datatype: "json"
        })
        return false;
    });
});  

