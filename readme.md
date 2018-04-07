# CodeIgniter API Controller v.1.0.0

## Files

* `\application\libraries\API_Controller.php`
* `\application\helpers\api_helper.php`
* `\application\config\api.php`

## Database

```sql
CREATE TABLE `database_name`.`api_limit` ( 
    `id` INT NOT NULL AUTO_INCREMENT ,  
    `user_id` INT NULL DEFAULT NULL ,  
    `uri` VARCHAR(200) NOT NULL ,  
    `class` VARCHAR(200) NOT NULL ,  
    `method` VARCHAR(200) NOT NULL ,  
    `ip_address` VARCHAR(50) NOT NULL ,  
    `time` TEXT NOT NULL ,    PRIMARY KEY  (`id`)
) ENGINE = InnoDB;
```

```sql
CREATE TABLE `database_name`.`api_keys` ( 
    `id` INT NOT NULL AUTO_INCREMENT ,  
    `api_key` VARCHAR(50) NOT NULL ,  
    `controller` VARCHAR(50) NOT NULL ,  
    `date_created` DATE NULL DEFAULT NULL ,  
    `date_modified` DATE NULL DEFAULT NULL ,    PRIMARY KEY  (`id`)
) ENGINE = InnoDB;
```

## Documentation

* This Function by default request method `GET`

```php
$this->_apiConfig();
```

* Set `API Request` Method `POST, GET, ..`

```php
$this->_apiConfig([
    'methods' => ['POST', 'GET'],
]);
```

* Set `API Limit` (By default request method `GET`)


```php
/**
 * API Limit
 * ----------------------------------
 * @param: {int} api limit number
 * @param: {string} api limit type (ip)
 * @param: {int} api limit time/minute (last {5} minute)
 */

$this->_apiConfig([
    // number limit, type limit, time limit (last minute)
    'limit' => [10, 'ip', 5] 
]);
```

```php
/**
 * API Limit
 * ----------------------------------
 * @param: {int} api limit number
 * @param: {string} api limit type (ip)
 * @param: {string} api limit everyday
 */

$this->_apiConfig([
    // number limit, type limit, everyday
    'limit' => [10, 'ip', 'everyday'] 
]);
```

* Set `API Key`
* Request `Content-Type` Header

1. application/json

```php
/**
 * API Key
 * ---------------------------------------------------------
 * @param: {string} type ['header', 'get', 'post']
 * @param: {string} ['table : Check Key in Database', 'key']
 */

$this->_apiConfig([
    // type, {key}|table (by default)
    'key' => ['header', 'key'],
    // 'key' => ['get', 'key'],
    // 'key' => ['post', 'key'],
    // 'key' => ['header', 'table'],
]);
```

```php
/**
 * API Key
 * ---------------------------------------------------------
 * @param: {string} type ['header', 'get', 'post']
 * @param: {string} ['table : Check Key in Database', 'key']
 */

$this->_apiConfig([
    // type, {key}|table (by default)
    'key' => ['header'], 
]);
```