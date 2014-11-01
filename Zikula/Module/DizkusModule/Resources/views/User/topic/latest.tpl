{gt text="Latest forum posts" assign='templatetitle'}
{pagesetvar name=title value="`$templatetitle` - `$text`"}

{include file='User/header.tpl'}

<div id="latestposts">

    <nav class="navbar navbar-default" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-latest-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="navbar-latest-collapse">
            <form id="nohours-form" class="navbar-form navbar-right" action="{route name='zikuladizkusmodule_user_viewlatest'}" method="post">
                <span>{gt text='Last'}</span>&nbsp;
                <div class="form-group">
                    <input type='hidden' name='selorder' value='5'>
                    <input type="text" class="form-control input-sm" name="nohours" id="Dizkus_hours" size="3" value="{$nohours}" maxlength="3" tabindex="0">
                </div>
                <button type="submit" class="btn btn-default btn-sm">{gt text="hours"}</button>
            </form>
            <ul class="nav navbar-nav navbar-right">
                <li{if $selorder eq "unanswered"} class="active"{/if}><a href="{route name='zikuladizkusmodule_user_viewlatest' selorder='unanswered'}">{gt text="Unanswered"}</a></li>
                <li{if $selorder eq "unsolved"} class="active"{/if}><a href="{route name='zikuladizkusmodule_user_viewlatest' selorder='unsolved'}">{gt text="Unsolved"}</a></li>
                <li{if $selorder eq "3"} class="active"{/if}><a href="{route name='zikuladizkusmodule_user_viewlatest' selorder=3}">{gt text="Since Yesterday"}</a></li>
                <li{if $selorder eq "2"} class="active"{/if}><a href="{route name='zikuladizkusmodule_user_viewlatest' selorder=2}">{gt text="Today"}</a></li>
                <li{if $selorder eq "4"} class="active"{/if}><a href="{route name='zikuladizkusmodule_user_viewlatest' selorder=4}">{gt text="Last week"}</a></li>
                {if $last_visit_unix neq 0}
                    <li{if $selorder eq "6"} class="active"{/if}><a href="{route name='zikuladizkusmodule_user_viewlatest' selorder=6 last_visit_unix=$last_visit_unix}">{gt text="Last visit"}</a></li>
                {/if}
            </ul>
        </div><!-- /.navbar-collapse -->
    </nav>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>{gt text="Latest forum posts"} ({$text})</h2>
        </div>
        {pager rowcount=$pager.numitems limit=$pager.itemsperpage|default:15 posvar='start' route='zikuladizkusmodule_user_viewlatest'}
        {include file='User/forum/forumtopicstable.tpl'}
        {pager rowcount=$pager.numitems limit=$pager.itemsperpage|default:15 posvar='start' route='zikuladizkusmodule_user_viewlatest'}
        {include file='User/forum/panelfooter.tpl'}
    </div>
</div>

{include file='User/footer.tpl'}