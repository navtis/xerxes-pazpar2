/**
 * index page library checkbox management
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
	var res = target_checkboxes(this.name, this);
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

/** Called onChange of any checkbox 
 *  If higher node set/cleared, sets/clears all lower nodes
 *  If lower node cleared, clears all higher nodes
 *  @param type String either 'region' or 'target'
 *  @param id String Unique identifier for node 
 * 
 */

function target_checkboxes(type, cb)
{
    if (type == 'region[]')
    {
        if ( $(cb).is(":checked") )
        {
            // turn children on
            //console.log($(cb).closest('li').find('.subjectDatabaseCheckbox'));
            $(cb).closest('li').find('.subjectDatabaseCheckbox').prop("checked", true);
        }
        else
        {
            // turn children off
            $(cb).closest('li').find('.subjectDatabaseCheckbox').prop("checked", false);
            // turn parents off, if any, without affecting siblings
            // FIXME very inefficient: surely there is a selector that will do this
            var curid = cb.id;
            $(cb).parents('li').find('span .subjectDatabaseCheckbox').each(function(i) {
                if (this.id === curid.substr( 0, this.id.length ))
                    $(this).prop("checked", false);
                });
        }
    }
    else if ((type == 'target[]')||(type == 'subject[]'))
    {
        if (! $(cb).is(":checked") )
        {
            // turn parents off, if any, without affecting siblings
            // FIXME very inefficient: surely there is a selector that will do this
            // FIXME DRY
            var curid = cb.id;
            $(cb).parents('li').find('span .subjectDatabaseCheckbox').each(function(i) {
                if (this.id === curid.substr( 0, this.id.length ))
                    $(this).prop("checked", false);
                });
        }
    }
    else
    { 
        alert ('Error in checkbox setting routine');
    }
}

