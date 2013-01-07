{if $session_user}
    Signed in as {$session_user->GetSafe('displayname')} | <a href="{action_url do='Logout'}">Logout</a>
{else}
    <form method="post" action="{$uri}">
        {form_action do='Login'}
        
        <label>Email <input type="text" name="email" /></label>
        <label>Password <input type="password" name="password" /></label>
        
        <label><input type="checkbox" name="remember" /> Remember Me</label>
        
        <input type="submit" name="submit" value="Login" />
    </form>
{/if}