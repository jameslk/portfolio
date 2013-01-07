<?php

Libs('controller/sub/tg', 'model/user', 'model/avatar', 'image_upload');

class SC_AvatarUploader extends SC_Tg {
    public $template_name = 'avatar_uploader.tpl';
    
    public $avatar;
    
    public $save_message = 'Avatar has been updated.';
    
    function __construct($parent, $avatar_parent) {
        parent::__construct($parent);
        
        $this->avatar = M_Avatar::GetByParent($avatar_parent);
        if(!$this->avatar) {
            $this->avatar = new M_Avatar;
            $this->avatar->SetParent($avatar_parent);
        }
    }
    
    protected function Controller_Post($params) {
        parent::Controller_Post($params);
        
        $this->template->assign('avatar_uri', array(
            'normal' => $this->avatar->GetPath(),
            'medium' => $this->avatar->GetPath('medium'),
            'small' => $this->avatar->GetPath('small')
        ));
    }
    
    protected function Action_Default() {
    }
    
    protected function Action_Save() {
        $upload = ImageUpload::Create('avatar', $error);
        if(!$upload) {
            $this->messages->Error('Avatar upload failed: '.$error);
            return;
        }
        
        if(!$this->avatar->HasID())
            $this->avatar->Create();
        
        $upload->ConvertToJPEG();
        
        //Normal size
        $upload->ResizeFitRatio(200, 200, true, 16, 16);
        $upload->Write(PUBLIC_DIR.$this->avatar->GetPath());
        
        //Medium size
        $upload->ResizeFitRatio(64, 64, true, 16, 16);
        $upload->Write(PUBLIC_DIR.$this->avatar->GetPath('medium'));
        
        //Small size
        $upload->ResizeFitRatio(32, 32, true, 16, 16);
        $upload->Write(PUBLIC_DIR.$this->avatar->GetPath('small'));
        
        $this->messages->Notice($this->save_message);
    }
}