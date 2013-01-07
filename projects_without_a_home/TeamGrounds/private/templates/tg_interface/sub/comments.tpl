<div class="comments">
    {$pagination}
    
    {foreach from=$threads item=thread}
        {foreach from=$thread->posts item=post name=posts}
            <div class="{if $smarty.foreach.posts.first}head{else}reply{/if}">
                <div class="avatar"><a href="{$post->user->GetProfileURI()}"><img src="{$post->user->GetAvatarPath('medium')}" /></a></div>
                <div class="author"><a href="{$post->user->GetProfileURI()}">{$post->user->GetSafe('displayname')}</a></div>
                <div class="date">{$session->FormatDate($post.post_date)}, {$session->FormatTime($post.post_date)}</div>
                {if $can_delete}<div class="delete"><a href="{action_url do='DeletePost' query="post_id=`$post.post_id`"}">Delete</a></div>{/if}
                
                <div class="content">
                    {$post->GetParsed('content')}
                </div>
                
                <div class="reply"><a href="{custom_url query="replyto=`$post.post_id`"}">Reply</a></div>
            </div>
            
            {* //todo: Add AJAX reply code here *}
            {if isset($smarty.request.replyto) && ($smarty.request.replyto == $post.post_id)}
            <div class="reply_form">
                <h3>Post a Reply</h3>
                
                <form method="post" action="{$uri}">
                    {form_action do='NewReply'}
                    
                    <input type="hidden" name="post_id" value="{$smarty.request.replyto}">
                    
                    <textarea name="reply_comment">{if isset($smarty.post.reply_comment)}{$smarty.post.reply_comment}{elseif !$smarty.foreach.posts.first && !$smarty.foreach.posts.last}[quote]{$post->GetSafe('content')}[/quote]

{/if}</textarea>
                    
                    <input type="submit" name="submit" value="Reply">
                </form>
            </div>
            {/if}
        {/foreach}
    {/foreach}
    
    {$pagination}
    
    <div class="comment_form">
        <h3>Post a Comment</h3>
        
        <form method="post" action="{custom_url remove='replyto'}">
            {form_action do='NewThread'}
            
            <textarea name="thread_comment">{$smarty.post.thread_comment}</textarea>
            
            <input type="submit" name="submit" value="Post Comment">
        </form>
    </div>
</div>