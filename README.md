# PhpSqlSessionHandler

This library will allow to handle PHP session in a databases.
This is mainly for authenticated sessions and to maintain sessions between many php servers instances.
It also allow to have more than one http request at once after session start.

## Prerequisites

### Composer requirements

```json
"php": "^7.3|^8.0",
"illuminate/database": "^6|^7|^8",
"symfony/var-dumper": "^5"
```

### Database

Minimum for getting this code to work is to have a Database with a table named "sessions" declared like follow

```sql
--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(32) NOT NULL,
  `timestamp` int(10) UNSIGNED DEFAULT NULL,
  `data` mediumtext,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Database should be declared in your app before loading the session using Illuminate capsule manager

## Code declaration in php (sample)

This is the way to call this function

```php
use Pitch7900\SessionsHandler\DBSessionsHandler;

session_cache_limiter('public');
ini_set("session.cookie_httponly", 1);
session_name('APPS_SESSID');

//Use a custom SessionHandler Based on database.
$handler = new DBSessionsHandler(3600,'user',$rootPath."/logs/sessions.log",false);
session_set_save_handler($handler, true);
session_start();
```

## DBSessionsHadnlder Declaration

By default a non authenticated session will only last 30 seconds of iddle.

```doc
Pitch7900\SessionsHandler\DBSessionsHandler::__construct

__construct

<?php
public function __construct(
    int $session_duration = 3600,
    $authenticatedUserValue = null,
    ?string $logfile = null,
    bool $debug = false
) { }
@param int $session_duration : duration in seconds for an authenticated session

@param string $logfile : logfile for debug

@param bool $debug : enable debug mode 

@param mixed|null $authenticatedUserValue : String Value to lookup for an authenticated user. This can be changed depending on how you authenticate your sessions

@return void
```

## Implementation sample

An implementation sample can be found in this Slim Template : <https://github.com/pitch7900/slim4Template>
See bootstrap/app.php file.
