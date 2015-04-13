<?php
namespace Baasbox;

class User {
	protected $client;

    /**
     * segments
     * @var string
     */
    protected $segments;

    protected $appcode;

	function __construct($client) {
		$this->client = $client;
        $this->segments = 'login';
	}

    public function login(array $user){
        $result = $this->client->login($this->segments,$user);
        if(isset($result['result'])){
            if($result['result']==='ok'){
                if(isset($result['data'])&&isset($result['data']['X-BB-SESSION'])){
                    $this->client->setSession($result['data']['X-BB-SESSION']);
                }
            }
        }
    }

    public function logout(){
        $this->client->post('logout',array());
    }

    public function profile($user='me'){
        $this->client->get('user/'.$user);
    }
}
