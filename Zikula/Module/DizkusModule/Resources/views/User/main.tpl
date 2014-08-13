{include file='User/header.tpl'}

<div class="panel panel-default">
    <div class="panel-heading"><h2 class='fa fa-home'>&nbsp;{gt text="Forums index page"}</h2>
        {if isset($numposts)}
        <div style='position:absolute; top:0; right:0; padding: 1.25em;'>
            <a class='btn btn-default btn-sm tooltips' title="{gt text="RSS Feed"}" href="{modurl modname=$module type='user' func='feed'}"><i class='fa fa-rss-square fa-150x fa-orange'></i>
            {gt text="Total posts: %s" tag1=$numposts}</a>
        </div>
        {/if}
    </div>
    {foreach item='parent' from=$forums}
        {include file='User/forum/singleforumtable.tpl'}
    {/foreach}
    {include file='User/forum/panelfooter.tpl'}
</div>

{include file='User/footer.tpl'}