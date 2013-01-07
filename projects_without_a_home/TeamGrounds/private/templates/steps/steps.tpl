<div class="steps">
    {foreach from=$steps key=step item=title name=steps}
        {if $smarty.foreach.steps.first}
            <span{if !$selected_step || ($step == $selected_step)} class="active"{/if}>{$title}</span>
        {else}
            &gt; <span{if $step == $selected_step} class="active"{/if}>{$title}</span>
        {/if}
    {/foreach}
</div>