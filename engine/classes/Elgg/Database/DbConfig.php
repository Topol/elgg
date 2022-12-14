<?php

namespace Elgg\Database;

use Elgg\Config;

/**
 * Database configuration service
 *
 * @internal
 * @since 1.9.0
 */
class DbConfig {

	const READ = 'read';
	const WRITE = 'write';
	const READ_WRITE = 'readwrite';

	protected $db;
	protected $dbprefix;
	protected $dbhost;
	protected $dbport;
	protected $dbuser;
	protected $dbpass;
	protected $dbname;
	protected $dbencoding;

	/**
	 * Constructor
	 *
	 * @param \stdClass $config Object with keys:
	 *                          - db
	 *                          - dbprefix
	 *                          - dbhost
	 *                          - dbport
	 *                          - dbuser
	 *                          - dbpass
	 *                          - dbname
	 *                          - dbencoding
	 */
	public function __construct(\stdClass $config) {
		foreach (array_keys(get_class_vars(__CLASS__)) as $prop) {
			$this->{$prop} = isset($config->{$prop}) ? $config->{$prop} : null;
		}
	}

	/**
	 * Construct from an Elgg Config
	 *
	 * @param Config $config Elgg config
	 *
	 * @return DbConfig
	 */
	public static function fromElggConfig(Config $config) {
		$obj = new \stdClass();
		foreach (array_keys(get_class_vars(__CLASS__)) as $prop) {
			$obj->{$prop} = $config->{$prop};
		}
		return new self($obj);
	}

	/**
	 * Get the database table prefix
	 *
	 * @return string
	 */
	public function getTablePrefix() {
		return (string) $this->dbprefix;
	}

	/**
	 * Are the read and write connections separate?
	 *
	 * @return bool
	 */
	public function isDatabaseSplit() {
		return $this->db['split'] ?? false;
	}

	/**
	 * Get the connection configuration
	 *
	 * @note You must check isDatabaseSplit before using READ or WRITE for $type
	 *
	 * The parameters are in an array like this:
	 * array(
	 *	'host' => 'xxx',
	 *  'user' => 'xxx',
	 *  'password' => 'xxx',
	 *  'database' => 'xxx',
	 *  'encoding' => 'xxx',
	 *  'prefix' => 'xxx',
	 * )
	 *
	 * @param string $type The connection type: READ, WRITE, READ_WRITE
	 * @return array
	 */
	public function getConnectionConfig($type = self::READ_WRITE) {
		switch ($type) {
			case self::READ:
			case self::WRITE:
				$config = $this->getParticularConnectionConfig($type);
				break;
			default:
				$config = $this->getGeneralConnectionConfig();
				break;
		}

		$config['encoding'] = $this->dbencoding ? $this->dbencoding : 'utf8';
		$config['prefix'] = $this->dbprefix;

		return $config;
	}

	/**
	 * Get the read/write database connection information
	 *
	 * @return array
	 */
	protected function getGeneralConnectionConfig() {
		return [
			'host' => $this->dbhost,
			'port' => $this->dbport,
			'user' => $this->dbuser,
			'password' => $this->dbpass,
			'database' => $this->dbname,
		];
	}

	/**
	 * Get connection information for reading or writing
	 *
	 * @param string $type Connection type: 'write' or 'read'
	 * @return array
	 */
	protected function getParticularConnectionConfig($type) {
		if (array_key_exists('dbhost', $this->db[$type])) {
			// single connection
			$config = [
				'host' => $this->db[$type]['dbhost'],
				'port' => $this->db[$type]['dbport'],
				'user' => $this->db[$type]['dbuser'],
				'password' => $this->db[$type]['dbpass'],
				'database' => $this->db[$type]['dbname'],
			];
		} else {
			// new style multiple connections
			$index = array_rand($this->db[$type]);
			$config = [
				'host' => $this->db[$type][$index]['dbhost'],
				'port' => $this->db[$type][$index]['dbport'],
				'user' => $this->db[$type][$index]['dbuser'],
				'password' => $this->db[$type][$index]['dbpass'],
				'database' => $this->db[$type][$index]['dbname'],
			];
		}

		return $config;
	}
}
