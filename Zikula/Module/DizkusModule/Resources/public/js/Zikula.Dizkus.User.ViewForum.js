/**
 * Zikula.Dizkus.User.ViewForum.js
 * 
 * jQuery based JS
 */

jQuery(document).ready(function() {
    jQuery("#forum-favourite").click(modifyForum);
    jQuery("#forum-subscription").click(modifyForum);
});

function modifyForum(e) {
    var action;
    var i = jQuery(this);
    switch(i.attr('id')) {
        case 'forum-subscription':
            action = i.data('status') == 0 ? 'subscribe' : 'unsubscribe';
            break;
        case 'forum-favourite':
            action = i.data('status') == 0 ? 'addToFavorites' : 'removeFromFavorites';
            break;
        default:
            console.log('Wrong action');
            return;
    }

    jQuery.ajax({
        type: "POST",
        data: {
            forum: jQuery('#forum_id').val(),
            action: action
        },
        url: Zikula.Config.baseURL + "index.php?module=ZikulaDizkusModule&type=ajax&func=modifyForum",
        success: function(result) {
            if (result == 'successful') {
                if (action == 'addToFavorites') {
                    i.text(unfavouriteForum);
                } else if (action == 'removeFromFavorites') {
                    i.text(favouriteForum);
                } else if (action == 'subscribe') {
                    i.text(unsubscribeForum);
                } else if (action == 'unsubscribe') {
                    i.text(subscribeForum);
                }
                // invert data-status value
                i.data('status', i.data('status') == 0 ? 1 : 0);
            } else {
                alert('Error! Erroneous result from modifyForum request.');
            }
        },
        error: function(result) {
            DizkusShowAjaxError(result.responseJSON.core.statusmsg);
            return;
        }
    });
    e.preventDefault();
}