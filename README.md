# Amper PHP Framework
Amper is a PHP micro-framework for extremely fast prototyping and building REST API.

### Installation

To install via composer use:
```
composer require naglfar/amper:dev-master
```

Create index.php file in your project's root directory:
```php
define('GLOBAL_DIR', __DIR__);
require_once(GLOBAL_DIR.'/config/bootstrapper.php');
```
In order to use queues install predis library and pm2:
```
composer require predis/predis
npm i -g pm2
```
composer.json example:
```json
{
    "require": {
        "naglfar/amper": "dev-master",
        "predis/predis": "^1.1"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```
Configurate your project according to the structure below:


### General structure

- App
  - Controllers
    - ExampleController.php
  - Dispatchers
    - ExampleDispatcher.php
  - Entities
    - ExampleEntity.php
  - Middleware
    - ExampleMiddleware.php
  - Repositories
    - ExampleRepo.php
  - Routes.php
 - config
   - bootstrapper.php
   - cache.php
   - database.php
   - queue.php
- index.php
- migrate.php
- queue.php

### Routing
Automatically Amper will search for Routes class in the App namespace and call _register() method.

```php
namespace App;

use Amper\Router;

class Routes
{

  public function _register(Router $Router) : void
  {
    // Passing array of middlewares as a 3-rd argument
    $Router->options('/api/*', '', [ 'CorsMiddleware' ]);
    // Grouping routes with same middleware
    $Router->group('/api/v1',
    [
      ['GET','{product}', 'ExampleController@index'],
      ['POST','{product}/show', 'ExampleController@show']
    ], [ 'CorsMiddleware' ]);
    // Passing single routes
    $Router
      ->get('/example', 'ExampleController@example', [ 'CorsMiddleware' ])
      ->post('/example', 'ExampleController@examplePost', [ 'CorsMiddleware' ]);
  }
}
```

### Controllers
Each method of the controller class gets two arguments by default: request and response.

```php
namespace App\Controllers;

use \Amper\Request;
use \Amper\Response;

class ExampleController {
  
  public function show(Request $req, Response $res)
  {
    // Body of the request. In case of GET method body always is empty array
    $Body = $req->getBody(); 
    // Params from the Router. In case of show method, param from example is "product"
    $Params = $req->getParams();
    // Method of the request. POST, GET or another one
    $Method = $req->getMethod();
    // Query from the request URL string. In case of GET method this one will be filled up
    $Query = $req->getQuery();
    // Headers of the request
    $Headers = $req->getHeaders();

    $res
      ->setStatus(200) // Setting up HTTP status code of the response
      ->setMeta(['type' => 'success']) // Building meta data of response
      ->setData(['some' => 'payload']) // Passing payload
      ->toJson(); // Encoding to JSON
  }
}
```

### Middlewares
Middlewares are functions, that is going to be executed before request get to the controller

Example of CORS middleware, setting the proper response headers:

```php
namespace App\Middleware;

use Amper\Request;
use Amper\Response;

class CorsMiddleware {
  // By default Amper will search for handle() method and passes into this method Request and Response instances
  public function handle(Request $request, Response $response)
  {
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    // If request method is OPTIONS, send response directly to client, in other cases set proper headers
    if ($request->getMethod() == 'OPTIONS') {
      $response
        ->setMeta(['type' => 'success'])
        ->toJson()
        ->execute();
      $response->finish();
    }
  }

}
```

### Entities
Entities help you to easily manipulate with database structure at the migration time and making model for repositories.
Each property of Entity class must hold some annotations:
* @Id - unique id of the row in database
* @GeneratedValue : strategy=AUTO_INCREMENT - Strategy of auto generating the field value
* @Field : name=<some_name>, type=<some_type>, length=<some_length> - Name, type and length of field in database
* @NotNull - Setting the field as not nullable in database
* @Nullable - Setting the field as nullable in database

Class annotation must contain @Table : name=<some_name> of the table.

```php
namespace App\Entities;

use \Amper\Entity;
/**
 * @Table : name=example
 */
class ExampleEntity extends Entity {
  /**
   * @Id
   * @GeneratedValue : strategy=AUTO_INCREMENT
   * @Field : name=id, type=int, length=11
   * @NotNull
   * @var integer
   */
  private $id;
  /**
   * @NotNull
   * @Field : name=keyname, type=varchar, length=255
   * @var string
   */
  private $keyname;
  /**
   * @Nullable
   * @Field : name=value, type=varchar, length=255
   * @var string
   */
  private $value;

  /**
   * @NotNull
   * @Field: name=date_add, type=int, length=11
   * @var integer
   */
  private $dateAdd;
  
  // getters and setters
}
```

