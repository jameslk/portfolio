{if !empty($message_data)}
    <div id="tg_messages">
        {foreach from=$message_data item=data}
            <div class="{$data.type}">{$data.message}</div>
        {/foreach}
    </div>
{/if}