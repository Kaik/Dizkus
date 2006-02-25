/**
 * pnforum.js
 *
 * $Id$
 *
 */

var preview;
var postingID;
var editstatus = false;
var replystatus = false;
var editchanged = false;
var lockstatus = false;
var stickystatus = false;
var subscribestatus = false;
var subscribeforumstatus = false;
var favoritestatus = false;
var subjectstatus = false;
var uisize = 'undefined';

function topicsubjectedit(topicid)
{
    if(subjectstatus == false) {
        subjectstatus = true;
        var pars = "module=pnForum&type=ajax&func=edittopicsubject&topic=" + topicid;
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: topicsubjecteditinit
            });
    }
}

function topicsubjecteditinit(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    
    var result = dejsonize(originalRequest.responseText);

    var topicsubjectID = 'topicsubject_' + result.topic_id;

    Element.hide($(topicsubjectID));
    updateAuthid(result.authid);

	new Insertion.After($(topicsubjectID), result.data);	
}

function topicsubjecteditcancel(topicid)
{
    var topicsubjectID = 'topicsubject_' + topicid;

    Element.remove($(topicsubjectID + '_editor'));
    Element.show($(topicsubjectID));
    subjectstatus = false;
}

function topicsubjecteditsave(topicid)
{
    var topicsubjectID = 'topicsubject_' + topicid;
    var editID = topicsubjectID + '_edit';
    var authID = topicsubjectID + '_authid';

    var pars = "module=pnForum&type=ajax&func=updatetopicsubject" +   
               "&topic=" + topicid +
               "&subject=" + encodeURIComponent($F(editID)) +
               "&authid=" + $F(authID);

    var myAjax = new Ajax.Request(                              
                    "index.php",                                
                    {                                           
                        method: 'post',                         
                        parameters: pars,                       
                        onComplete: topicsubjecteditsave_response
                    }                                           
                    );              

}

function topicsubjecteditsave_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    var topicsubjectID = 'topicsubject_' + result.topic_id;

    Element.remove($(topicsubjectID + '_editor'));
    updateAuthid(result.authid);
    
    $(topicsubjectID + '_content').innerHTML = result.topic_title;
    Element.show($(topicsubjectID));

    subjectstatus = false;
}

function toggleuserinfo(postid)
{
    var userinfoID = 'posting_' + postid + '_userinfo';

    if(uisize == 'undefined') {
        uisize = $(userinfoID).style.width;
        var height = $(userinfoID).style.height;
        new Insertion.Before(userinfoID, '<div id="uireplacement_' + postid + '" style="background: url(../pnimages/pixel.gif) repeat-y 0 0;">&nbsp;</div>');
        Element.hide($(userinfoID + 'content'));
        $(userinfoID).style.width = '2%';
    } else {    
        $(userinfoID).style.width = uisize;
        uisize = 'undefined';
        Element.remove($('uireplacement_' + postid));
        Element.show($(userinfoID + 'content'));
    }
}

function addremovefavorite(forumid, mode)
{
    if(favoritestatus == false) {
        favoritestatus = true;
        var pars = "module=pnForum&type=ajax&func=addremovefavorite&forum=" + forumid + "&mode=" + mode;
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: addremovefavorite_response
            });
    }
}

function addremovefavorite_response(originalRequest)
{
    favoritestatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.newmode) {
        case 'added':
            Element.hide('addfavoritebutton_' + result.forum_id);
            Element.show('removefavoritebutton_' + result.forum_id);
            break;
        case 'removed':
            Element.hide('removefavoritebutton_' + result.forum_id);
            Element.show('addfavoritebutton_' + result.forum_id);
            break;
        default:
             alert('wrong result from add/remove favorite');
    }
}

function subscribeunsubscribeforum(forumid, mode)
{
    if(subscribeforumstatus == false) {
        subscribeforumstatus = true;
        var pars = "module=pnForum&type=ajax&func=subscribeunsubscribeforum&forum=" + forumid + "&mode=" + mode;
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: subscribeunsubscribeforum_response
            });
    }
}

function subscribeunsubscribeforum_response(originalRequest)
{
    subscribeforumstatus = false;
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.newmode) {
        case 'subscribed':
            Element.hide('subscribeforumbutton_' + result.forum_id);
            Element.show('unsubscribeforumbutton_' + result.forum_id);
            break;
        case 'unsubscribed':
            Element.hide('unsubscribeforumbutton_' + result.forum_id);
            Element.show('subscribeforumbutton_' + result.forum_id);
            break;
        default:
             alert('wrong result from subscribe/unsubscribe');
    }
}

function subscribeunsubscribetopic(topicid, mode)
{
    if(subscribestatus == false) {
        subscribestatus = true;
        var pars = "module=pnForum&type=ajax&func=subscribeunsubscribetopic&topic=" + topicid + "&mode=" + mode;
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: subscribeunsubscribetopic_response
            });
    }
}

