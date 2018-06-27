<?php

/**
 * category API.
 *
 * Provides category informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Category.php';
$api = new Api('json', ['GET', 'POST', 'PUT']);
switch ($api->method) {
    case 'GET':
        //returns the operation
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        $category = new Category();
        $categories = array_values($category->getCategories(true));
        $api->output(200, $categories);
        //return categories list
        return;
        break;
    case 'POST':
        //post operation for adding category
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        $category = new Category();
        $requestedCategory = $api->query['body'];
        if (!$category->validateModel($requestedCategory, $errorMessage)) {
            $api->output(400, 'Category is not valid: '.$errorMessage);
            //provided category is not valid
            return;
        }
        //create category
        $category->status = true;
        if (!$category->insert()) {
            $api->output(500, 'Error during creation');
            //something gone wrong :(
            return;
        }
        $api->output(201, $category);
        break;
    case 'PUT':
       //put operation for updating category label, status or parent
       if (!$api->checkAuth()) {
           //User not authentified/authorized
           return;
       }
       $category = new Category();
       $requestedCategory = $api->query['body'];
       if (!$category->validateModel($requestedCategory, $errorMessage)) {
           $api->output(400, 'Category is not valid: '.$errorMessage);
           //provided category is not valid
           return;
       }
       //update category
       $category->status = true;
       if (!$category->update($errorMessage)) {
           $api->output(500, 'Error during update: '.$errorMessage);
           //something gone wrong :(
           return;
       }
       $api->output(200, $category);
       break;
}
