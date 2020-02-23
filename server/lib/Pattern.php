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

    /**
     * @var Array Users shares dispatch
     */
    public $shares;

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
        $query = $connection->prepare('SELECT `pattern`.*, IFNULL(`share`, 100) AS `share` FROM `pattern` LEFT JOIN `pattern_user_dispatch` ON `userShare` = :user AND `pattern_user_dispatch`.`user` = `pattern`.`user` AND `pattern_user_dispatch`.`id` = `pattern`.`id` WHERE `pattern`.`user` =:user AND `pattern`.`id` =:id;');
        $query->bindValue(':user', $this->user, PDO::PARAM_INT);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->execute() && $query->fetch()) {
            $this->getShares();
            return true;
            //return true if there is pattern fetched, false otherwise
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
        $query = $connection->prepare('SELECT `pattern`.*, IFNULL(`share`, 100) AS `share` FROM `pattern` LEFT JOIN `pattern_user_dispatch` ON `userShare` = :user AND `pattern_user_dispatch`.`user` = `pattern`.`user` AND `pattern_user_dispatch`.`id` = `pattern`.`id` WHERE `pattern`.`user` =:user;');
        $query->bindValue(':user', $userId, PDO::PARAM_INT);
        if ($query->execute()) {
            $patterns = $query->fetchAll(PDO::FETCH_CLASS, 'Pattern');
            foreach ($patterns as $pattern) {
                $pattern->getShares();
            }
            //return array of patterns
            return $patterns;
        }
        return [];
    }

    /**
     * Populate pattern shares dispatch
     *
     * @return bool True if the pattern object is read
     */
    private function getShares()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `userShare` AS user, `share` FROM `pattern_user_dispatch` WHERE `user` = :user and `id` = :id;');
        $query->bindValue(':user', $this->user, PDO::PARAM_INT);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $this->shares = [];
        if ($query->execute()) {
            $this->shares = $query->fetchAll(PDO::FETCH_OBJ);
            foreach ($this->shares as &$share) {
                $share->user = (int) $share->user;
                $share->share = (int) $share->share;
            }
            //returns the shares was successfully fetched
            return true;
        }
        //returns an error occurs when fetching shares
        return false;
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
        if (isset($pattern->share)) {
            $pattern->share = (int) $pattern->share;
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
            $this->id = (int) $connection->lastInsertId();
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
        if ($this->shares) {
            if (array_sum(array_column($this->shares, 'share')) !== 100) {
                $error = 'total dispatch must be equal to 100%';
                //return false and detailed error message
                return false;
            }
            $shareUsers = array_column($this->shares, 'user');
            if (count(array_unique($shareUsers)) !== count($shareUsers)) {
                $error = 'there is duplication in dispatch';
                //return false and detailed error message
                return false;
            }
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
     * Update pattern shares dispatch with provided informations.
     *
     * @param int $userId User identifier to set pattern share
     * @param string $error The returned error message
     *
     * @return bool True if shares are updated
     */
    public function updateShares($userId, &$error)
    {
        $error = '';
        if (!is_int($this->id)) {
            //return false to indicate operation is not done
            return false;
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('DELETE FROM `pattern_user_dispatch` WHERE `user` = :user and `id` = :id;');
        $query->bindValue(':user', $this->user, PDO::PARAM_INT);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        if (!$query->execute()) {
            $error = $query->errorInfo()[2];
            //return false to indicate an error occurs during operation
            return false;
        }
        $query = $connection->prepare('INSERT INTO `pattern_user_dispatch` (`user`, `id`, `userShare`, `share`) VALUES (:user, :id, :userShare, :share);');
        foreach ($this->shares as $share) {
            if (!is_null($share->user) && !is_null($share->share)) {
                $query->bindValue(':user', $this->user, PDO::PARAM_INT);
                $query->bindValue(':id', $this->id, PDO::PARAM_INT);
                $query->bindValue(':userShare', $share->user, PDO::PARAM_INT);
                $query->bindValue(':share', $share->share, PDO::PARAM_INT);
                if (!$query->execute()) {
                    $error = $query->errorInfo()[2];
                    //return false to indicate an error occurs during operation
                    return false;
                }
                if ($userId === $share->user) {
                    //this is the requester share, update pattern
                    $this->share = $share->share;
                }
            }
        }
        //return true to indicate operation is successful
        return true;
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
