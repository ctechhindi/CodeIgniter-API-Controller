# API Authorization

## Files

* `\application\libraries\Authorization_Token.php`
* `\application\config\jwt.php`
* [PHP-JWT](https://github.com/firebase/php-jwt) Library `\application\third_party\php-jwt\`

## Require Authentication ?

Just add one more parameter to your API Configuration Array `requireAuthorization` like,

```php
// API Configuration
$this->_apiConfig([
    'methods' => ['POST'],
    'requireAuthorization' => true,
]);
```

If you want to pass the token in the header, use the following format. In which you can also change the name of the header by going to jwt config file.

```
Authorization: [token]
```
Now, the library will check in the form of header for the JWT token in the request And also change the token expiration time.

```php
// Token Request Header Name
$config['token_header'] = 'authorization';

// Token Expire Time [seconds]
$config['token_expire_time'] = 86400;  // 1 Day
```


## Generate a token

If you require the authentication in API, you first must grant the token to the user who is making the API request. In general, when a user logs in, the response should contain the token for all next requests.

```php
/**
 * login method 
 *
 * @link [api/user/login]
 * @method POST
 * @return Response|void
*/
public function login()
{
    header("Access-Control-Allow-Origin: *");

    // API Configuration
    $this->_apiConfig([
        'methods' => ['POST'],
    ]);

    // you user authentication code will go here, you can compare the user with the database or whatever
    $payload = [
        'id' => "Your User's ID",
        'other' => "Some other data"
    ];

    // Load Authorization Library or Load in autoload config file
    $this->load->library('authorization_token');

    // generte a token
    $token = $this->authorization_token->generateToken($payload);

    // return data
    $this->api_return(
        [
            'status' => true,
            "result" => [
                'token' => $token,
            ],
            
        ],
    200);
}
```

And it will return the token in response. So, in next API calls, a user can use that token for authorization.

By default, the plugin uses the predefined key and algorithm to generate JWT token. You can update this configuration by creating `config/jwt.php` file. The content of this configuration file will be as following,

```php
// JWT Secure Key
$config['jwt_key'] = 'TWvLUzI1NiJ9IiRkYXRhIg';

// JWT Algorithm Type
$config['jwt_algorithm'] = 'HS256';
```

## Access Token Data

If there is a valid token available in the request, then the `$this->_apiConfig()` method used by us returns the token data after decoding token, which you can use. like this,

```php
/**
 * view method
 *
 * @link [api/user/view]
 * @method POST
 * @return Response|void
 */
public function view()
{
    header("Access-Control-Allow-Origin: *");

    // API Configuration [Return Array: User Token Data]
    $user_data = $this->_apiConfig([
        'methods' => ['POST'],
        'requireAuthorization' => true,
    ]);

    // return data
    $this->api_return(
        [
            'status' => true,
            "result" => [
                'user_data' => $user_data['token_data']
            ],
        ],
    200);
}
```


## Reporting Issues

If you have a problem with this plugin or found any bug, please open an issue on [GitHub](https://github.com/jeevan15498/CodeIgniter-API-Controller/issues).