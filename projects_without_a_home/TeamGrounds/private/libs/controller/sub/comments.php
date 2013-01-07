<?php

Libs('controller/sub/tg', 'model/comment_thread', 'model/comment_post', 'pagination');

class SC_Comments extends SC_Tg {
    public $template_name = 'comments.tpl';

    public $comments_parent;

    function __construct($parent, $comments_parent) {
        parent::__construct($parent);

        $this->comments_parent = $comments_parent;
    }

    protected function Controller_Post($params) {
        parent::Controller_Post($params);

        $count = M_CommentThread::GetCountByParent($this->comments_parent);
        $max = CFG_COMMENTS_MAX_THREADS_PER_PAGE;

        $pagination = new Pagination($count, $max, 'comments_page');

        $limit = $pagination->SQL_Limit();

        $threads = M_CommentThread::GetArrayByParent($this->comments_parent, $limit);
        if($threads) {
            foreach($threads as $thread) {
                $thread->GetPosts();
                foreach($thread->posts as $post)
                    $post->GetUser();
            }

            $this->template->assign('threads', $threads);
            $this->template->assign('pagination', $pagination->MakePager($_SERVER['REQUEST_URI']));
        }

        if($this->session->IsActive())
            $this->template->assign('can_delete', $this->comments_parent->Id() == $this->session->user->Id());
    }

    protected function Action_NewThread($thread_comment) {
        $this->RequireSession();

        $post = new M_CommentPost;
        $post->SetField('user_id', $this->session->user->Id());

        if(!$post->SetField('content', $thread_comment)) {
            $this->messages->FieldError('thread_comment', 'Your comment was too short or invalid.');
            return;
        }

        $thread = new M_CommentThread;
        $thread->SetParent($this->comments_parent);
        $thread->NewThread($post);
    }

    protected function Action_NewReply($post_id, $reply_comment) {
        $this->RequireSession();

        $replyto_post = M_CommentPost::GetByID($post_id, array('thread_id'));
        if(!$replyto_post) {
            $this->messages->Error('Sorry, the post you were replying to does not exist anymore.');
            return;
        }

        $post = new M_CommentPost;
        $post->SetFields(array(
            'thread_id' => $replyto_post['thread_id'],
            'user_id' => $this->session->user->Id()
        ), $fail_data);

        if(!$post->SetField('content', $reply_comment)) {
            $this->messages->FieldError('thread_comment', 'Your comment was too short or invalid.');
            return;
        }

        $post->Create();

        //todo: Remove this redirect when AJAX support is added
        $this->Redirect(false, array('replyto'));
    }

    protected function Action_DeletePost($post_id) {
        $this->RequireSession();

        if($this->comments_parent->Id() != $this->session->user->Id()) {
            $this->messages->Error('You cannot delete other player comments');
            return;
        }

        $post = M_CommentPost::GetByID($post_id, array('thread_id', 'is_firstpost'));
        if($post) {
            //todo: Show notice telling user that the post has been deleted
            if((int)$post['is_firstpost']) {
                $thread = new M_CommentThread($post['thread_id']);
                $thread->Delete();
            }
            else {
                $post->Delete();
            }
        }

        $this->Redirect(false, array($this->action_var, 'post_id'));
    }
}