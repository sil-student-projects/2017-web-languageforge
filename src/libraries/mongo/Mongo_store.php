<?php

class MongoStore
{
	/**
	 * @var MongoDB[]
	 */
	private static $_pool = array();
	

	/**
	 * @var Mongo
	 */
	private static $_mongo;
	
	/**
	 * @param string $databaseName
	 * @return MongoDB
	 */
	public static function connect($databaseName) {
		if (!isset(static::$_pool[$databaseName])) {
			static::$_pool[$databaseName] = static::connectMongo($databaseName);
		}
		return static::$_pool[$databaseName];
	}
	
	/**
	 * @param string $databaseName
	 * @return MongoDB
	 */
	private static function connectMongo($databaseName) {
		if (static::$_mongo == null) {
			static::$_mongo = new Mongo();
		}
		return static::$_mongo->selectDB($databaseName);
	}
	
}

class MapperModel /*extends CI_Model*/
{
	protected static $_mapper;

	public static function init($mapper)
	{
		static::$_mapper = $mapper;
	}
	
	function __construct($id = NULL)
	{
		if (!empty($id))
		{
			static::$_mapper->read($this, $id);
		}
	}

	function read()
	{
		return static::$_mapper->read($this);
	}
	
	/**
	 * @return string The unique id of the object written
	 */
	function write()
	{
		return static::$_mapper->write($this);
	}
	
	public static function remove($id)
	{
		return static::$_mapper->remove($id);
	}

}

class MapperListModel /*extends CI_Model*/
{
	/**
	 * @var int
	 */
	public $count;
	
	/**
	 * @var array
	 */
	public $entries;
	
	protected static $_mapper;

	/**
	 * @var array
	 */
	protected $_query;

	/**
	 * @var array
	 */
	protected $_fields;
	
	public static function init($mapper)
	{
		static::$_mapper = $mapper;
	}
	
	function __construct($query, $fields = array())
	{
		$this->_query = $query;
		$this->_fields = $fields;
	}

	function read()
	{
		return static::$_mapper->readList($this, $this->_query, $this->_fields);
	}
	
	public static function remove($id)
	{
		return static::$_mapper->remove($id);
	}

}

class MongoMapper
{
	/**
	 * @var MongoDB
	 */
	protected $_db;

	/**
	 * @var MongoCollection
	 */
	protected $_collection;
	
	/**
	 * @var string
	 */
	private $_idKey;

	/**
	 * @param string $database
	 * @param string $collection
	 * @param string $idKey defaults to id
	 */
	public function __construct($database, $collection, $idKey = 'id')
	{
		$this->_db = MongoStore::connect($database);
		$this->_collection = $this->_db->$collection;
		$this->_idKey = $idKey;
	}
	
	public function readList($model, $query, $fields = array())
	{
		$cursor = $this->_collection->find($query, $fields);
		$model->count = $cursor->count();
		$model->entries = array();
		foreach ($cursor as $item) {
			$id = strval($item['_id']);
			$item[$this->_idKey] = $id;
			unset($item['_id']);
			$model->entries[] = $item;
		}
	}
	
	/**
	 * @param User_model $model
	 * @param MongoId $id
	 */
	public function read($model, $id)
	{
		assert(is_string($id) && !empty($id));
		$data = $this->_collection->findOne(array("_id" => new MongoId($id)));
		if ($data === NULL)
		{
			throw new Exception("Could not find id '$id'");
		}
		$this->decode($model, $data);
	}
	
	public function write($model)
	{
		$data = $this->encode($model);
		return $this->update($this->_collection, $data, $model->id);
	}

	/**
	 * Sets the public properties of $model to values from $values[propertyName]
	 * @param object $model
	 * @param array $values
	 */
	public function decode($model, $values)
	{
		$properties = get_object_vars($model);
		$idKey = $this->_idKey;
		// Map the Mongo _id to the property $idKey
		if (array_key_exists($idKey, $properties))
		{
			$model->$idKey = strval($values['_id']);
			unset($properties[$idKey]);
		}
		foreach ($properties as $key => $value)
		{
			if (!array_key_exists($key, $values))
			{
				// oops // TODO Add to list, throw at end CP 2013-06
				continue;
			}
			$model->$key = $values[$key];
		}
	}

	/**
	 * Sets key/values in the array to the public properties of $model
	 * @param object $model
	 * @return array
	 */
	public function encode($model)
	{
		$data = array();
		$properties = get_object_vars($model);
		$idKey = $this->_idKey;
		// We don't want the 'idKey' in the data so remove that from the properties
		if (array_key_exists($idKey, $properties))
		{
			unset($properties[$idKey]);
		}
		foreach ($properties as $key => $value)
		{
			$data[$key] = $value;
		}
		return $data;
	}

	public function remove($id)
	{
		assert(is_string($id) && !empty($id));
		$result = $this->_collection->remove(
			array('_id' => new MongoId($id)),
			array('safe' => true)
		);
		return $result;
	}
	
	/**
	 *
	 * @param MongoCollection $collection
	 * @param array $data
	 * @param MongoId $id
	 * @return MongoId
	 */
	protected function update($collection, $data, $id)
	{
		if (!$id) {
			$id = NULL;
		}
		assert($id === NULL || is_string($id));
		$result = $collection->update(
				array('_id' => new MongoId($id)),
				array('$set' => $data),
				array('upsert' => true, 'multiple' => false, 'safe' => true)
		);
		return isset($result['upserted']) ? $result['upserted'].$id : $id;
	}

}

?>