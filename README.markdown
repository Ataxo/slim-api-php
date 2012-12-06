# Slim-API PHP

Accessing SlimApi and work with clients, contracts, campaigns and statistics

## Installation

Copy SlimApi directory to your php libs directory, set autoload for including files OR include by

``` php
//main object working with API
require_once '[path_to_SlimApi_lib]/SlimApiObject.class.php';
//exceptions
require_once '[path_to_SlimApi_lib]/SlimApiException.class.php';
//including required sources
require_once '[path_to_SlimApi_lib]/objects/Contract.class.php';
//etc.
```

## Initialization

``` php
//api token - required
$config['apiToken'] = 'yourApiToken';
//taxonomy of customer - optional
$config['taxonomy'] = 'your_taxonomy';
//api domain - optional
$config['url'] = 'http://slimapi.ataxo.com';
//version of api - optional
$config['version'] = 'v1';

//api object initialization
$contract = new SlimApi\Contract($config);
```

## Get results

``` php
//return all results
$contract->all();
//return results limited by arguments
$contract->find($args);
```

## Pagination

``` php
$arr = array('limit' => 20, 'offset' => 60);
$contract->setFindOptions($arr);
```