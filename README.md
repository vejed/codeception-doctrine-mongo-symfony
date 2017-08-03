# DoctrineMongoSymfony


Allows integration with Codeception for projects with Doctrine MongoDB ODM for Symfony2 projects.

## Status

* Maintainer: **vejed**
* Stability: **unstable**
* Contact: vejed@vejed.net.ru

## Config
 #### Example (`functional.suite.yml`)

     modules:
         enabled: [DoctrineMongoSymfony]
         config:
            DoctrineMongoSymfony:
                depends: Symfony

## Actions

### dontSeeInRepository
 
Flushes changes to database and performs ->findOneBy() call for current repository.

 * `param` $entity
 * `param array` $params


### flushToDatabase
 
Performs $dm->flush();


### grabFromRepository
 
Selects field value from repository.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$email = $I->grabFromRepository('User', 'email', array('name' => 'davert'));
?>
```

 * `param` $entity
 * `param` $field
 * `param array` $params
 * `return` array


### haveInRepository
 
Persists record into repository.
This method crates an entity, and sets its properties directly (via reflection).
Setters of entity won't be executed, but you can create almost any entity and save it to database.
Returns id using `getId` of newly created entity.

```php
$I->haveInRepository('Entity\User', array('name' => 'davert'));
```


### persistEntity
 
Adds entity to repository and flushes. You can redefine it's properties with the second parameter.

Example:

``` php
<?php
$I->persistEntity(new \Entity\User, array('name' => 'Miles'));
$I->persistEntity($user, array('name' => 'Miles'));
```

 * `param` $obj
 * `param array` $values


### seeInRepository
 
Flushes changes to database executes a query defined by array.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$I->seeInRepository('User', array('name' => 'davert'));
$I->seeInRepository('User', array('name' => 'davert', 'Company' => array('name' => 'Codegyre')));
$I->seeInRepository('Client', array('User' => array('Company' => array('name' => 'Codegyre')));
?>
```

Fails if record for given criteria can\'t be found,

 * `param` $entity
 * `param array` $params
