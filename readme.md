# CodeIgniter API Controller v.1.1.5

## Files

[API Documentation](https://github.com/jeevan15498/CodeIgniter-API-Controller#documentation)

* `\application\libraries\API_Controller.php`
* `\application\helpers\api_helper.php`
* `\application\config\api.php`

[Token Documentation](token.md)

* `\application\libraries\Authorization_Token.php`
* `\application\config\jwt.php`
* [PHP-JWT](https://github.com/firebase/php-jwt) Library `\application\third_party\php-jwt\`

## Installation

You can install this project into your PC using [composer]().

The recommended way to install composer packages is:

```
composer require ctechhindi/codeigniter-api:dev-master --prefer-source
```

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

### Setup API Request Methods

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
---

### Use API Limit

Before using the limit in API, we need to load the codeigniter `database library`.
You can also load the database library in the autoload config `config/autoload.php` file.

After database library loaded, database must be set in database config file `config/database.php`.

After creating and setting up a database, we need to create a table for API Limit `[api_limit]` in the database. like this.

```sql
CREATE TABLE `api_limit` (
    `id` INT NOT NULL AUTO_INCREMENT ,  
    `user_id` INT NULL DEFAULT NULL ,  
    `uri` VARCHAR(200) NOT NULL ,  
    `class` VARCHAR(200) NOT NULL ,  
    `method` VARCHAR(200) NOT NULL ,  
    `ip_address` VARCHAR(50) NOT NULL ,  
    `time` TEXT NOT NULL ,    PRIMARY KEY  (`id`)
) ENGINE = InnoDB;
```

The name of the database table of api limit is `api_limit` by default. Which we can change through the API configuration file `[config/api.php]`. like this.

```php
/**
 * API Limit database table name
 */
$config['api_limit_table_name'] = 'api_limit';
```
Now we can use API Limit Method.

Thus this API can be run only 10 times in 5 minutes. It on `IP address`.

```php
/**
 * API Limit
 * ----------------------------------
 * @param: {int} API limit Number
 * @param: {string} API limit Type (IP)
 * @param: {int} API limit Time [minute]
 */

$this->_APIConfig([
    // number limit, type limit, time limit (last minute)
    'limit' => [10, 'ip', 5] 
]);
```

At API limit `time argument` we can also use `everyday` which will follow the api limit per day. On the same API address

```php
/**
 * API Limit
 * ----------------------------------
 * @param: {int} API limit Number
 * @param: {string} API limit Type (IP)
 * @param: {string} API limit [everyday]
 */

$this->_APIConfig([
    // number limit, type limit, everyday
    'limit' => [10, 'ip', 'everyday'] 
]);
```
---
### Use API Key without Database

We can set the API key in the Request Header. by default, the name of the header is `X-API-K`, which we can change in the API config file `[config/api.php]`. like this 

```php
/**
 * API Key Header Name
 */
$config['api_key_header_name'] = 'X-API-KEY';
```
Use this code in your API Controller file.

```php
/**
 * Use API Key without Database
 * ---------------------------------------------------------
 * @param: {string} Types
 * @param: {string} API Key
 */

$this->_APIConfig([
    'key' => ['header', 'Set API Key'],
]);
```
---

### Use API Key with Database

We can also check the API key by `databases`. For this, we need to first create `api_keys` table in MySQL. like this 

```sql
CREATE TABLE `api_keys` ( 
    `id` INT NOT NULL AUTO_INCREMENT ,  
    `api_key` VARCHAR(50) NOT NULL ,  
    `controller` VARCHAR(50) NOT NULL ,  
    `date_created` DATE NULL DEFAULT NULL ,  
    `date_modified` DATE NULL DEFAULT NULL ,    PRIMARY KEY  (`id`)
) ENGINE = InnoDB;
```

The name of the database table of API Keys is `api_keys` by default. Which we can change through the API configuration file `[config/api.php]`. like this.

```php
/**
 * API Keys Database Table Name 
 */
$config['api_keys_table_name'] = 'api_keys';
```

Set API keys in `api_keys` database table And Use this code in your API Controller file. 

```php
/**
 * API Key
 * ---------------------------------------------------------
 * @param: {string} Types
 * @param: {string} [table]
 */
$this->_APIConfig([
    // 'key' => ['header', 'table'],
    'key' => ['header'], 
]);
```
---

### Use Custom Function in API Key

```php
/**
 * API Key
 * ---------------------------------------------------------
 * @param: {string} Types
 * @param: [function] return api key
 */
$this->_APIConfig([
    'key' => ['header', $this->key() ],
]);

// This is Custom function and return api key
private function key() {
    return 1452;
}
```
---

### Add Custom Data in API Responses

In response to the API, we can also add custom data to something like this.


```php
$this->_APIConfig([
    'key' => ['header'],
    'data' => [ 'is_login' => false ] // custom data
]);
```

API Output :: 

```json
{
    "status": false,
    "error": "API Key Header Required",
    "is_login": false
}
```
---

### API Return Data

This method is used to return data to api in which the response data is `first` and the `second` request status code.

```php
/**
 * Return API Response
 * ---------------------------------------------------------
 * @param: API Data
 * @param: Request Status Code
 */
$this->api_return(data, status_code);
```

#### Request Status Code List

| Status Code| Status Text |
| ---------- | ----------- |
| 200 | OK |
| 401 | UNAUTHORIZED |
| 404 | NOT FOUND |
| 408 | Request Timeout |
| 400 | BAD REQUEST |
| 405 | Method Not Allowed |


## Using the Config file in the API key

1. Create config file `\application\config\api_keys.php`
2. Use in API Controller like this

```php
// load API Keys config file
$this->load->config('api_keys');

$this->_APIConfig([
    'key' => ['post', $this->config->item('controller/api key name')],
]);
```

## Reporting Issues

If you have a problem with this plugin or found any bug, please open an issue on [GitHub](https://github.com/jeevan15498/CodeIgniter-API-Controller/issues).

# License
CodeIgniter API Controller is licensed under [MIT](http://www.opensource.org/licenses/MIT)