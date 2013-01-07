<link rel="stylesheet" type="text/css" href="http://demo.myclientbase.com/assets/style/css/superfish.css" media="screen" />

<div id="wrapper">
    <h1>mymumble</h1>

    <ul id="mainNav" class="sf-menu">
        <li><a href="/dashboard/{$billing_id}">Dashboard</a></li>
        <li><a href="/logout/{$billing_id}">Log Out</a></li>
    </ul>

    <div id="containerHolder">
        <div id="container">
            <div id="main">
                <h2>{$server.name} Server</h2>

                <table>
                    <tr>
                        <td width="30%">Status:</td>
                        <td width="70%">
                        {if $server.status}
                            Online (<a href="/dashboard/stop_server/{$billing_id}">stop server</a>)
                        {else}
                            Offline (<a href="/dashboard/start_server/{$billing_id}">start server</a>)
                        {/if}
                        </td>
                    </tr>
                    
                    <!--
                    <tr>
                        <td width="30%">Address:</td>
                        <td width="70%">http://???</td>
                    </tr>
                    -->
                    
                    <tr>
                        <td width="30%">Location:</td>
                        <td width="70%">{$server.location}</td>
                    </tr>
                    
                    <tr>
                        <td width="30%">Slots:</td>
                        <td width="70%">{$server.slots}</td>
                    </tr>
                    
                    <tr>
                        <td width="30%">Current Users:</td>
                        <td width="70%">{$server.users}</td>
                    </tr>
                    
                    <tr>
                        <td width="30%">Uptime:</td>
                        <td width="70%">{$server.uptime}</td>
                    </tr>
                </table>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</div>