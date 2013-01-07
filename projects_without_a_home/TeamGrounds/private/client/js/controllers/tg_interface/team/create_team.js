var create_team = {
    AddNewGame_last_id: 0,
    AddNewGame: function(title) {
        if($('.new_game').size() >= 15)
            return;
        
        var id = 'new_game' + (++this.AddNewGame_last_id);
        
        $('#new_game_clone').clone().attr({'id': id, 'class': 'new_game'})
            .css('display', '').hide().insertBefore('#new_game_grayed')
            .slideDown('fast', function() {
                if(title instanceof String)
                    $('#' + id + ' input').attr('value', title).autocomplete(ajax_game_uri);
                else
                    $('#' + id + ' input').autocomplete(ajax_game_uri).focus();
            })
    },
    
    AddGames: function(games) {
        $('.new_game').remove();
        
        $.each(games, function() {
            create_team.AddNewGame(this);
        });
    },
    
    DeleteGame: function() {
        if($('.new_game').size() > 1) {
            $(this).parent('.new_game').slideUp('fast', function() {
                $(this).remove();
            });
        }
    },
    
    MoveGameUp: function() {
        var parent = $(this).parent('.new_game');
        
        parent.prev('.new_game:first').insertAfter(parent);
    },
    
    MoveGameDown: function() {
        var parent = $(this).parent('.new_game');
        
        parent.next('.new_game:first').insertBefore(parent);
    },
    
    recruits_tracker: {},
    
    AddRecruit: function(user_id, name, avatar_uri) {
        if(this.recruits_tracker[user_id])
            return; //already added
        else
            this.recruits_tracker[user_id] = true;
        
        user_search.HideResult(user_id);
        
        var id = 'new_recruit' + user_id;
        
        var new_recruit = $('#new_recruit_clone').clone()
            .attr({'id': id, 'class': 'new_recruit'}).css('display', '')
            .data('user_data', {
                'user_id': user_id,
                'name': name,
                'avatar_uri': avatar_uri
            });
        
        new_recruit.find('input').attr('value', user_id);
        new_recruit.find('.avatar img').attr('src', avatar_uri);
        new_recruit.find('.name').text(name);
        
        new_recruit.hide().appendTo('#add_recruits_list').slideDown('fast');
        
        $('#recruit_submit').removeAttr('disabled');
    },
    
    RemoveRecruit: function() {
        var recruit = $(this).parent('.new_recruit');
        var user_id = recruit.data('user_data').user_id;
        
        user_search.ShowResult(user_id);
        
        recruit.slideUp('fast', function() {
            $(this).remove();
            
            if(!$('.new_recruit').size())
                $('#recruit_submit').attr('disabled', 'disabled');
        });
    },
    
    MoveRecruitUp: function() {
        var parent = $(this).parent('.new_recruit');
        
        parent.prev('.new_recruit:first').insertAfter(parent);
    },
    
    MoveRecruitDown: function() {
        var parent = $(this).parent('.new_recruit');
        
        parent.next('.new_recruit:first').insertBefore(parent);
    },
    
    StoreRecruits_is_stored: false,
    StoreRecruits: function() {
        if(!create_team.StoreRecruits_is_stored) {
            create_team.StoreRecruits_is_stored = true;
            
            var recruits_store = new Array();
            $('.new_recruit').each(function() {
                recruits_store.push($(this).data('user_data'));
            });
            
            $.get(ajax_store_recruits_uri,
                {'recruits_store': JSON.stringify(recruits_store)},
                function() {
                    /* Submit form when finished loading */
                    $('form.user_search').submit();
                });
            
            return false;
        }
        else {
            create_team.StoreRecruits_is_stored = false;
        }
    },
    
    AddRecruits: function(recruits) {
        $.each(recruits, function() {
            if(this.user_id != undefined)
                create_team.AddRecruit(this.user_id, this.name, this.avatar_uri);
        });
    },
}

$(function() {
    $('#new_game_grayed a.add').click(create_team.AddNewGame);
    
    $('.new_game .delete').live('click', create_team.DeleteGame);
    $('.new_game .up').live('click', create_team.MoveGameUp);
    $('.new_game .down').live('click', create_team.MoveGameDown);
    
    $('.new_recruit .remove').live('click', create_team.RemoveRecruit);
    $('.new_recruit .up').live('click', create_team.MoveRecruitUp);
    $('.new_recruit .down').live('click', create_team.MoveRecruitDown);
    
    $('form.user_search').submit(create_team.StoreRecruits);
    
    create_team.AddNewGame();
});