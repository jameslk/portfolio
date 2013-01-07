<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('thirdparty/smarty/libs/Smarty.class.php');

class Murmur {
    public $CI;
    
    public $address = '';
    
    public $link = NULL;
    public $meta;
    
    public function Murmur() {
        $this->CI =& get_instance();
        
        $this->CI->config->load('Murmur');
        $this->address = $this->CI->config->item('murmur_ice_address');
        
        if (!extension_loaded('ice'))
			make_error('Failed to initialize Murmur library: IcePHP extension is not loaded');
		
		try {
			Ice_loadProfile();
			if($this->address)
			     $this->connect();
		}
        catch(Ice_ProfileAlreadyLoadedException $exc) {
			make_warning('Ice profile has already been loaded');
		}
    }
    
    public function is_connected() {
        return $this->link != NULL;
    }
    
    public function connect($address = NULL) {
        global $ICE;
        
        if($address !== NULL)
            $this->address = $address;
        
		$this->link = $ICE->stringToProxy($this->address);
		
		$this->meta = $this->link->ice_checkedCast('::Murmur::Meta');
		$this->meta = $this->meta->ice_timeout(10000);
		
		return $this->link;
    }
    
    public function get_version() {
		$this->meta->getVersion($major, $minor, $patch, $text);
		return $major.'.'.$minor.'.'.$patch.' '.$text;
	}
	
	public function get_ice_servers() {
		return $this->meta->getAllServers();
	}
	
	public function get_ice_server($id) {
		return $this->meta->getServer(intval($id));
	}
	
	public function get_ice_user($id) {
		return $this->meta->getServer(intval($id));
	}
	
	public function get_server($id) {
        return MurmurServer::from_ice_id($this, $id);
    }
	
	public function create_server() {
		return new MurmurServer($this, $this->meta->newServer()->id());
	}
	
	public function get_default_config() {
		return $this->meta->getDefaultConf();
	}
}

abstract class MurmurBase {
    public $murmur;
    
    public $entity;
    
    protected function __construct(Murmur &$murmur, &$entity) {
        if(!$murmur->is_connected())
            $murmur->connect();
        
        $this->murmur =& $murmur;
        $this->entity =& $entity;
    }
    
    public static function from_ice_id(&$murmur, $id, $type = '') {
        $type = strtolower($type);
        
        $entity = call_user_method('get_ice_'.$type, $murmur, $id);
        
        $class = 'Murmur'.ucfirst($type);
        $murmur_obj = new $class($murmur, $entity);
        
        return $murmur_obj;
    }
    
    public static function from_ice_object(&$murmur, &$object) {
        $murmur_obj = new $class($murmur, $object);
        
        return $murmur_obj;
    }
}

class MurmurServer extends MurmurBase {
    public $server;
    
    protected function __construct(Murmur &$murmur, &$entity) {
        parent::__construct($murmur, $entity);
        
        $this->server =& $this->entity;
    }
    
    public static function from_ice_id(&$murmur, $id) {
        return parent::from_ice_id($murmur, $id, 'server');
    }
    
    public static function from_ice_object(&$murmur, $id) {
        return parent::from_ice_object($murmur, $id, 'server');
    }
    
    public function is_running() {
		return $this->server->isRunning();
	}
	
	public function start() {
		$this->server->start();
		
		return $this;
	}
	
	public function stop() {
		$this->server->stop();
		
		return $this;
	}
    
    public function delete() {
		if($this->is_running())
			$this->stop();
		
		$this->server->delete();
		
		return $this;
	}
	
	public function get_config() {
	    $default_config = $this->murmur->get_default_config();
		$config = $this->server->getAllConf();
		
		return array_merge($default_config, $config);
    }
	
	public function get_config_item($key) {
		return $this->server->getConf($key);
	}
	
	public function set_config_item($key, $value) {
		$this->server->setConf($key, strval($value));
		
		return $this;
	}
	
	public function set_superuser_password($password) {
        $this->server->setSuperuserPassword($password);
        
        return $this;
    }
    
    public function get_uptime() {
        if($this->is_running())
            return $this->server->getUptime();
        else
            return 0;
    }
    
    public function get_users() {
        if(!$this->is_running())
            return array();
        
		$user_map = $this->server->getUsers();
		
		$users = array();
		foreach($user_map as $session_id => $ice_obj)
			$users[] = MurmurUser::from_ice_object($this->murmur, $ice_obj);
		
		return $users;
	}
}

class MurmurUser extends MurmurBase {
    public $user;
    
    protected function __construct(Murmur &$murmur, &$entity) {
        parent::_construct($murmur, $entity);
        
        $this->user =& $this->entity;
    }
    
    public static function from_ice_id(&$murmur, $id) {
        return; //todo
    }
    
    public static function from_ice_object(&$murmur, $id) {
        return parent::from_ice_object($murmur, $id, 'user');
    }
}