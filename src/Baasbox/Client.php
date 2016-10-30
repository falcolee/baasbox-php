<?php
namespace Baasbox;

class Client {
	protected static $instance;

	protected $credentials;

	/**
	 * getInstance
	 * @return Baasbox\Client
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * configure
	 * @static
	 * @param array $credentials
	 */
	public static function configure($credentials = array()) {
		return self::$instance = new static($credentials);
	}

	/**
	 * __construct
	 * @param array $credentials
	 */
	function __construct($credentials = array()) {
		if (isset($credentials['url'])) { $credentials['endpoint'] = $credentials['url']; }
		// set default dl-api endpoint
		if (!isset($credentials['endpoint'])) {
			$credentials['endpoint'] = 'http://www.test.com:9000/';
		}

		$this->credentials = $credentials;
	}

	/**
	 * collection
	 * @param string $name
	 */
	public function collection($name) {
		return new Collection(array(
			'name' => $name,
			'client' => $this
		));
	}

    /**
     * user
     */
    public function user() {
        return new User($this);
    }

	/**
	 * get
	 * @param mixed $segments
	 * @param array $headers
	 */
	public function get($segments, $params = array(), $headers = array()) {
		return $this->request('get', $segments, $params, $headers);
	}

	/**
	 * remove
	 * @param mixed $segments
	 * @param array $headers
	 */
	public function remove($segments, $headers = array()) {
		return $this->request('delete', $segments, array(), $headers);
	}

	/**
	 * put
	 * @param mixed $segments
	 * @param array $data
	 * @param array $headers
	 */
	public function put($segments, $data = array(), $headers = array()) {
		return $this->request('put', $segments, $data, $headers);
	}

    /**
     * login
     * @param mixed $segments
     * @param array $data
     */
    public function login($segments, $data = array()){
        $body = null;
        if(!empty($data)){
            $body = 'username='.$data['username'].'&password='.$data['password'].'&appcode='.$this->credentials['app_id'];
        }
        $headers = array('Content-Type'=>'application/x-www-form-urlencoded');
        $client = new \GuzzleHttp\Client();
        return $client->post($this->credentials['endpoint'] . $segments, array(
            'headers' => $this->getHeaders($headers),
            'body' => $body,
            'exceptions' => false
        ))->json();
    }

	/**
	 * post
	 * @param mixed $segments
	 * @param array $data
	 * @param array $headers
	 */
	public function post($segments, $data = array(), $headers = array()) {
		return $this->request('post', $segments, $data, $headers);
	}

	protected function request($method, $segments, $data, $headers = array()) {
		$client = new \GuzzleHttp\Client();
		$method = strtoupper($method);
		$body = null;

		if ($method === "GET" && !empty($data)) {
			$segments .= '?' . $data;
		} elseif ($method !== "GET" && !empty($data)) {
			$body = json_encode($data);
		}

		$getHeaders = $this->getHeaders($headers);

		if ($method === "DELETE") {
		    $getHeaders['Content-Type'] = "application/x-www-form-urlencoded";
		}
		
		return $client->{$method}($this->credentials['endpoint'] . $segments, array(
			'headers' => $getHeaders,
			'body' => $body,
			'exceptions' => false
		))->json();
	}

    public function setSession($session){
        $this->credentials['session'] = $session;
    }

	protected function getHeaders($concat = array()) {
        $session = (isset($this->credentials['session']))?array('X-BB-SESSION'=>$this->credentials['session']):array();
        $authorization = (isset($this->credentials['authorization']))?array('Authorization'=>'Basic '.base64_encode($this->credentials['authorization'])):array();
		return array_merge(array(
			'Content-Type' => 'application/json',
			'X-BAASBOX-APPCODE' => $this->credentials['app_id'],
            'User-Agent' => 'baasbox-php'
		),$session,$authorization,$concat);
	}

}
