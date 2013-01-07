{js_file path='controllers/tg_interface/sub/user_search.js'}

<div id="user_search">
    <div class="search_options">
        <h3>Search Options</h3>
        
        <div id="search_options_tabs">
        	<ul>
        		<li><a href="#search_personal">Personal Search</a></li>
        		<li><a href="#search_contacts">Contact Info Search</a></li>
        		<li><a href="#gamexp_search">Game Experience Search</a></li>
        		<li><a href="#search_groups">Group Search</a></li>
        	</ul>
        	
        	<div id="search_personal">
        	   <h3>Personal Search</h3>
        	   
        	   <form method="get" action="{$uri}" class="user_search">
        	       {form_action do='Search_Personal' get='true'}
        	       
                    <table>
                        <tr>
                        <td>Player Name</td>
                        <td><input type="text" name="displayname" value="{$smarty.get.displayname}" /></td>
                        </tr>
                        
                        <tr>
                        <td>E-mail Address</td>
                        <td><input type="text" name="email" value="{$smarty.get.email}" /></td>
                        </tr>
                        
                        <tr>
                        <td>Real Name</td>
                        <td><input type="text" name="realname" value="{$smarty.get.realname}" /></td>
                        </tr>
                    </table>
        	       
        	       <input type="submit" value="Search" />
               </form>
            </div>
            
            <div id="search_contacts">
        	   <h3>Contact Info Search</h3>
            </div>
            
            <div id="gamexp_search">
                <h3>Game Experience Search</h3>
            </div>
            
            <div id="search_groups">
        	   <h3>Group Search</h3>
            </div>
        </div>
    </div>
    
    {if $has_searched}
        <div class="search_results">
            <h3>Search Results</h3>
            
            {if $users}
                {foreach from=$users item=user}
                    <div id="user_search_player_{$user->Id()}" class="player">
                        <div class="displayname">{$user.displayname}</div>
                        <div class="avatar"><img src="{$user->GetAvatarPath('medium')}" /></div>
                        
                        <div class="games">
                            {foreach from=$user->games item=game name=games}
                                {if !$smarty.foreach.games.last}
                                    {$game.title},
                                {else}
                                    {$game.title}
                                {/if}
                            {/foreach}
                        </div>
                        
                        <div class="actions">
                            {eval var=$search_actions}
                        </div>
                    </div>
                {/foreach}
            {else}
                No players were found matching your search options. Please try a different search.
            {/if}
        </div>
    {/if}
</div>