{if !isset($subpage) || !$subpage || $subpage == 'form'}
    <fieldset>
        <table>
            <tr>
                <td>Database Host Address</td>
                <td><input name="db_hostname" type="text" value="{$db_hostname}" /></td>
            </tr>
            
            <tr>
                <td>Database Port</td>
                <td><input name="db_port" type="text" value="{$db_port}" />
                If you don't know, leave this blank to use default settings.</td>
            </tr>
            
            <tr>
                <td>Database Name</td>
                <td><input name="db_name" type="text" value="{$db_name}" /></td>
            </tr>
            
            <tr>
                <td>Username</td>
                <td><input name="db_username" type="text" value="{$db_username}" /></td>
            </tr>
            
            <tr>
                <td>Password</td>
                <td><input name="db_password" type="text" value="{$db_password}" /></td>
            </tr>
            
            <tr>
                <td>Table Prefix</td>
                <td><input name="db_prefix" type="text" value="{$db_prefix}" /></td>
            </tr>
        </table>
    </fieldset>
{elseif $subpage == 'overwrite_tables'}
    <p>
        It appears some or all database tables have already been created. Do you
        want to overwrite these tables (this cannot be undone!) or skip over them
        and only create the non-existing ones?
    </p>
    
    <p>
        <input name="overwrite_tables" type="submit" value="Overwrite Tables" />
        (recommended)
    </p>
    
    <p>
        <input name="skip_tables" type="submit" value="Skip Existing Tables" />
    </p>
{elseif $subpage == 'drop_error'}
    <div class="error">
        <p>
            Failed to drop some or all existing database tables. Check database
            user's permissions and try again.
            <br />
            Database Error: {$db_error}
        </p>
    </div>
    
    <input name="try_drop" type="submit" value="Try Again" />
{elseif $subpage == 'create_error'}
    <div class="error">
        <p>
            Failed to create some or all database tables. Check database user's
            permissions and try again.
            <br />
            Database Error: {$db_error}
        </p>
    </div>
    
    <input name="try_create" type="submit" value="Try Again" />
{elseif $subpage == 'success'}
    <input name="success" type="hidden" value="true" />
    
    <p>
        All database tables have been successfully created. Press "Continue" to
        move on to the next step.
    </p>
{/if}