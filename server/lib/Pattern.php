<?php
/**
 * Pattern definition.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Pattern
{
    /**
     * @var int User owner of pattern
     */
    public $user;

    /**
     * @var int Pattern identifier
     */
    public $id;

    /**
     * @var string Pattern matching to transaction label
     */
    public $label;

    /**
     * @var int Pattern category to set
     */
    public $category;

    /**
     * @var int Pattern subcategory to set
     */
    public $subcategory;

    /**
     * @var boolean Indicate if pattern is reccuring or single shot
     */
    public $isRecurring;

    public function __construct($userId = null, $id = null)
    {
        if ($userId !== null) {
            $this->user = intval($userId);
        }
        if ($id !== null) {
            $this->id = $id;
        }
    }

    /**
     * Populate pattern
     *
     * @return bool True if the pattern object is read
     */
    public function get()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `pattern` WHERE `user` =:user AND `id` =:id;');
        $query->bindValue(':user', $this->user, PDO::PARAM_INT);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        if ($query->execute()) {
            $query->setFetchMode(PDO::FETCH_INTO, $this);
            //return true if there is pattern fetched, false otherwise
            return (bool) $query->fetch();
        }
        //return false to indicate an error occurred while reading the pattern
        return false;
    }

    /**
     * Return all patterns.
     *
     * @param string $userId Owner of patterns
     *
     * @return array Pattern
     */
    public static function getAll($userId)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `pattern` WHERE `user` =:user;');
        $query->bindValue(':user', $userId, PDO::PARAM_INT);
        if ($query->execute()) {
            //return array of patterns
            return $query->fetchAll(PDO::FETCH_CLASS, 'Pattern');
        }
        return [];
    }

    /**
     * Return structured pattern.
     *
     * @return object A public version of pattern
     */
    public function structureData()
    {
        //create pattern structure
        $pattern = $this;
        $pattern->id = (int) $pattern->id;
        if (isset($pattern->category)) {
            $pattern->category = (int) $pattern->category;
        }
        if (isset($pattern->subcategory)) {
            $pattern->subcategory= (int) $pattern->subcategory;
        }
        if (isset($pattern->isRecurring)) {
            $pattern->isRecurring = (bool) $pattern->isRecurring;
        }
        unset($this->user);
        //return structured pattern
        return $pattern;
    }

    /**
     * Inserts a pattern in database.
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
        $query = $connection->prepare('INSERT INTO `pattern` (`user`, `label`, `category`, `subcategory`, `isRecurring`) VALUES ( :user, :label, :category, :subcategory, :isRecurring );');
        $query->bindValue(':user', $this->user, PDO::PARAM_INT);
        $query->bindValue(':label', $this->label, PDO::PARAM_STR);
        $query->bindValue(':category', $this->category, PDO::PARAM_INT);
        $query->bindValue(':subcategory', $this->subcategory, PDO::PARAM_INT);
        $query->bindValue(':isRecurring', $this->isRecurring, PDO::PARAM_BOOL);
        if ($query->execute()) {
            $this->id = $connection->lastInsertId();
            //returns insertion was successfully processed
            return true;
        }
        $error = $query->errorInfo()[2];
        //returns insertion has encountered an error
        return false;
    }

    /**
     * Validate a pattern object with provided informations.
     *
     * @param object $pattern Pattern object to validate
     * @param string $error The returned error message
     *
     * @return bool True if the pattern object provided is correct
     */
    public function validateModel($pattern, &$error)
    {
        $error = '';
        if ($pattern === null) {
            $error = 'invalid resource';
            //return false and detailed error message
            return false;
        }
        //iterate on each object attributes to set object
        foreach ($this as $key => $value) {
            if (property_exists($pattern, $key)) {
                //get provided attribute
                $this->$key = $pattern->$key;
            }
        }
        //check mandatory attributes
        if (!is_string($this->label) || $this->label === '') {
            $error = 'string must be provided in label attribute';
            //return false and detailed error message
            return false;
        }
        $this->category = null;
        if (isset($pattern->category) && $pattern->category !== '') {
            $this->category = intval($pattern->category);
        }
        $this->subcategory = null;
        if (isset($pattern->subcategory) && $pattern->subcategory !== '') {
            $this->subcategory = intval($pattern->subcategory);
        }
        //Pattern is valid
        return true;
    }

    /**
     * Update a pattern in database.
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
        $query = $connection->prepare('UPDATE `pattern` SET `label`=:label, `category`=:category, `subcategory`=:subcategory, `isRecurring`=:isRecurring WHERE `user`=:user AND `id`=:id;');
        $query->bindValue(':user', $this->user, PDO::PARAM_INT);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->bindValue(':label', $this->label, PDO::PARAM_STR);
        $query->bindValue(':category', $this->category, PDO::PARAM_INT);
        $query->bindValue(':subcategory', $this->subcategory, PDO::PARAM_INT);
        $query->bindValue(':isRecurring', $this->isRecurring, PDO::PARAM_BOOL);

        if ($query->execute()) {
            //returns udpate was successfully processed
            return true;
        }
        $error = $query->errorInfo()[2];
        //returns update has encountered an error
        return false;
    }

    /**
     * Delete a pattern in database.
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
        $query = $connection->prepare('DELETE FROM `pattern` WHERE `user`=:user AND `id`=:id;');
        $query->bindValue(':user', $this->user, PDO::PARAM_INT);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        if ($query->execute()) {
            //returns udpate was successfully processed
            return true;
        }
        $error = $query->errorInfo()[2];
        //returns update has encountered an error
        return false;
    }

    /**
     * Update patterns category following an update on subcategory parent id
     *
     * @param string $oldCategory Previous patterns category
     * @param string $newCategory New patterns category
     * @param string $subcategory patterns subcategory
     * @param string $error The returned error message
     *
     * @return bool True on success or false on failure
     */
    public static function updateFollowingParentSubategoryChange($oldCategory, $newCategory, $subcategory, &$error)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('UPDATE `pattern` SET `category`=:newCategory WHERE `category`=:oldCategory AND `subcategory`=:subcategory;');
        $query->bindValue(':oldCategory', $oldCategory, PDO::PARAM_INT);
        $query->bindValue(':newCategory', $newCategory, PDO::PARAM_INT);
        $query->bindValue(':subcategory', $subcategory, PDO::PARAM_INT);
        if ($query->execute()) {
            //returns udpate was successfully processed
            return true;
        }
        $error = $query->errorInfo()[2];
        //returns update has encountered an error
        return false;
    }
}
