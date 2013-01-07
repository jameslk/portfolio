<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>{$title}</title>

<link href="/client/css/layout.css" rel="stylesheet" type="text/css" media="screen" />

{$head_extra}

</head>
<body>

<div id="wrapper">
    <h1>MumbleAdmin Installation</h1>

    <div id="containerHolder">
        <div id="container">
            <h2>Step {$step} of {$total_steps} - {$step_title}</h2>

            <div id="main">
                {if isset($form_errors) && !empty($form_errors)}
                    <div class="error">
                        {foreach from=$form_errors item=error}
                            <p>{$error}</p>
                        {/foreach}
                    </div>
                {/if}
                
                <form method="post" action="{$form_action}">
                    <input name="install_key" type="hidden" value="{$install_key}" />
                    
                    {$content}
                    
                    {if $can_continue}
                        <input name="continue" type="submit" value="Continue" />
                    {/if}
                </form>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</div>

</body>
</html>