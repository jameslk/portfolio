<div class="tabs">
    <ul>
        {foreach from=$tabs key=tab item=title}
            <li {if $tab == $selected_tab}class="selected"{/if}>
                <a href="{custom_url query="`$tab_var`=`$tab`"}">{$title}</a>
            </li>
        {/foreach}
    </ul>
</div>