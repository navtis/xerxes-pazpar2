/**
 * options page library checkbox management
 * for pazpar2 interface
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */


$(document).ready(addHandlers);

function setSubmitStatus()
{
    if ($("input.subjectDatabaseCheckbox:checked").length == 0)
    {
        $('#change-targets').attr("value", "Select at least one");
        $('#change-targets').attr("disabled", "true");
    }
    else
    {
        $('#change-targets').attr("value", "Save changes");
        $('#change-targets').removeAttr('disabled');
    }
}

function addHandlers()
{
    $('input[type="checkbox"]').click(function() {
        setSubmitStatus();
    });

    $('#all-button').click(function() {
        $('#target-form').find('.subjectDatabaseCheckbox').prop("checked", true);
        setSubmitStatus();
    });

    $('#clear-button').click(function() {
        $('#target-form').find('.subjectDatabaseCheckbox').prop("checked", false);
        setSubmitStatus();
    });

}

