{if $session_user}
    {$subs.tabs}
    
    {if $tab == 'summary'}
        <div id="edit_{$tab}">
            <h2>{$tab_title}</h2>
            
            <form method="post" action="{$uri}">
                {form_action do='Save_Summary'}
                
                <textarea name="summary">{$summary|clean_data}</textarea>
                
                <div class="save">
                    <input type="submit" name="submit" value="Save {$tab_title}" /> or
                    <a href="{$session_user->GetProfileURI()}">Cancel</a>
                </div>
            </form>
        </div>
    {elseif $tab == 'gamexp'}
        <div id="edit_{$tab}">
            <h2>{$tab_title}</h2>
            
            {if $ugame_id}
                <form method="post" action="{$uri}">
                    {form_action do='Save_Gamexp'}
                    
                    {if isset($gamexp_data) && is_array($gamexp_data)}
                        <table>
                            {foreach from=$gamexp_data key=id item=gamexp}
                                <tr>
                                <td>Field Title</td>
                                <td>
                                    <input type="text" name="gamexp_data[{$id}][title]"
                                        value="{$gamexp_data[$id].title}" />
                                </td>
                                </tr>
                                
                                <tr>
                                <td>Content</td>
                                <td>
                                    <textarea name="gamexp_data[{$id}][content]">{$gamexp_data[$id].content|clean_data}</textarea>
                                </td>
                                </tr>
                                
                                <tr>
                                <td>Options</td>
                                <td>
                                    <a href="{action_url do='Delete_Gamexp' query="gamexp_id=`$id`"}">Delete</a> -
                                    <a href="{action_url do='MoveGamexpUp' query="gamexp_id=`$id`"}">
                                    Move Up</a>/<a href="{action_url do='MoveGamexpDown' query="gamexp_id=`$id`"}">Move Down</a>
                                </td>
                                </tr>
                            {/foreach}
                        </table>
                    {/if}
                    
                    <h3>New Fields</h3>
                    
                    {* //todo: Replace with AJAX *}
                    <table>
                    <tr>
                    <td>Field Title</td>
                    <td>
                        <input type="text" name="new[0][title]" value="{$new[0].title}" />
                        or
                        <select name="new[0][title_predef]">
                            <option label="-- Predefined Titles --" value="">-- Predefined Titles --</option>
                            {html_options options=$predefined_titles selected=`$new[0].title_predef`}
                        </select>
                    </td>
                    </tr>
                    
                    <tr>
                    <td>Content</td>
                    <td>
                        <textarea name="new[0][content]">{$new[0].content|clean_data}</textarea>
                    </td>
                    </tr>
                    
                    {section name=newfields_loop start=1 loop=`$smarty.request.newfields+1`}
                        <tr>
                        <td>Field Title</td>
                        <td>
                            <input type="text" name="new[{$smarty.section.newfields_loop.index}][title]"
                                value="{$new[$smarty.section.newfields_loop.index].title}" />
                            or
                            <select name="new[{$smarty.section.newfields_loop.index}][title_predef]">
                                <option label="-- Predefined Titles --" value="">-- Predefined Titles --</option>
                                {html_options options=$predefined_titles selected=`$new[$smarty.section.newfields_loop.index].title_predef`}
                            </select>
                        </td>
                        </tr>
                        
                        <tr>
                        <td>Content</td>
                        <td>
                            <textarea name="new[{$smarty.section.newfields_loop.index}][content]">{$new[$smarty.section.newfields_loop.index].content|clean_data}</textarea>
                        </td>
                        </tr>
                    {/section}
                    
                    </table>

                    
                    <a href="{custom_url query="newfields=`$smarty.request.newfields+1`"}">Add another field</a>
                    
                    <div class="save">
                        <input type="submit" name="submit" value="Save {$tab_title}" /> or
                        <a href="{$session_user->GetProfileURI()}">Cancel</a>
                    </div>
                </form>
            {else}
                {if $user_games}
                    Please select the game you want to edit your experience for.
                    
                    <ul class="game_list">
                        {foreach from=$user_games item=game}
                            <li><a href="{custom_url query="ugame_id=`$game.ugame_id`"}">{$game.title}</a></li>
                        {/foreach}
                    </ul>
                {else}
                    You need to add games to your profile first. Click
                    <a href="/settings?tab=games">here</a> to {* //todo: Change this *}
                    go to your game manager.
                {/if}
            {/if}
        </div>
    {elseif $tab == 'personal'}
        <div id="edit_{$tab}">
            <h2>{$tab_title}</h2>
            
            <form method="post" action="{$uri}">
                {form_action do='Save_PersonalInfo'}
                
                <table>
                    <tr>
                    <td>Country</td>
                    <td>
                        {html_options name='country' options=$country_options selected=$country}
                    </td>
                    </tr>
                    
                    <tr>
                    <td>Location</td>
                    <td><input type="text" name="location" value="{$location}" /></td>
                    </tr>
                    
                    <tr>
                    <td>Age</td>
                    <td>
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
                        
                        <div>
                            Show age?
                            <br />
                            {html_radios name='show_age' options=$show_age_options selected=$show_age}
                        </div>
                    </td>
                    </tr>
                    
                    <tr>
                    <td>Gender</td>
                    <td>
                        {html_options name='gender' options=$gender_options selected=$gender}
                    </td>
                    </tr>
                    
                    <tr>
                    <td>Interests</td>
                    <td><input type="text" name="interests" value="{$interests}" /></td>
                    </tr>
                    
                    <tr>
                    <td>Personal Website</td>
                    <td><input type="text" name="website" value="{$website}" /></td>
                    </tr>
                </table>
                
                <div>
                    Who can view your personal info?
                    <br />
                    {html_checkboxes name='show_personal' options=$show_personal_options selected=$show_personal}
                </div>
                
                <div class="save">
                    <input type="submit" name="submit" value="Save {$tab_title}" /> or
                    <a href="{$session_user->GetProfileURI()}">Cancel</a>
                </div>
            </form>
        </div>
    {elseif $tab == 'contact'}
        <div id="edit_{$tab}">
            <h2>{$tab_title}</h2>
            
            <form method="post" action="{$uri}">
                {form_action do='Save_ContactInfo'}
                
                <table>
                    <tr>
                    <td>MSN Messenger</td>
                    <td><input type="text" name="msn" value="{$msn}" /></td>
                    </tr>
                    
                    <tr>
                    <td>AIM</td>
                    <td><input type="text" name="aim" value="{$aim}" /></td>
                    </tr>
                    
                    <tr>
                    <td>Yahoo! Messenger</td>
                    <td><input type="text" name="yahoo" value="{$yahoo}" /></td>
                    </tr>
                    
                    <tr>
                    <td>ICQ</td>
                    <td><input type="text" name="icq" value="{$icq}" /></td>
                    </tr>
                    
                    <tr>
                    <td>Skype</td>
                    <td><input type="text" name="skype" value="{$skype}" /></td>
                    </tr>
                    
                    <tr>
                    <td>IRC</td>
                    <td>
                        IRC Channel: <input type="text" name="irc_channel" value="{$irc_channel}" />
                        <br />
                        IRC Network (address): <input type="text" name="irc_network" value="{$irc_network}" />
                    </td>
                    </tr>
                </table>
                
                <div>
                    Who can view your contact info?
                    <br />
                    {html_checkboxes name='allow_contact' options=$allow_contact_options selected=$allow_contact}
                </div>
                
                <div class="save">
                    <input type="submit" name="submit" value="Save {$tab_title}" /> or
                    <a href="{$session_user->GetProfileURI()}">Cancel</a>
                </div>
            </form>
        </div>
    {else}
        Invalid tab selected.
    {/if}
{else}
    Please login to view this page.
{/if}