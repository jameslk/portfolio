<?php

/**
 * Email sender wrapper class.
 */

class SendMail {
    var $to_list = array();
    
    var $cc_list = array();
    var $bcc_list = array();
    
    var $from = '';
    
    var $subject = '';
    
    var $is_html = false;
    var $body = '';
    
    /**
     * @brief Removes any potentially hazardous text from the specified field.
     * @param string $field
     * @param bool $ignore_crlf = false
     */
    function _SafetyCheck(&$field, $ignore_crlf = false) {
        if($field != NULL) {
            if(!$ignore_crlf)
                $bad = "/(((Content-Type)|(Bcc)|(Cc)|(To)|(From)):)|\\r\\n/i";
            else
                $bad = "/((Content-Type)|(Bcc)|(Cc)|(To)|(From)):/i";
            
            $field = preg_replace($bad, '', $field);
        }
    }
    
    /**
     * @brief Add a email address to the list of recievers.
     * @param string $address
     * @param string $name (default NULL)
     */
    function AddTo($address, $name = NULL) {
        $this->_SafetyCheck($address);
        $this->_SafetyCheck($name);
        
        $this->to_list[] = array('name' => $name, 'address' => $address);
    }
    
    /**
     * @brief Remove all "To" recipients of this email.
     */
    function ClearTo() {
        $this->to_list = array();
    }
    
    /**
     * @brief Add a CC address.
     * @param string $address
     * @param string $name (default NULL)
     */
    function AddCC($address, $name = NULL) {
        $this->_SafetyCheck($address);
        $this->_SafetyCheck($name);
        
        $this->cc_list[] = array('name' => $name, 'address' => $address);
    }
    
    /**
     * @brief Remove all "CC" recipients of this email.
     */
    function ClearCC() {
        $this->cc_list = array();
    }
    
    /**
     * @brief Add a BCC address.
     * @param string $address
     * @param string $name (default NULL)
     */
    function AddBCC($address, $name = NULL) {
        $this->_SafetyCheck($address);
        $this->_SafetyCheck($name);
        
        $this->bcc_list[] = array('name' => $name, 'address' => $address);
    }
    
    /**
     * @brief Remove all "BCC" recipients of this email.
     */
    function ClearBCC() {
        $this->bcc_list = array();
    }
    
    /**
     * @brief Set the sender of this email.
     * @param string $address
     * @param string $name (default NULL)
     */
    function From($address, $name = NULL) {
        $this->_SafetyCheck($address);
        $this->_SafetyCheck($name);
        
        if($name == NULL)
            $this->from = $address;
        else
            $this->from = $name.' <'.$address.'>';
    }
    
    /**
     * @brief Set the subject of this email.
     * @param string $subject
     */
    function Subject($subject) {
        $this->_SafetyCheck($subject);
        
        $this->subject = $subject;
    }
    
    function IsHTML($flag) {
        if($flag == true)
            $this->is_html = true;
        else
            $this->is_html = false;
    }
    
    /**
     * @brief Set the body text of this email.
     * @param string $body
     */
    function Body($body) {
        $this->_SafetyCheck($body, true);
        
        $this->body = $body;
    }
    
    /**
     * @brief Send this email.
     * @return bool
     */
    function Send() {
        $to = '';
        
        foreach($this->to_list as $i => $email) {
            if($i)
                $to .= ', ';
            
            $to .= $email['name'].' <'.$email['address'].'>';
        }
        
        $headers = '';
        
        if(strlen($this->from))
            $headers = 'From: '.$this->from."\r\n";
        
        if(count($this->cc_list)) {
            $headers .= 'Cc: ';
            
            foreach($this->cc_list as $i => $email) {
                if($i)
                    $headers .= ', ';
                
                $headers .= $email['name'].' <'.$email['address'].'>';
            }
            
            $headers .= "\r\n";
        }
        
        if(count($this->bcc_list)) {
            $headers .= 'Bcc: ';
            
            foreach($this->bcc_list as $i => $email) {
                if($i)
                    $headers .= ', ';
                
                $headers .= $email['name'].' <'.$email['address'].'>';
            }
            
            $headers .= "\r\n";
        }
        
        if($this->is_html)
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        
        return @mail($to, $this->subject, $this->body, $headers);
    }
}