<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
<h1>App Log</h1>

<p>URI: {$uri}</p>

<table border="0" width="100%">
    <tr style="background: #000000; color: #FFFFFF">
        <td>Time</td>
        <td>Report</td>
    </tr>
    
    {foreach from=$applog item=log}
    <tr style="background: {cycle values='#EEEEEE,#DDDDDD'}">
        <td>{$log.time}</td>
        <td style="padding: 10px">
            {$log.report}
            {if isset($log.info)}
            <p>
                <span style="font-style: italic">Additional Info:</span>
                <pre>{$log.info}</pre>
            </p>
            {/if}
        </td>
    </tr>
    {/foreach}
</table>
</body>

</html>