<div id="wrapper">
    <h1>MumbleAdmin</h1>

    <div id="containerHolder">
        <div id="container">
            <p>&nbsp;</p>

            <h2>Log In</h2>

            <div id="main">
                {if isset($form_errors) && !empty($form_errors)}
                    <div class="error">
                        {foreach from=$form_errors item=error}
                            <p>{$error}</p>
                        {/foreach}
                    </div>
                {/if}

                <form method="post" action="{$form_action}">
                    <input type="hidden" name="login" value="true" />
                    
                    <fieldset>
                        <p>
                            <label>Username:</label>
                            <input type="text" class="text-long" name="username"
                                value="{$username}" />
                        </p>

                        <p>
                            <label>Password:</label>
                            <input type="password" class="text-long" name="password"
                                value="{$password}" />
                        </p>
                        
                        <p>
                            <label>
                                <input type="checkbox" name="persistent"
                                    {if $persistent}checked="checked"{/if} />
                                Remember me
                            </label>
                        </p>
                        
                        <input type="submit" value="Log In" />
                    </fieldset>
                </form>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</div>