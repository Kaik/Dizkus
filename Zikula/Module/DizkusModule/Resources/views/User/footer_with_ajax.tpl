{* Rename this to User/footer.tpl if you want to use it*}

<div id="dzk_footer" class="z-clearfix">
    <script type="text/javascript">
        // <![CDATA[
        new Ajax.PeriodicalUpdater(
                'dzk_footer',
                Zikula.Config.baseURL + 'index.php',
        {
                    method: 'get',
                    parameters: 'module=Dizkus&type=ajax&func=forumusers',
                    frequency: 60
                });
        // ]]>
    </script>
    <noscript>
    {include file='Ajax/forumusers.html'}
    </noscript>
</div>
<p id="dzk_footer_line">{gt text="Powered by "}<a href="https://github.com/zikula-modules/Dizkus" title="Dizkus forum software for Zikula">Dizkus {modgetinfo modname=$module info='version'}</a></p>


{*

This is the end of the main Dizkus div, see dizkus_user_header for more information

*}

</div>