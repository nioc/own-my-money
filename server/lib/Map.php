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
    public $attributes;

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
        $query = $connection->prepare('SELECT `target`, `origin` FROM `map_attribute` WHERE `code` =:code;');
        $query->bindValue(':code', $this->code, PDO::PARAM_STR);
        if ($query->execute()) {
            //return array of attributes
            $this->attributes = array_map('reset', $query->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP));
            return $this->attributes;
        }
        return [];
    }

    /**
     * Validate a map object with provided informations.
     *
     * @param object $map Map object to validate
     * @param string $error The returned error message
     *
     * @return bool True if the map object provided is correct
     */
    public function validateModel($map, &$error)
    {
        $error = '';
        if ($map === null) {
            $error = 'invalid resource';
            //return false and detailed error message
            return false;
        }
        //iterate on each object attributes to set object
        foreach ($this as $key => $value) {
            if (property_exists($map, $key)) {
                //get provided attribute
                $this->$key = $map->$key;
            }
        }
        //check mandatory attributes
        if (!is_string($this->code) || $this->code === '') {
            $error = 'string must be provided in code attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->label) || $this->label === '') {
            $error = 'string must be provided in label attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->dateFormat) || $this->dateFormat === '') {
            $error = 'string must be provided in dateFormat attribute';
            //return false and detailed error message
            return false;
        }
        //Category is valid
        return true;
    }

    /**
     * Inserts a map in database.
     *
     * @param string $error The returned error message
     *
     * @return bool True on success or false on failure
     */
    public function insert(&$error)
    {
        $error = '';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('INSERT INTO `map` (`code`, `label`, `dateFormat`) VALUES ( :code, :label, :dateFormat );');
        $query->bindValue(':code', $this->code, PDO::PARAM_STR);
        $query->bindValue(':label', $this->label, PDO::PARAM_STR);
        $query->bindValue(':dateFormat', $this->dateFormat, PDO::PARAM_STR);
        if ($query->execute()) {
            //returns insertion was successfully processed
            return true;
        }
        $error = $query->errorInfo()[2];
        //try to return intelligible error
        if ($query->errorInfo()[1] === 1062 || $query->errorInfo()[2] === 'UNIQUE constraint failed: map.code') {
            $error = ' : code `'.$this->code.'` already exists';
        }
        //returns insertion has encountered an error
        return false;
    }

    /**
     * Inserts a map in database.
     *
     * @return bool True on success or false on failure
     */
    public function setAttributes()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('DELETE FROM `map_attribute` WHERE `code`=:code;');
        $query->bindValue(':code', $this->code, PDO::PARAM_STR);
        if (!$query->execute()) {
            //returns deletion has encountered an error
            return false;
        }
        foreach ($this->attributes as $target => $origin) {
            $query = $connection->prepare('INSERT INTO `map_attribute` (`code`, `target`, `origin`) VALUES ( :code, :target, :origin );');
            $query->bindValue(':code', $this->code, PDO::PARAM_STR);
            $query->bindValue(':target', $target, PDO::PARAM_STR);
            $query->bindValue(':origin', $origin, PDO::PARAM_STR);
            $query->execute();
        }
        //returns attributes were successfully inserted
        return true;
    }

    /**
     * Update a map in database.
     *
     * @param string $error The returned error message
     *
     * @return bool True on success or false on failure
     */
    public function update(&$error)
    {
        $error = '';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('UPDATE `map` SET `label`=:label, `dateFormat`=:dateFormat WHERE `code`=:code;');
        $query->bindValue(':code', $this->code, PDO::PARAM_STR);
        $query->bindValue(':label', $this->label, PDO::PARAM_STR);
        $query->bindValue(':dateFormat', $this->dateFormat, PDO::PARAM_STR);
        if ($query->execute()) {
            //returns udpate was successfully processed
            return true;
        }
        $error = $query->errorInfo()[2];
        //returns update has encountered an error
        return false;
    }

    /**
     * Delete a map in database.
     *
     * @param string $error The returned error message
     *
     * @return bool True on success or false on failure
     */
    public function delete(&$error)
    {
        $error = '';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('DELETE FROM `map` WHERE `code`=:code;');
        $query->bindValue(':code', $this->code, PDO::PARAM_STR);
        if ($query->execute()) {
            //returns udpate was successfully processed
            return true;
        }
        $error = $query->errorInfo()[2];
        //returns update has encountered an error
        return false;
    }
}