### Repositories
Repositories are acting role of ORM and helps you to manage connections of entities to the database.

By default CrudRepository helps you to construct SQL queries from method-name. For example, findById($id) will transform to SELECT * FROM table WHERE id = $id. More complex example: findAllByStatusAndTypeOrderByIdLimitDesc($status, $type, $limitStart, $limitEnd) equals to SELECT * FROM table WHERE status = $status AND type = $type LIMIT $limitStart, $limitEnd ORDER BY id DESC.

CrudRepository has following built-in methods:
* save (If the passing entity has ID, will apply UPDATE query, else will apply INSERT query)
* remove
* query
* findAll
* findAllDesc

```php
namespace App\Repositories;

use Amper\CrudRepository;
use \App\Entities\ExampleEntity;

class ConfigRepo extends CrudRepository {

  public function __construct()
  {
    parent::__construct(ExampleEntity::class);
  }
  
}
```

### Configurating
By default Amper will search for config folder in your root directory.
bootstrapper.php (starting your app):
```php
require_once(GLOBAL_DIR.'/vendor/autoload.php');

$Core = new Amper\Core();
$Core->run();
```
cache.php (rules for script, routes and entities caching):

```php
return [
  'reset_cache' => true, // In dev mode should be true to reset opcache
  'script_cache' => false, // Allow to use opcache for engine scripts
  'middleware_cache' => false, // Allow to use opcache for middleware
  'router_cache' => false, // Allows to cache all routes in a file
  'router_cache_method' => 'file',
  'entities_cache' => false, // Allow to cache entities in a file
  'entities_cache_method' => 'file'
];
```

database.php (rules for database connections and entities):

```php
return [
  'connection' => [
    'prefix' => 'pre_', // prefix for table
    'user' => 'root', // db user
    'password' => '', // db password
    'driver' => 'mysql', // db access driver
    'host' => 'localhost', // db host
    'name' => 'amper_example' // db name
  ],
  'redis' => [ // array directly passing to Predis\Client
    'scheme' => 'tcp', // redis protocol
    'host'   => '127.0.0.1', // redis host
    'port'   => 6379, // redis address
  ],
  'entities' => [ // All registered entities
    'ExampleEntity'
  ]

];
```

queue.php (rules for managing queues):
```php
return [
  'max_priority' => 5, // Max allowed priority of task
  'max_dispatch_time' => 300, // Time to retry task in fail case
  'dispatched_amount' => 10, // Single-tick dispatched tasks amount
  'dispatchers' => [ // List of all dispatchers
    'SleepDispatcher'
  ]

];
```

### Migrations
To create a migration you should have proper annotations structure in your Entity, that is described above. Place a migrate.php file in your projects root directory:

```php
define('GLOBAL_DIR', __DIR__);
require_once(GLOBAL_DIR.'/config/bootstrapper.php');

$Migrate = new Amper\Migrate;
$Migrate->refresh(); // Refresh your database structure

// some db inserts, updates and so on
```

### Queues
To create a task in a queue from any place of your project call the push($dispatcher, $priority, $payload) method:
```php
Amper\Queue::push('ExampleDispatcher', 0, ['email' => 'example@example.com', 'title' => 'title', 'body' => 'body']);
```

After that you should create a dispatcher in your App\Dispatchers dir.
ExampleDispatcher.php:
```php
namespace App\Dispatchers;

class ExampleDispatcher {
  
  public function handle(array $payload)
  {
    mail($payload['email'],$payload['title'],$payload['body']);
    return true; // To ensure queue manager that your task is done, return true. In other cases task will return to execution again.
  }
}
```
To dispatch all tasks create a queue.php file in your project's root dir:
```php
define('GLOBAL_DIR', __DIR__);
require_once(GLOBAL_DIR.'/config/bootstrapper.php');
while (true) {
  Amper\Queue::dispatchAll(); // Dispatch all tasks by priority
  Amper\Queue::dispatch($dispatcher, $priority); // Use this if you want to separate dispatching in multiple processes
}
```
To hold your loop 24/7 and control memory leaks use pm2. Example: "pm2 start queue.php".
