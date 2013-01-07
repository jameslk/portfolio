{if !isset($subpage) || !$subpage || $subpage == 'form'}
    <fieldset>
        <table>
            <tr>
                <td>Admin E-mail</td>
                <td><input name="admin_email" type="text" value="{$admin_email}" /></td>
            </tr>
            
            <tr>
                <td>Admin Password</td>
                <td><input name="admin_password" type="password" value="{$admin_password}" /></td>
            </tr>
            
            <tr>
                <td>Confirm Password</td>
                <td><input name="password_confirm" type="password" value="{$password_confirm}" /></td>
            </tr>
        </table>
    </fieldset>
{elseif $subpage == 'success'}
    <input name="success" type="hidden" value="true" />
    
    <p>
        Root admin user has been created. Press "Continue" to move on to the
        next step.
    </p>
{/if}