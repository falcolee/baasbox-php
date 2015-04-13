<?php

class baasbox{

    public $table;			// table name
	/**
	 * Init baasbox conf
	 * @param string $var
	 * @return object
	 */
	function __get($var) {
		switch ($var) {
			case 'box':
				return $this->load_baasbox();
            case 'box_conf':
                return $this->box_conf = &$_ENV['_config']['baasbox'];
		}
	}

	/**
	 * magic method
	 * @param string $method
	 */
	function __call($method, $args) {
		throw new Exception("$method is not existed");
	}

	/**
	 * load baasbox instance
	 * @return object
	 */
	public function load_baasbox() {
        $baasbox = Baasbox\Client::getInstance();
        if(isset($baasbox)) {
            return $baasbox;
        }else{
            return Baasbox\Client::configure(array(
			  'app_id' => 1234567890,
			  'endpoint' => 'http://www.test.com:9000/',
			  'authorization'=>'test:123456'	
			));
        }
	}

    public function find($key) {
        return $this->box->collection($this->table)->find($key);
    }

    public function create($data=array()) {
        return $this->box->collection($this->table)->create($data);
    }

    public function get(){
        return $this->box->collection($this->table)->get();
    }

    public function where($field, $_operation = null, $_value = null, $opera = 'and'){
        return $this->box->collection($this->table)->where($field, $_operation, $_value, $opera);
    }

    public function orWhere($field, $_operation = null, $_value = null){
        return $this->box->collection($this->table)->where($field, $_operation, $_value, 'or');
    }

    public function count() {
        return $this->box->collection($this->table)->count();
    }

    public function orderBy($field, $direction = null){
        return $this->box->collection($this->table)->orderBy($field, $direction = null);
    }

    public function recordsPerPage($int){
        return $this->box->collection($this->table)->recordsPerPage($int);
    }

    public function page($int){
        return $this->box->collection($this->table)->page($int);
    }

    public function skip($int){
        return $this->box->collection($this->table)->skip($int);
    }

    public function delete($_id = null){
        return $this->box->collection($this->table)->delete($_id);
    }

    public function update($_id, array $data = null){
        return $this->box->collection($this->table)->update($_id,$data);
    }

}
