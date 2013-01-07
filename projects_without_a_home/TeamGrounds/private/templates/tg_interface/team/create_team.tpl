{css_file path='misc/autocomplete.css'}
{js_file path='libs/thirdparty/jquery.autocomplete.min.js'}

{js_code}
var ajax_game_uri = '{{action_url ajax='GamesList'}}';

var ajax_store_recruits_uri = '{{action_url ajax='StoreRecruits'}}';
var ajax_fetch_recruits_uri = '{{action_url ajax='FetchRecruits'}}';
{/js_code}

{js_file path='controllers/tg_interface/team/create_team.js'}

{if $session_user}
    {$subs.steps}
    
    {if !$step || $step == 'team_info'}
        <div id="{$step}">
            <h2>{$step_title}</h2>
            
            <form method="post" action="{$uri}">
                {form_action do='CreateTeam'}
                
                <table>
                    <tr>
                    <td>Team Name</td>
                    <td><input type="text" name="name" value="{$name}" /></td>
                    </tr>
                    
                    <tr>
                    <td>In-Game Name Tag</td>
                    <td><input type="text" name="game_tag" value="{$game_tag}" /></td>
                    </tr>
                    
                    <tr>
                    <td>Team Type</td>
                    <td>{html_radios name='team_type' options=$team_type_options selected=$team_type}</td>
                    </tr>
                    
                    <tr>
                    <td>Profile URL</td>
                    <td>{$CFG_URL}/team/<input type="text" name="profile_key" value="{$profile_key}" /></td>
                    </tr>
                </table>
                
                <input type="submit" name="submit" value="Create New Team" />
            </form>
        </div>
    {elseif $step == 'add_games'}
        {if !empty($add_games)}
            {js_code}
                $(function() {
                    create_team.AddGames({{$add_games|@json}});
                });
            {/js_code}
        {/if}
        
        <div id="{$step}">
            <h2>{$step_title}</h2>
            
            <form method="post" action="{$uri}">
                {form_action do='AddGames'}
                
                <ul id="add_games_list">
                    <li id="new_game_clone" style="display: none">
                        <input type="text" name="games[]" />
                        <a class="up" href="javascript:void(0);">Up</a> /
                        <a class="down" href="javascript:void(0);">Down</a> /
                        <a class="delete" href="javascript:void(0);">Delete</a>
                    </li>
                    
                    <li id="new_game_grayed">
                        <a class="add" href="javascript:void(0);">Add another game</a>
                    </li>
                </ul>
            
                <input type="submit" name="submit" value="Save Games" />
            </form>
            
            <div class="skip">
                <a href="{custom_url query='step=recruit'}">Skip this step</a>
            </div>
        </div>
    {elseif $step == 'recruit'}
        {if $add_recruits}
            {js_code}
                $(function() {
                    create_team.AddRecruits({{$add_recruits}});
                });
            {/js_code}
        {/if}
        
        <div id="{$step}">
            <h2>{$step_title}</h2>
            
            <div>
                <h3>Selected Recruits</h3>
                <p>
                    Search for players on the right and click
                    <b>Recruit Player</b>
                    to add them to the list of selected recruits for your
                    team. When you're finished simply press
                    <b>Recruit Members</b>.
                </p>
                
                <form method="post" action="{$uri}">
                    {form_action do='AddRecruits'}
                    
                    <ul id="add_recruits_list">
                        <li id="new_recruit_clone" style="display: none">
                            <input type="hidden" name="new_recruits[]" value="" />
                            
                            <span class="avatar"><img src="" /></span>
                            <span class="name"></span>
                            
                            <br />
                            
                            <a class="up" href="javascript:void(0);">Up</a> /
                            <a class="down" href="javascript:void(0);">Down</a> /
                            <a class="remove" href="javascript:void(0);">Remove</a>
                        </li>
                    </ul>
                
                    <input id="recruit_submit" type="submit" name="submit" value="Recruit Members" disabled="disabled" />
                </form>
            </div>
                
            <div>
                {$subs.user_search}
            </div>
            
            <div class="skip">
                <a href="{$team->GetProfileURI()}">Skip this step</a>
            </div>
        </div>
    {/if}
{else}
    Please login to view this page.
{/if}