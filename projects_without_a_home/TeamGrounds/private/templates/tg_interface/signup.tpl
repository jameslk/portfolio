<form method="post" action="{$uri}">
    {form_action do='SignUp'}
    
    <table>
        <tr>
            <td>Player Name</td>
            <td><input type="text" name="displayname" /></td>
        </tr>
        <tr>
            <td>Email Address</td>
            <td><input type="text" name="email" /></td>
        </tr>
        <tr>
            <td>New Password</td>
            <td><input type="password" name="password" /></td>
        </tr>
    </table>
    
    <input type="submit" name="submit" value="Sign Up" />
</form>