function subscribeunsubscribetopic_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        subscribestatus = false;
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.data) {
        case 'subscribed':
            Element.hide('subscribetopicbutton');
            Element.show('unsubscribetopicbutton');
            break;
        case 'unsubscribed':
            Element.hide('unsubscribetopicbutton');
            Element.show('subscribetopicbutton');
            break;
        default:
             alert('wrong result from subscribe/unsubscribe');
    }
    subscribestatus = false;
}

function stickyunstickytopic(topicid, mode)
{
    if(stickystatus == false) {
        stickystatus = true;
        var pars = "module=pnForum&type=ajax&func=stickyunstickytopic&topic=" + topicid + "&mode=" + mode;
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: stickyunstickytopic_response
            });
    }
}

function stickyunstickytopic_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        stickystatus = false;
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.data) {
        case 'sticky':
            Element.hide('stickytopicbutton');
            Element.show('unstickytopicbutton');
            break;
        case 'unsticky':
            Element.hide('unstickytopicbutton');
            Element.show('stickytopicbutton');
            break;
        default:
             alert('wrong result from sticky/unsticky');
    }
    stickystatus = false;
}

function lockunlocktopic(topicid, mode)
{
    if(lockstatus == false) {
        lockstatus = true;
        var pars = "module=pnForum&type=ajax&func=lockunlocktopic&topic=" + topicid + "&mode=" + mode;
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: lockunlocktopic_response
            });
    }
}

function lockunlocktopic_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        lockstatus = false;
        return;
    }
    var result = dejsonize(originalRequest.responseText);
    
    switch(result.data) {
        case 'locked':
            Element.hide('locktopicbutton');
            Element.show('unlocktopicbutton');
            break;
        case 'unlocked':
            Element.hide('unlocktopicbutton');
            Element.show('locktopicbutton');
            break;
        default:
             alert('wrong result from lock/unlock');
    }
    lockstatus = false;
}

function quickEdit(postid)
{
    if(editstatus == false) {
        editstatus = true;
        editchanged = false;
        postingID = postid;
        var pars = "module=pnForum&type=ajax&func=editpost&post=" + postid;
        var myAjax = new Ajax.Request(
            "index.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: quickEditInit
            });
    }
}

function quickEditInit(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var postingtextID = 'postingtext_' + postingID;
    var postinguserID = 'posting_' + postingID + '_userinfo';
    
    var result = dejsonize(originalRequest.responseText);
    
    Element.hide($(postingtextID));
    Element.hide($(postinguserID));
    updateAuthid(result.authid);

	new Insertion.After($(postingtextID), result.data);	

    Event.observe(postingtextID + '_edit',   'keyup', function(){quickEditchanged(postingID)}, false);		
	Event.observe(postingtextID + '_save',   'click',  function(){quickEditsave(postingID)}, false);
	Event.observe(postingtextID + '_cancel', 'click',  function(){quickEditcancel(postingID)}, false);
}

function quickEditchanged(postingID)
{
    if(editchanged == false) {
        editchanged = true;
        var postingtextstatusID = 'postingtext_' + postingID + '_status';
        $(postingtextstatusID).innerHTML = '<span style="color: red;">' + statusChanged + '</span>';
    }
    return;
}

function quickEditcancel(postingID)
{
    var postingtextID = 'postingtext_' + postingID;
    var postinguserID = 'posting_' + postingID + '_userinfo';
    Element.show($(postingtextID));
    Element.show($(postinguserID));
    Element.remove($(postingtextID + '_editor'));
    postingID = '';
    editstatus = false;
}
function quickEditsave(postingID)
{
    var postingtextID = 'postingtext_' + postingID;
    var statusID = postingtextID + '_status';
    var deletepost;
    var editID = postingtextID + '_edit';
    var authID = postingtextID + '_authid';

    if($(postingtextID + '_delete') && $(postingtextID + '_delete').checked == true) {
        $(statusID).innerHTML = '<span style="color: red;">' + deletingPost + '</span>';
        deletepost = '&delete=1';
    } else {
        $(statusID).innerHTML = '<span style="color: red;">' + updatingPost + '</span>';
        deletepost = '';
    }
    var pars = "module=pnForum&type=ajax&func=updatepost" +   
               "&post=" + postingID +
               deletepost +
               "&message=" + encodeURIComponent($F(editID)) +
               "&authid=" + $F(authID);

    var myAjax = new Ajax.Request(                              
                    "index.php",                                
                    {                                           
                        method: 'post',                         
                        parameters: pars,                       
                        onComplete: quickEditsave_response
                    }                                           
                    );              
    
}

function quickEditsave_response(originalRequest)
{
    // show error if necessary
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);
    var postingtextID = 'postingtext_' + postingID;
    var postingobjID = 'posting_' + postingID;
    var postinguserID = postingobjID + '_userinfo';
    
    Element.remove($(postingtextID + '_editor'));
    
    if(result.action == 'deleted') {
        Element.remove($(postingobjID));
    } else {
        $(postingtextID).innerHTML = result.post_text;
        Element.show($(postingtextID));
        Element.show($(postinguserID));
    }
    postingID = '';
    editstatus = false;
}

