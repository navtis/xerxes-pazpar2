/**
 * record page functions
 * - reveal toggle for long lists of holdings
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */
$(document).ready(function(){
	// start with holdings hidden
	if ($("button#holdings-toggle").length > 0 )
		$('#record-full-text').css('display','none');

	$("button#holdings-toggle").click(function () {
		$("#record-full-text").slideToggle("slow", function(){
			if ($("button#holdings-toggle").html() == 'Show holdings')
				$("button#holdings-toggle").html('Hide holdings');
			else
				$("button#holdings-toggle").html('Show holdings');

		});
	});
});  



