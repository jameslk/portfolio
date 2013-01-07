$(function() {
    $('#search_options_tabs').tabs();
})

var user_search = {
    RemoveResult: function(user_id) {
        $('#user_search_player_' + user_id).slideUp('fast', function() {
            $(this).remove();
        });
    },
    
    ShowResult: function(user_id) {
        $('#user_search_player_' + user_id).slideDown('fast');
    },
    
    HideResult: function(user_id) {
        $('#user_search_player_' + user_id).slideUp('fast');
    },
}