function createQuote(postid)
{
    var pars = "module=pnForum&type=ajax&func=preparequote&post=" + postid;
    var myAjax = new Ajax.Request(
        "index.php", 
        {
            method: 'post', 
            parameters: pars, 
            onComplete: createQuoteInit
        });
    
}

function createQuoteInit(originalRequest)
{
    if( originalRequest.status != 200 ) { 
        pnf_showajaxerror(originalRequest.responseText);
        return;
    }    

    var oldvalue = $('message').value;
    if(oldvalue.length != 0) {
        oldvalue += '\n\n';
    }
    var result = dejsonize(originalRequest.responseText);
    
    $('message').value = oldvalue + result.message  + '\n';
    Field.focus('message');
}

function createQuickReply()
{
    if(replystatus==false) {
        replystatus = true;
        showpnForumInfo(storingReply);
        
        var attach_signature = ''
        var sigObj = $('attach_signature');
        if(sigObj) {
            attach_signature = '&attach_signature=' + sigObj.value;
        }
        
        var subscribe_topic;
        var subObj = $('subscribe_topic');
        if(subObj) {
            subscribe_topic = '&subscribe_topic' + subObj.value;
        }

        var pars = "module=pnForum&type=ajax&func=reply" +   
                   "&topic=" + $F('topic') +
                   "&message=" + encodeURIComponent($F('message')) +              
                   attach_signature +            
                   subscribe_topic + 
                   "&authid=" + $F('authid');
        
        var myAjax = new Ajax.Request(                              
                        "index.php",                                
                        {                                           
                            method: 'post',                         
                            parameters: pars,                       
                            onComplete: createQuickReply_response
                        }                                           
                        );              
    }
}

function createQuickReply_response(originalRequest)
{
    hidepnForumInfo();

    // show error if necessary
    if( originalRequest.status != 200 ) { 
        alert(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    updateAuthid(result.authid);
    
    if(preview != 1) {
        // clear textarea
        $('message').value = '';
        // reset preview
        $('quickreply_preview').innerHTML = '&nbsp;';
        Element.hide($('quickreply_preview'));
        
        $('quickreplyposting').innerHTML = result.data;
        Element.show($('quickreplyposting'));
        
        new Insertion.After('quickreplyposting', '<li id="new_quickreplyposting"></li>');
        // clear old id
        $('quickreplyposting').id = '';
        // rename new id
        $('new_quickreplyposting').id = 'quickreplyposting';
    } else {
        $('quickreply_preview').innerHTML = result.data;
        Element.show($('quickreply_preview'));
        // clear preview
        $('preview').checked = false;
    }
    replystatus = false;

}

function previewQuickReply()
{
    if(replystatus==false) {
        replystatus = true;
        showpnForumInfo(preparingPreview);
        
        var pars = "module=pnForum&type=ajax&func=reply" +   
                   "&topic=" + $F('topic') +
                   "&message=" + encodeURIComponent($F('message')) +              
                   "&preview=1" +
                   "&authid=" + $F('authid');
        
        var myAjax = new Ajax.Request(                              
                        "index.php",                                
                        {                                           
                            method: 'post',                         
                            parameters: pars,                       
                            onComplete: previewQuickReply_response
                        }                                           
                        );              
    }
}

function previewQuickReply_response(originalRequest)
{
    hidepnForumInfo();

    // show error if necessary
    if( originalRequest.status != 200 ) { 
        alert(originalRequest.responseText);
        return;
    }

    var result = dejsonize(originalRequest.responseText);

    updateAuthid(result.authid);
    
    $('quickreply_preview').innerHTML = result.data;
    Element.show($('quickreply_preview'));
    replystatus = false;
}

function clearQuickReply()
{
    $('message').value = '';
    $('quickreply_preview').innerHTML = '&nbsp;';
    Element.hide($('quickreply_preview'));
    replystatus = false;
}                        

function showpnForumInfo(infotext)
{
    $('pnforuminformation').innerHTML = infotext;
    $('pnforuminformation').style.visibility = 'visible';
    
}

function hidepnForumInfo()
{
    $('pnforuminformation').innerHTML = '&nbsp;';
    $('pnforuminformation').style.visibility = 'hidden';
}

function pnf_showajaxerror(error)
{
    alert(error);
}

function updateAuthid(authid)
{
    if(authid.length != 0) {
        for(var i=0; i<document.forms.length; i++) {
            for(var j=0; j<document.forms[i].elements.length; j++) {
                if(document.forms[i].elements[j].type=='hidden' && document.forms[i].elements[j].name=='authid') {
                    //alert(i + ', ' + j + ': hidden authid - ' + document.forms[i].elements[j].value);
                    document.forms[i].elements[j].value = authid;
                }
            }
        }
    }
}

function dejsonize(jsondata)
{
    var result;
    try {
        result = eval('(' + jsondata + ')');
    } catch(error) {
        alert('illegal JSON response: ' + error);
    }
    return result;
}

