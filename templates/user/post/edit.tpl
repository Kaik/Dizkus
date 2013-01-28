{gt text="Edit post" assign=templatetitle}
{pagesetvar name=title value=$templatetitle}
{include file='user/header.tpl'}

{*modcallhooks hookobject='item' hookaction='display' hookid=$post.topic_id implode=false*}

{if $preview}
<div id="editpostpreview" style="margin:1em 0;">
    {include file='user/post/single.tpl'}
</div>
{/if}

<div id="dzk_newtopic" class="forum_post post_bg2 dzk_rounded">
    <div class="inner">

        <div class="dzk_subcols z-clearfix">

        {form cssClass="z-form"}
        {formvalidationsummary}
                <div>
                    <fieldset>
                        <legend class="post_header">{gt text="Edit post"}: {$topic_subject|safetext}</legend>
                        <div class="post_text_wrap">
                            <div id="dizkusinformation" style="visibility: hidden;">&nbsp;</div>

                            {*if $post.moderate eq true OR $post.edit_subject eq true}
                            <div>
                                <label for="subject">{gt text="Subject line"}</label><br />
                                <input style="width: 98%" type="text" name="subject" size="80" maxlength="100" id="subject" tabindex="0" value="{$post.topic_subject|safehtml}" />
                            </div>
                            {/if*}
                            <div>
                                {formlabel for="message" __text="Message body"}<br />
                                {notifydisplayhooks eventname='dizkus.ui_hooks.editor.display_view' id='message'}
                                {formtextinput textMode="multiline" id="post_text" rows="10" cols="60"}
                                {if $modvars.Dizkus.striptags == 'yes'}
                                <p>{gt text="No HTML tags allowed (except inside [code][/code] tags)"}</p>
                                {/if}
                            </div>

                            <div class="dzk_subcols z-clearfix">
                                <div id="editpostoptions" class="dzk_col_left">
                                    <ul>
                                        {if $moderate eq true}
                                        <li><strong>{gt text="Options"}</strong></li>
                                        {if !$post_first}
                                        <li>
                                            {formcheckbox id="delete"}
                                            {formlabel for="delete" __text="Delete post"}
                                        </li>
                                        {/if}
                                        <li>
                                            {formcheckbox id="post_attach_signature"}
                                            {formlabel for="post_attach_signature" __text="Attach my signature"}
                                        </li>
                                        {/if}
                                    </ul>
                                </div>
                            </div>

                            <div class="z-formbuttons z-buttons">
                                {formbutton id="submit"  commandName="submit"  __text="Save"    class="z-bt-ok"}
                                {formbutton id="preview" commandName="preview" __text="preview" class="z-bt-preview"}
                                {formbutton id="cancel"  commandName="cancel"  __text="Cancel"  class="z-bt-cancel"}
                            </div>

                        </div>
                    </fieldset>
                </div>
            {/form}
        </div>

    </div>
</div>
{include file='user/footer.tpl'}