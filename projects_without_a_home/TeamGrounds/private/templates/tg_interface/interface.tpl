<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>{$title}</title>

{js_file path='libs/thirdparty/json2.js' priority='high'}

{js_file path='libs/thirdparty/jquery-1.3.2.min.js' priority='high'}

{css_file path='jquery/jquery-ui-1.7.1.custom.css' priority='high'}
{js_file path='libs/thirdparty/jquery-ui-1.7.1.custom.min.js' priority='high'}

{js_file path='libs/thirdparty/jquery.hint.js' priority='high'}

{js_file path='controllers/tg_interface/interface.js' priority='high'}

{css_file path='global.css' priority='high'}

{head_extra}

</head>

<body>
<div id="container">
    <div id="header">
        <h1>{$title}</h1>
    </div>
    
    <div id="nav">
        <ul>
        </ul>
    </div>
    
    <div id="login">
        {$subs.user_login}
    </div>
    
    <div id="content">
        {$messages}
        
        {$content}
    </div>
</div>
</body>

</html>