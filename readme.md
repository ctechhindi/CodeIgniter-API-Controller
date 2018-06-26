# CodeIgniter API Controller v.1.1.0

[![GitHub package version](https://img.shields.io/badge/package-v1.1.0-blue.svg)](https://github.com/jeevan15498/CodeIgniter-API-Controller/releases/tag/v1.1.0)
[![GitHub stars](https://img.shields.io/github/stars/jeevan15498/CodeIgniter-API-Controller.svg?style=flat-square)](https://github.com/jeevan15498/CodeIgniter-API-Controller/stargazers)
[![GitHub issues](https://img.shields.io/github/issues/jeevan15498/CodeIgniter-API-Controller.svg?style=flat-square)](https://github.com/jeevan15498/CodeIgniter-API-Controller/issues)
[![GitHub forks](https://img.shields.io/github/forks/jeevan15498/CodeIgniter-API-Controller.svg?style=flat-square)](https://github.com/jeevan15498/CodeIgniter-API-Controller/network)
[![GitHub license](https://img.shields.io/github/license/mashape/apistatus.svg?style=flat-square)](https://github.com/jeevan15498/CodeIgniter-API-Controller)


[![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](https://github.com/jeevan15498/CodeIgniter-API-Controller/issues)
[![HitCount](http://hits.dwyl.io/jeean15498/CodeIgniter-API-Controller.svg)](https://github.com/jeevan15498/CodeIgniter-API-Controller)
[![Github All Releases](https://img.shields.io/github/downloads/atom/atom/total.svg)](https://github.com/jeevan15498/CodeIgniter-API-Controller/archive/master.zip)

## Files

[API Documentation](https://github.com/jeevan15498/CodeIgniter-API-Controller#documentation)

* `\application\libraries\API_Controller.php`
* `\application\helpers\api_helper.php`
* `\application\config\api.php`

[Token Documentation](token.md)

* `\application\libraries\Authorization_Token.php`
* `\application\config\jwt.php`
* [PHP-JWT](https://github.com/firebase/php-jwt) Library `\application\third_party\php-jwt\`


## Requirements

1. PHP 5.4 or greater
2. CodeIgniter 3.0+

Note: The library is used in CodeIgniter v3.8 and PHP 5.6.8.

## DEMO

Simple API

```php
header("Access-Control-Allow-Origin: *");

// API Configuration
$this->_apiConfig([
    /**
     * By Default Request Method `GET`
     */
    'methods' => ['POST'], // 'GET', 'OPTIONS'

    /**
     * Number limit, type limit, time limit (last minute)
     */
    'limit' => [5, 'ip', 'everyday'],

    /**
     * type :: ['header', 'get', 'post']
     * key  :: ['table : Check Key in Database', 'key']
     */
    'key' => ['POST', 'string_key' ], // type, {key}|table (by default)
]);

// return data
$this->api_return(
    [
        'status' => true,
        "result" => "Return API Response",
    ],
200);
```

## Documentation

* This Function by default request method `GET`

```php
$this->_APIConfig();
```

* Set `API Request` Method `POST, GET, ..`

```php
$this->_APIConfig([
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

$this->_APIConfig([
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

$this->_APIConfig([
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

$this->_APIConfig([
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

$this->_APIConfig([
    // type, {key}|table (by default)
    'key' => ['header'], 
]);
```

* Use custom function in api key

```php
$this->_APIConfig([
    'key' => ['POST', $this->key() ],
]);

// Custom function
private function key()
{
    return 1452;
}
```

* Add Return Data in API Responses : __array()__

```php
$this->_APIConfig([
    'data' => [ 'is_login' => false ]
]);
```

```json
{
    "status": false,
    "error": "API Key POST Parameter Required",
    // data user define data
    "is_login": false
}
```

## API Return

```php
$this->api_return(data, header_code);
```

### Header List

| Header Code| Header Text |
| ---------- | ----------- |
| 200 | OK |
| 401 | UNAUTHORIZED |
| 404 | NOT FOUND |
| 408 | Request Timeout |
| 400 | BAD REQUEST |
| 405 | Method Not Allowed |

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


## Using the Config file in the API key

1. Create config file `\application\config\api_keys.php`
2. Use in API Controller like this

```php
// load api keys config file
$this->load->config('api_keys');

$this->_APIConfig([
    'key' => ['post', $this->config->item('controller/api key name')],
]);
```

## Reporting Issues

If you have a problem with this plugin or found any bug, please open an issue on [GitHub](https://github.com/jeevan15498/CodeIgniter-API-Controller/issues).

# License
CodeIgniter API Controller is licensed under [MIT](http://www.opensource.org/licenses/MIT)