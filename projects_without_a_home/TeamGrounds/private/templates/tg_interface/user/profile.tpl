<div id="primary">
    <div id="main_info">
        <div>
            <h1>{$player.displayname|clean_data}</h1>
            
            {if $is_self}
                <div class="edit_link"><a href="/settings?tab=account">Edit</a></div>{* //todo: Change this *}
            {/if}
            
            <img src="{$player.avatar_url}" />
            
            {if $player.is_online}
                <div>Online</div>
            {else}
                {if $player.lastseen > time()+60*60*24*7}
                    <div>Last online on {$player.lastseen|date_format:'%e %B, %Y'}</div>
                {else}
                    <div>Last online {$player.lastseen_duration} ago</div>
                {/if}
            {/if}
            
            {if $can_addfriend}
                <div class="add_to_friends">
                    <a href="{action_url do='AddFriend'}">Add to Friends</a>
                </div>
            {/if}
            
            {if $can_recruit}
                <div class="add_to_friends">
                    <a href="{action_url do='Recruit'}">Recruit Player</a>
                </div>
            {/if}
            
            {if $can_endorse}
                <div>
                    <a href="{action_url do='Endorse'}">Endorse Player</a>
                </div>
            {/if}
        </div>
        
        <div>
            <h2>Summary</h2>
            
            {if $is_self}
                <div class="edit_link"><a href="/editprofile?tab=summary">Edit</a></div>{* //todo: Change this *}
            {/if}
            
            <div>
                {if $player.summary}
                    {$player.summary}
                {else}
                    No information given.
                {/if}
            </div>
        </div>
    </div>
    
    <div id="gamexp">
        <h2>Game Experience</h2>
        
        {if $is_self}
            <div class="edit_link"><a href="/editprofile?tab=gamexp">Edit</a></div>{* //todo: Change this *}
        {/if}
        
        {if !empty($player.games)}
            {foreach from=$player.games item=game}
            {if !empty($game.gamexp_array)}
                {assign var=has_gamexp value='true'}
                
                <div class="game">
                    <h3>{$game.title}</h3>
                    
                    {if $is_self}
                        <div class="edit_link"><a href="/editprofile?tab=gamexp&ugame_id={$game.id}">Edit</a></div>{* //todo: Change this *}
                    {/if}
                    
                    {foreach from=$game.gamexp_array item=gamexp}
                        <div class="xp">
                            <h4>{$gamexp.title}:</h4>
                            
                            <p>{$gamexp->GetParsed('content')}</p>
                        </div>
                    {/foreach}
                </div>
            {/if}
            {/foreach}
            
            {if !$has_gamexp}
                This noob has no game experience.
            {/if}
        {else}
            This noob has no game experience.
        {/if}
    </div>
    
    <div id="screenshots">
        <h2>Screenshots &amp; Pictures</h2>
    </div>
    
    <div id="videos">
        <h2>Videos</h2>
    </div>
    
    <div id="comments">
        <h2>Comments</h2>
        
        {$subs.comments}
    </div>
</div>

<div id="secondary">
    <div id="player_info">
        <div>
            <h2>Games</h2>
            
            {if $is_self}
                <div class="edit_link"><a href="/settings?tab=games">Edit</a></div>{* //todo: Change this *}
            {/if}
            
            {if !empty($player.games)}
                <ul>
                    {foreach from=$player.games item=game}
                        <li>
                            {if $game.external}
                                <a href="http://en.wikipedia.org/wiki/Special:Search/{$game.title}" target="_blank">{$game.title}</a>
                            {/if} {* //todo: Add internal game link *}
                        </li>
                    {/foreach}
                </ul>
            {else}
                This player has not added any games yet.
            {/if}
        </div>
        
        {if $is_self || $show_personal}
            <div>
                <h2>Personal Info</h2>
                
                {if $is_self}
                    <div class="edit_link"><a href="/editprofile?tab=personal">Edit</a></div>{* //todo: Change this *}
                {/if}
                
                {if $player.realname}
                    <div>Real Name: {$player.realname}</div>
                {/if}
                
                <div>Country: {$player.country}</div>
                
                <div>Location: {$player.location}</div>
                
                {if $player.age}
                    <div>Age: {$player.age}</div>
                {/if}
                
                {if $player.gender}
                    <div>Gender: {$player.gender}</div>
                {/if}
                
                <div>Interests:
                    {if $player.interests}<div>{$player.interests}</div>{/if}
                </div>
                
                {if $player.website}
                    <div>Website: <a href="{$player.website}" target="_blank">{$player.website}</a></div>
                {/if}
            </div>
        {/if}
        
        {if $is_self || $can_contact}
            <div>
                <h2>Contact Info</h2>
                
                {if $is_self}
                    <div class="edit_link"><a href="/editprofile?tab=contact">Edit</a></div>{* //todo: Change this *}
                {/if}
                
                {if !$is_self}
                    <div><a href="#">Send Private Message</a></div>
                {/if}
                
                {if $player.msn}
                    <div>MSN Messenger: {$player.msn}</div>
                {/if}
                
                {if $player.aim}
                    <div>AIM: {$player.aim}</div>
                {/if}
                
                {if $player.yahoo}
                    <div>Yahoo! Messenger: {$player.yahoo}</div>
                {/if}
                
                {if $player.icq}
                    <div>ICQ: {$player.icq}</div>
                {/if}
                
                {if $player.skype}
                    <div>Skype: {$player.skype}</div>
                {/if}
                
                {if $player.irc}
                    <div>IRC: <a href="{$player.irc}">{$player.irc_channel} @ {$player.irc_network}</a></div>
                {/if}
            </div>
        {/if}
    </div>
    
    <div id="teams">
        <h2>Teams</h2>
    </div>
    
    <div id="friends">
        <h2>Friends</h2>
        
        <ul>
            {foreach from=$player.friends item=friend}
                <li>
                    <a href="{$friend.profile_uri}">
                        <img src="{$friend.avatar_uri}" />
                        {$friend.displayname}
                    </a>
                </li>
            {/foreach}
        </ul>
    </div>
    
    <div id="groups">
        <h2>Groups</h2>
    </div>
</div>