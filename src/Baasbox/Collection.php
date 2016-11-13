<?php
namespace Baasbox;

class Collection {

	/**
	 * name
	 * @var string
	 */
	protected $name;

	/**
	 * segments
	 * @var string
	 */
	protected $segments;

	/**
	 * client
	 * @var \Baasbox\Client
	 */
	protected $client;

	/**
	 * wheres
	 * @var array
	 */
	protected $wheres;

	/**
	 * options
	 * @var array
	 */
	protected $options;

	/**
	 * ordering
	 * @var array
	 */
	protected $ordering;

	/**
	 * group
	 * @var array
	 */
	protected $group;

	/**
	 * $recordsPerPage
	 * @var int
	 */
	protected $recordsPerPage;

    /**
     * $page
     * @var int
     */
    protected $page;

	/**
	 * $skip
	 * @var int
	 */
	protected $skip;

	public function __construct(array $options) {
		$this->name = $options['name'];
		$this->client = isset($options['client']) ? $options['client'] : Client::getInstance();
		$this->segments = 'document/' . $this->name;
		$this->reset();
	}

	protected function reset() {
		$this->wheres = array();
		$this->options = array();
		$this->ordering = array();
		$this->group = array();
		$this->recordsPerPage = null;
		$this->skip = null;
        $this->page = null;
		return $this;
	}

	public function create(array $data) {
		return $this->client->post($this->segments, $data);
	}

    public function fullText($keyword){
        $value =  '%'.$keyword.'%';
        $this->addWhere('any()', 'like', $value,'and');
    }

	public function where($field, $_operation = null, $_value = null, $opera = 'and') {
		$operation = (is_null($_value)) ? "=" : $_operation;
		$value = (is_null($_value)) ? $_operation : $_value;

		if (is_array($field)) {
			foreach($field as $field => $value) {
				if (is_array($value)) {
					$operation = $value[0];
					$value = $value[1];
				}
				$this->addWhere($field, $operation, $value, $opera);
			}
		} else {
			$this->addWhere($field, $operation, $value, $opera);
		}

		return $this;
	}

	public function orWhere($field, $_operation = null, $_value = null) {
		return $this->where($field, $_operation, $_value, 'or');
	}

	public function get() {
		return $this->client->get($this->segments, $this->buildQuery());
	}

	public function find($_id) {
		return $this->client->get($this->segments . '/' . $_id, $this->buildQuery());
	}

	public function select() {
		$this->options['select'] = func_get_args();
		return $this;
	}

	public function with() {
		$this->options['with'] = func_get_args();
		return $this;
	}

	public function group() {
		$this->group = func_get_args();
		return $this;
	}

	public function count() {
        return $this->client->get($this->segments.'/count', $this->buildQuery());
	}


	public function orderBy($field, $direction = null) {
		if (is_null($direction)) {
			$direction = 'asc';
		} else if (is_int($direction)) {
			$direction = (intval($direction) === -1) ? 'desc' : 'asc';
		}
		$this->ordering[] = array($field, $direction);
		return $this;
	}

	public function recordsPerPage($int) {
		$this->recordsPerPage = $int;
		return $this;
	}

    public function page($int) {
        $this->page = $int;
        return $this;
    }

	public function skip($int) {
		$this->skip = $int;
		return $this;
	}

	public function channel($options) {
		throw Exception("Not implemented.");
	}

	public function delete($_id = null) {
		$path = $this->segments;
		if (!is_null($_id)) {
			$path .= '/' . $_id;
		}
		return $this->client->remove($path, $this->buildQuery());
	}

	public function update($_id, array $data = null) {
		return $this->client->put($this->segments . '/' . $_id, $data);
	}


	protected function addWhere($field, $operation, $value,$oprea) {
		$this->wheres[] = array($field, strtolower($operation), $value,$oprea);
		return $this;
	}

	protected function buildQuery() {
		$query = '';
        
		// apply prePage / skip
		if ($this->recordsPerPage !== null) { $query .='&recordsPerPage='.$this->recordsPerPage; }
		if ($this->skip !== null) { $query .='&skip='.$this->skip; }
        if ($this->page !== null) { $query .='&page='.$this->page; }
		// apply wheres
		if (count($this->wheres) > 0) {
            $params = '';
            $query .= '&where=';
            foreach ($this->wheres as $k=>$val) {
                if($k==0){
                    $query .= $val[0].$val[1].'? ';
                }else{
                    $query .= $val[3].' '.$val[0].$val[1].'? ';
                }
                $params .= '&params='.$val[2];
            }

            $query .= $params;
		}

		// apply ordering
		if (count($this->ordering) > 0) {
            $query .= '&orderBy=';
            $order = '';
            foreach ($this->ordering as $k=>$val) {
                if($k==0){
                    $query .= $val[0];
                    $order .= $val[1];
                }else{
                    $query .= ','.$val[0];
                    $order .= ','.$val[1];
                }
            }
            $query .=' '.$order;
		}

		// apply group
		if (count($this->group) > 0) {
			$query['g'] = $this->group;
		}

		// clear wheres/ordering for future calls
		$this->reset();
		return $query;
	}

}
