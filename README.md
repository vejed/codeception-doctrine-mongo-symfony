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
Fails if record for given criteria was found.

Example:

``` php
<?php
$I->dontSeeInRepository('User', array('name' => 'hlogeon'));
$I->dontSeeInRepository(User::class, array('name' => 'tst', 'permissions.perm' => 'edit'));
?>
```

 * `param string` $className
 * `param array` $params


### dropCollection
 
Drops collection

 * `param string` $className


### flushToDatabase
 
Performs $dm->flush();


### getDocumentManager
 
Returns DocumentManager object


### grabEntitiesFromRepository
 
Selects entities from repository.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$users = $I->grabEntitiesFromRepository('AppBundle:User', array('name' => 'tst'));
?>
```

 * `param` $className
 * `param` $field
 * `param array` $params
 * `return` array


### grabEntityFromRepository
 
Selects a single entity from repository.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$user = $I->grabEntityFromRepository('AppBundle:User', array('id' => '1234'));
?>
```

 * `param` $className
 * `param` $field
 * `param array` $params
 * `return` array


### grabFromRepository
 
Selects field value from repository.
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$email = $I->grabFromRepository('AppBundle:User', 'email', array('name' => 'davert'));
?>
```

 * `param string` $className
 * `param string` $field
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

 * `param string` $className
 * `param array` $data


### persistEntity
 
Adds entity to repository and flushes. You can redefine it's properties with the second parameter.

Example:

``` php
<?php
$I->persistEntity(\Entity\User::class, array('name' => 'Miles'));
$I->persistEntity($user, array('name' => 'Miles'));
```

 * `param string|object` $obj
 * `param array` $values


### removeEntity
 
Deletes entity by its id
It builds query based on array of parameters.
You can use entity associations to build complex queries.

Example:

``` php
<?php
$I->removeEntity(User::class, ['_id' => '123']);
```

 * `param string` $className
 * `param array` $params


### seeInRepository
 
Flushes changes to database and performs ->findOneBy() call for current repository.
Fails if record for given criteria can't found.

Example:

``` php
<?php
$I->seeInRepository('User', array('name' => 'hlogeon'));
$I->seeInRepository(User::class, array('name' => 'tst', 'permissions.perm' => 'edit'));
?>
```

 * `param string` $className
 * `param array` $params
