<?php

/**
 * Category definition.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Category
{
    /**
     * @var int Category identifier
     */
    public $id;
    /**
     * @var string Label
     */
    public $label;
    /**
     * @var boolean Indicate if category is active
     */
    public $status;
    /**
     * @var string Category (only) icon
     */
    public $icon;
    /**
     * @var boolean Indicate if category (only) is included in budget
     */
    public $isBudgeted;
    /**
     * @var int Parent category (if is subcategory)
     */
    public $parentId;

    /**
     * Initializes a Category object with his identifier.
     *
     * @param int $id Category identifier
     * @param string $label Label
     */
    public function __construct($id = null, $label = null)
    {
        if ($id !== null) {
            $this->id = intval($id);
        }
        if ($label !== null) {
            $this->label = $label;
        }
    }

    /**
     * Inserts a category in database.
     *
     * @return bool True on success or false on failure
     */
    public function insert()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('INSERT INTO `category` (`label`, `status`, `icon`, `isBudgeted`, `parentId`) VALUES ( :label, :status, :icon, :isBudgeted, :parentId);');
        $query->bindValue(':label', $this->label, PDO::PARAM_STR);
        $query->bindValue(':status', $this->status, PDO::PARAM_BOOL);
        $query->bindValue(':icon', $this->icon, PDO::PARAM_STR);
        $query->bindValue(':isBudgeted', $this->isBudgeted, PDO::PARAM_BOOL);
        $query->bindValue(':parentId', $this->parentId, PDO::PARAM_INT);
        if ($query->execute()) {
            $this->id = $connection->lastInsertId();
            //returns insertion was successfully processed
            return true;
        }
        //returns insertion has encountered an error
        return false;
    }

    /**
     * Update a category in database.
     *
     * @param string $error The returned error message
     *
     * @return bool True on success or false on failure
     */
    public function update($error)
    {
        $error = '';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('UPDATE `category` SET `label`=:label, `status`=:status, `parentId`=:parentId, `icon`=:icon, `isBudgeted`=:isBudgeted WHERE `id`=:id;');
        $query->bindValue(':label', $this->label, PDO::PARAM_STR);
        $query->bindValue(':status', $this->status, PDO::PARAM_BOOL);
        $query->bindValue(':icon', $this->icon, PDO::PARAM_STR);
        $query->bindValue(':isBudgeted', $this->isBudgeted, PDO::PARAM_BOOL);
        $query->bindValue(':parentId', $this->parentId, PDO::PARAM_INT);
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
     * Populates a category.
     *
     * @return bool True on success or false on failure
     */
    public function get()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT * FROM `category` WHERE `id`=:id LIMIT 1;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->execute() && $query->fetch()) {
            $this->id = intval($this->id);
            //returns the category object was successfully fetched
            return true;
        }
        //returns the category is not known or database was not reachable
        return false;
    }

    /**
     * Return structured category.
     *
     * @return object A public version of category
     */
    public function structureData()
    {
        //create category structure
        $category = $this;
        if (isset($category->id)) {
            $category->id = (int) $category->id;
        }
        if (isset($category->status)) {
            $category->status = (bool) $category->status;
        }
        if (isset($category->parentId)) {
            $category->parentId = (int) $category->parentId;
        }
        if (isset($category->isBudgeted)) {
            $category->isBudgeted = (bool) $category->isBudgeted;
        }
        //return structured category
        return $category;
    }

    /**
     * Return all categories.
     *
     * @param bool $returnActive Indicates to return all categories including the deactivated ones
     *
     * @return array All categories
     */
    public function getCategories($returnActive)
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $queryString = 'SELECT * FROM `category` ORDER BY `parentId`, `id`;';
        if ($returnActive) {
            $queryString = 'SELECT * FROM `category` WHERE `status`=1 ORDER BY `parentId`, `id`;';
        }
        $query = $connection->prepare($queryString);
        if ($query->execute()) {
            $arr_category = $query->fetchAll(PDO::FETCH_CLASS, 'Category');
            $arr_categories = [];
            foreach ($arr_category as $category) {
                //remove status if the request is about only actives ones
                if ($returnActive) {
                    unset($category->status);
                }
                $category = $category->structureData();
                if (is_null($category->parentId)) {
                    unset($category->parentId);
                    $category->sub = [];
                    $arr_categories[$category->id] = $category;
                } else {
                    $category->parentId = (int) $category->parentId;
                    if (array_key_exists($category->parentId, $arr_categories)) {
                        array_push($arr_categories[$category->parentId]->sub, $category);
                    }
                }
            }
            //return array of categories
            return $arr_categories;
        }
        //indicate there is a problem during querying
        return false;
    }

    /**
     * Validate a category object with provided informations.
     *
     * @param object $category Category object to validate
     * @param string $error The returned error message
     *
     * @return bool True if the category object provided is correct
     */
    public function validateModel($category, &$error)
    {
        $error = '';
        if ($category=== null) {
            $error = 'invalid resource';
            //return false and detailed error message
            return false;
        }
        //iterate on each object attributes to set object
        foreach ($this as $key => $value) {
            if (property_exists($category, $key)) {
                //get provided attribute
                $this->$key = $category->$key;
            }
        }
        //check mandatory attributes
        if (!is_string($this->label) || $this->label=== '') {
            $error = 'string must be provided in label attribute';
            //return false and detailed error message
            return false;
        }
        //Category is valid
        return true;
    }
}
