{if $session_user}
    {$subs.tabs}
    
    {if !$tab || $tab == 'account'}
        <div>
            <h2>Account Settings</h2>
            
            <form method="post" action="{$uri}">
                {form_action do='Save_AccountSettings'}
                
                <div>
                    <h3>Email Address</h3>
                    <input type="text" name="email" value="{$email}" />
                </div>
                
                <div>
                    <h3>Password</h3>
                    Old Password <input type="password" name="old_password" />
                    New Password <input type="password" name="password" />
                    Confirm <input type="password" name="password_confirm" />
                </div>
                
                <div>
                    <h3>Timezone</h3>
                    
                    <select name="timezone">
                        <option value="">-- Timezone --</option>
                        {html_options options=$timezones selected=$timezone}
                    </select>
                </div>
            
                <input type="submit" name="submit" value="Save">
            </form>
        </div>
        
        <div>
            <h2>Profile Settings</h2>
            
            <form method="post" action="{$uri}">
                {form_action do='Save_ProfileSettings'}
            
                <div>
                    <h3>Player Name</h3>
                    <input type="text" name="displayname" value="{$displayname|clean_data}" />
                </div>
                
                <div>
                    <h3>Profile URL</h3>
                    {$CFG_URL}/player/<input type="text" name="profile_key" value="{$profile_key}" />
                </div>
                
                <input type="submit" name="submit" value="Save">
            </form>
        </div>
        
        <div>
            <h2>Profile Avatar</h2>
            
            <div>
                {$subs.avatar_uploader}
            </div>
        </div>
        
        <div>
            <h2>Profile Information</h2>
            
            <form method="post" action="{$uri}">
                {form_action do='Save_ProfileInformation'}
            
                <div>
                    <h3>Birthday</h3>
                    
                    <select name="birthday_day">
                        <option value="">-- Day --</option>
                        {html_options options=$birthday_days selected=$birthday_day}
                    </select>
                    
                    <select name="birthday_month">
                        <option value="">-- Month --</option>
                        {html_options options=$birthday_months selected=$birthday_month}
                    </select>
                    
                    <select name="birthday_year">
                        <option value="">-- Year --</option>
                        {html_options options=$birthday_years selected=$birthday_year}
                    </select>
                </div>
            
                <input type="submit" name="submit" value="Save">
            </form>
        </div>
    {elseif $tab == 'games'}
        <div>
            <h2>Manage Games</h2>
            
            <div>
                {if !empty($user_games)}
                    <ul>
                        {foreach from=$user_games item=user_game}
                            <li>
                                <div>{$user_game.title}</div>
                                <div>
                                    <a href="{action_url do='DeleteGame' query="game_id=`$user_game.ugame_id`"}">Delete</a> -
                                    <a href="{action_url do='MoveGameUp' query="game_id=`$user_game.ugame_id`"}">
                                    Move Up</a>/<a href="{action_url do='MoveGameDown' query="game_id=`$user_game.ugame_id`"}">Move Down</a>
                                </div>
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    You have not added any games to your list yet.
                {/if}
            </div>
            
            <div>
                <h3>Add a Game</h3>
                
                <form method="post" action="{$url}">
                    {form_action do='AddGame'}
                    
                    <input type="text" name="game" value="{$game}" />
                    
                    <input type="submit" name="submit" value="Add" />
                </form>
            </div>
        </div>
    {else}
        Invalid tab selected.
    {/if}
{else}
    Please login to view this page.
{/if}