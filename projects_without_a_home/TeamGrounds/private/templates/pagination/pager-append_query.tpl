{assign var='url' value=$url|regex_replace:'/[&]?page=\d+/':''}

<span class="pager">
{if $total_pages_before > 0}
    <a class="pager_prev" href="{custom_url url=$url query="`$page_var`=`$page-1`"}">&laquo; Previous</a>
    
    {if $total_pages_before > 7}
        <a href="{custom_url url=$url query="`$page_var`=1"}">1</a>
        <a href="{custom_url url=$url query="`$page_var`=2"}">2</a>
        ..
        {section name='prev_loop1' start=$page-3 loop=$page}
            <a href="{custom_url url=$url query="`$page_var`=`$smarty.section.prev_loop1.index`"}">{$smarty.section.prev_loop1.index}</a>
        {/section}
    {else}
        {section name='prev_loop2' start=1 loop=$total_pages_before+1}
            <a href="{custom_url url=$url query="`$page_var`=`$smarty.section.prev_loop2.index`"}">{$smarty.section.prev_loop2.index}</a>
        {/section}
    {/if}
{else}
    <span class="pager_prev">&laquo; Previous</span>
{/if}

<span class="pager_cur">{$page}</span>

{if $total_pages_after > 0}
    {if $total_pages_after > 7}
        {section name='next_loop1' start=$page+1 loop=$page+4}
            <a href="{custom_url url=$url query="`$page_var`=`$smarty.section.next_loop1.index`"}">{$smarty.section.next_loop1.index}</a>
        {/section}
        ..
        <a href="{custom_url url=$url query="`$page_var`=`$total_pages-1`"}">{$total_pages-1}</a>
        <a href="{custom_url url=$url query="`$page_var`=`$total_pages`"}">{$total_pages}</a>
    {else}
        {section name='next_loop2' start=$page+1 loop=$page+$total_pages_after+1}
            <a href="{custom_url url=$url query="`$page_var`=`$smarty.section.next_loop2.index`"}">{$smarty.section.next_loop2.index}</a>
        {/section}
    {/if}
    
    <a class="pager_next" href="{custom_url url=$url query="`$page_var`=`$page+1`"}">Next &raquo;</a>
{else}
    <span class="pager_next">Next &raquo;</span>
{/if}