{usergetvar name='uid' assign='current_userid'}
{assign var='msgmodule' value=$modvars.ZConfig.messagemodule}
{modapifunc modname=$module type='UserData' func='getUserOnlineStatus' uid=$post.poster.user_id assign='isPosterOnline'}

{if isset($post_counter) AND isset($post_count) AND $post_counter == $post_count}<a id="bottom"></a>{/if}
<a id="pid{$post.post_id}"></a>

<div id="posting_{$post.post_id}" class="panel panel-default">
    {if $isPosterOnline}<div class="ribbon-wrapper-right"><div class="ribbon-right ribbon-blue">{gt text="ONLINE"}</div></div>{/if}
    {if isset($preview) AND ($preview eq 1)}<div class="ribbon-wrapper-left"><div class="ribbon-left ribbon-red">{gt text="PREVIEW"}</div></div>{/if}
    <div class="panel-heading">
        <div class="postdate{if $isPosterOnline} padright{/if}">
            {if isset($topic)}<a class="tooltips" href="{route name='zikuladizkusmodule_user_viewtopic' topic=$topic.topic_id start=$start}#pid{$post.post_id}" title="{gt text="Link to this post"}"><i class="fa fa-file-o"></i></a>{/if}
            <strong>{gt text="Posted"}: </strong>{$post.post_time|dateformat:'datetimebrief'}
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div id="posting_{$post.post_id}_userinfo" class="post_author col-md-3">
                <div class="dzk_avatar">
                    <strong>{$post.poster.user.uname|profilelinkbyuname}</strong>
                    <br />
                    <div>{useravatar uid=$post.poster.user_id class="img-rounded"}</div>

                    {if !empty($post.poster.rank.image)}
                        {if $post.poster.rank.rank_link neq ''}
                            <a href="{$post.poster.rank.rank_link}" title="{$post.poster.rank.rank_link}">
                            {/if}
                            <img class="userinforankimage" src="{$baseurl}{$post.poster.rank.imageLink}" alt="{$post.poster.rank.title}" title="{$post.poster.rank.description}" />
                        {if $post.poster.rank.rank_link neq ''}</a>{/if}
                    {else}
                        {getRankByPostCount posts=$post.poster.postCount ranks=$ranks assign='posterRank'}
                        {if $posterRank.rank_link neq ''}
                        <a href="{$posterRank.rank_link}" title="{$posterRank.rank_link}">
                        {/if}
                        {if $posterRank.image neq ''}
                            <img class="userinforankimage" src="{$baseurl}{$posterRank.imageLink}" alt="{$posterRank.title}" title="{$posterRank.description}" />
                        {/if}
                    {if $posterRank.rank_link neq ''}</a>{/if}
                {/if}
                </div>

                <ul>
                {if !empty($post.poster.rank.title)}
                    <li><strong>{gt text="Rank"}: </strong>{$post.poster.rank.title|safetext}</li>
                {else}
                    <li><strong>{gt text="Rank"}: </strong>{$posterRank.title|safetext}</li>
                {/if}
                    {usergetvar name='user_regdate' assign="user_regdate"}
                    <li><strong>{gt text="Registered"}: </strong>{$post.poster.user.user_regdate|dateformat:'datebrief'}</li>
                {if !$isPosterOnline}
                    <li><strong>{gt text="Last visit"}: </strong>{$post.poster.lastvisit|dateformat:'datebrief'}</li>
                {/if}


                    <li><strong>{gt text="Posts"}: </strong>{$post.poster.postCount}</li>
                {if $coredata.logged_in eq true}
                    <li>
                        {if $msgmodule}
                            <a class="tooltips" title="{gt text="Send private message"}" href="{modurl modname=$msgmodule func="user" func="newpm" uid=$post.poster.user_id}"><i class="fa fa-envelope-o fa-150x"></i></a>
                        {/if}
                        {if isset($topic) AND isset($post.poster_data) AND $post.poster_data.moderate eq true AND $post.poster_data.seeip eq true}
                            <a class="tooltips" title="{gt text="View IP address"}" href="{route name='zikuladizkusmodule_user_viewipdata' post=$post.post_id}"><i class="fa fa-info-circle fa-150x"></i></a>
                        {/if}
                    </li>
                {/if}
                </ul>
            </div>

            <div class="postbody col-md-9">
                <div class="dizkusinformation_post" id="dizkusinformation_{$post.post_id}" style="display: none;">{img modname='core' set='ajax' src='indicator.white.gif'}</div>
                <div class="content" id="postingtext_{$post.post_id}">
                    <div id='solutionPost_{$post.post_id}' class="alert alert-success"{if (!isset($topic.solved) OR ($topic.solved neq $post.post_id))} style="display:none;"{/if}>
                        {if ((isset($permissions.edit) AND $permissions.edit eq 1) OR (isset($topic.poster.user_id) AND $topic.poster.user_id eq $current_userid))}
                            <a class="unsolvetopic close tooltips" aria-hidden="true" data-action="unsolve" data-post="{$post.post_id}" href="{route name='zikuladizkusmodule_user_changetopicstatus' action='unsolve' topic=$topic.topic_id}" title="{gt text="Remove: this is not the solution"}">&times;</a>
                        {/if}
                        <i class="fa fa-check fa-2x"></i> {gt text='This post has been marked as the solution.'}
                    </div>
                    {$post.post_text|dzkVarPrepHTMLDisplay|notifyfilters:'dizkus.filter_hooks.post.filter'|transformtags}
                    {if $post.attachSignature AND ($modvars.ZikulaDizkusModule.removesignature == 'no')}
                        {usergetvar name='signature' assign="signature" uid=$post.poster.user_id}
                        {if !empty($signature)}
                            <div class="dzk_postSignature">
                                {$modvars.ZikulaDizkusModule.signature_start}
                                <br />{$signature|dzkVarPrepHTMLDisplay|notifyfilters:'dizkus.filter_hooks.post.filter'}
                                <br />{$modvars.ZikulaDizkusModule.signature_end}
                            </div>
                        {/if}
                    {/if}
                </div>
                {notifydisplayhooks eventname='dizkus.ui_hooks.post.ui_view' id=$post.post_id}
            </div>
        </div>
    </div>
    <div class="panel-footer">
        {if !isset($preview) OR $preview neq true}
        <div class="pull-right">
            <ul id="postingoptions_{$post.post_id}" class="javascriptpostingoptions list-inline">
                {if isset($permissions.moderate) AND $permissions.moderate eq true}
                    {if ((isset($num) AND $num neq 0) OR (isset($topic) AND $start neq 0)) AND !$post.isFirstPost}
                        <li><a class="fa fa-arrow-right fa-150x tooltips" title="{gt text="Move post"}" href="{route name='zikuladizkusmodule_user_movepost' post=$post.post_id}"></a></li>
                        <li><a class="fa fa-scissors fa-150x tooltips" title="{gt text="Split topic"}" href="{route name='zikuladizkusmodule_user_splittopic' post=$post.post_id}"></a></li>
                    {/if}
                {/if}
                {if isset($topic) AND $topic.status neq 1}
                    {if isset($permissions.comment) AND $permissions.comment eq true AND $modvars.ZikulaDizkusModule.ajax}
                        <li><a class="quotepostlink fa fa-quote-left fa-150x tooltips" id="quotebutton_{$post.post_id}" title="{gt text="Quote post"}" onclick="quote('{dzkquote text=$post.post_text|htmlentities uid=$post.poster.user_id}');"></a></li>
                    {/if}
                    {if (isset($permissions.edit) AND $permissions.edit eq 1) OR $post.userAllowedToEdit}
                        <li><a class="editpostlink fa fa-pencil-square-o fa-150x tooltips" data-post="{$post.post_id}" id="editbutton_{$post.post_id}" title="{gt text="Edit post"}" href="{route name='zikuladizkusmodule_user_editpost' post=$post.post_id}"></a></li>
                    {/if}
                    {if ((isset($permissions.edit) AND $permissions.edit eq 1) OR $topic.poster.user_id eq $current_userid) AND ($modvars.ZikulaDizkusModule.solved_enabled|default:0) AND !$post.isFirstPost}
                    {if $topic.solved lt 0}
                        {assign var='stylestmt' value=''}
                    {else}
                        {assign var='stylestmt' value='style="display:none" '}
                    {/if}
                    <li>
                        <a {$stylestmt}class="solvetopic tooltips" data-post="{$post.post_id}" data-action="solve" href="{route name='zikuladizkusmodule_user_changetopicstatus' action='solve' topic=$topic.topic_id post=$post.post_id}" title="{gt text="Mark as solved by this post"}">
                            <i class="fa fa-check fa-150x"></i>
                        </a>
                    </li>
                    {/if}
                {elseif isset($topic)}
                    <li><i class="fa fa-lock fa-150x tooltips" title='{gt text='This topic is locked'}'></i></li>
                {/if}
                {if !isset($notify) OR $notify eq false}
                    {if isset($permissions.comment) AND $permissions.comment eq true}
                        <li><a class="fa fa-bell-o fa-150x tooltips" href="{route name='zikuladizkusmodule_user_report' post=$post.post_id}" title="{gt text="Notify moderator about this posting"}"></a></li>
                    {/if}
                    <li><a class="fa fa-chevron-circle-up fa-150x tooltips" title="{gt text="Top"}" href="#top">&nbsp;</a></li>
                {/if}
            </ul>
        </div>
        <div class="clearfix"></div>
        {/if}
    </div>
</div>