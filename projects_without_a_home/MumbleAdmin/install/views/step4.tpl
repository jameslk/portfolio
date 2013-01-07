{if !isset($subpage) || !$subpage || $subpage == 'form'}
    <fieldset>
        <table>
            <tr>
                <td>MumbleAdmin URL</td>
                <td><input name="site_url" type="text" value="{$site_url}"
                    style="width: 400px" /></td>
            </tr>
            
            <tr>
                <td>Default Murmur Ice Address</td>
                <td>
                    Hostname: <input name="murmur_ice_hostname" type="text" value="{$murmur_ice_hostname}" />
                    Port: <input name="murmur_ice_port" type="text" value="{$murmur_ice_port}" />
                </td>
            </tr>
        </table>
    </fieldset>
{elseif $subpage == 'success'}
    <input name="success" type="hidden" value="true" />
    
    <p>
        Configuration settings have been written. Setup is now completed. Press
        "continue" to proceed to the log-in page.
    </p>
{/if}