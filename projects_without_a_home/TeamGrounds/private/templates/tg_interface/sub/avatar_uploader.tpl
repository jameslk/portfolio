<div>
    Avatar:
    <img src="{$avatar_uri.normal}" />
    <img src="{$avatar_uri.medium}" />
    <img src="{$avatar_uri.small}" />
</div>

<form method="post" enctype="multipart/form-data" action="{$uri}">
    {form_action do='Save'}
    
    <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
    <input name="avatar" type="file" />
    <input type="submit" value="Upload" />
</form> 