<?php
/**
 * Map definition for dataset processing.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Map
{
    /**
     * @var string Map code
     */
    public $code;

    /**
     * @var string Map label
     */
    public $label;

    /**
     * @var string Date format ('Y-m-d')
     */
    public $dateFormat;

    /**
     * @var Array Key is `Origin` item attribute, value is `Target` transaction attribute
     */
    public $map;

    public function __construct($code = null)
    {
        if ($code !== null) {
            $this->code = $code;
        }
    }

    /**
     * Populate map
     */
    public function get()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `map` WHERE `code` =:code;');
        $query->bindValue(':code', $this->code, PDO::PARAM_STR);
        if ($query->execute()) {
            $query->setFetchMode(PDO::FETCH_INTO, $this);
            //return true if there is map fetched, false otherwise
            return (bool) $query->fetch();
        }
        //return false to indicate an error occurred while reading the map
        return false;
    }

    /**
     * Return all maps.
     *
     * @return array Maps
     */
    public static function getAll()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `map`;');
        if ($query->execute()) {
            //return array of users
            return $query->fetchAll(PDO::FETCH_CLASS, 'Map');
        }
        return [];
    }

    /**
     * Return map attributes
     *
     * @return array Associative array of the attributes
     */
    public function getAttributes()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `origin`, `target` FROM `map_attribute` WHERE `code` =:code;');
        $query->bindValue(':code', $this->code, PDO::PARAM_STR);
        if ($query->execute()) {
            //return array of attributes
            return array_map('reset',$query->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP));
        }
        return [];
    }
}
