<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\DataMapper;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleConfigException;
use Codeception\TestInterface;
use Codeception\Util\ReflectionHelper;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Access the database using [Doctrine MongoDB ODM](http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/)
 * for Symfony2 projects. Doctrine's Document Manager is automatically retrieved from Symfony Service Locator.
 */

class DoctrineMongoSymfony extends CodeceptionModule implements DependsOnModule, DataMapper
{
    /**
     * @var DocumentManager
     */
    protected $dm = null;

    protected $config = [
        'depends' => null,
    ];

    protected $dependencyMessage = <<<EOF
Example using:
--
modules:
    enabled:
        - DoctrineMongoSymfony:
            depends: Symfony
--
EOF;


    /**
     * @var \Codeception\Module\Symfony
     */
    private $symfony;

    public function _depends()
    {
        return [Symfony::class => $this->dependencyMessage];
    }

    public function _inject(Symfony $symfony = null)
    {
        $this->symfony = $symfony;
    }

    public function _beforeSuite($settings = [])
    {
        $this->retrieveDocumentManager();
    }

    public function _before(TestInterface $test)
    {
        $this->retrieveDocumentManager();
    }

    protected function retrieveDocumentManager()
    {

        if ($this->symfony) {
            $this->dm = $this->symfony->_getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        }

        if (!$this->dm) {
            throw new ModuleConfigException(
                __CLASS__,
                "DocumentManager can't be obtained.\n \n"
            );
        }


        if (!($this->dm instanceof DocumentManager)) {
            throw new ModuleConfigException(
                __CLASS__,
                "Connection object is not an instance of \\Doctrine\\ODM\\MongoDB\\DocumentManager.\n"
            );
        }

        $this->dm->getConnection()->connect();
    }

    public function _after(TestInterface $test)
    {
        if (!$this->dm instanceof DocumentManager) {
            return;
        }
        $this->dm->clear();
        $this->dm->getConnection()->close();
    }


    /**
     * Performs $dm->flush();
     */
    public function flushToDatabase()
    {
        $this->dm->flush();
    }


    /**
     * Adds entity to repository and flushes. You can redefine it's properties with the second parameter.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->persistEntity(new \Entity\User, array('name' => 'Miles'));
     * $I->persistEntity($user, array('name' => 'Miles'));
     * ```
     *
     * @param $obj
     * @param array $values
     */
    public function persistEntity($obj, $values = [])
    {
        if (is_string($obj)) {
            $obj = new $obj;
        }

        if ($values) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($values as $key => $val) {
                $accessor->setValue($obj, $key, $val);
            }
        }

        $this->dm->persist($obj);
        $this->dm->flush();
    }

    /**
     * Persists record into repository.
     * This method crates an entity, and sets its properties directly (via reflection).
     * Setters of entity won't be executed, but you can create almost any entity and save it to database.
     * Returns id using `getId` of newly created entity.
     *
     * ```php
     * $I->haveInRepository('Entity\User', array('name' => 'hlogeon'));
     * ```
     *
     * @param       $entity
     * @param array $data
     *
     * @return mixed
     */
    public function haveInRepository($entity, array $data)
    {
        $reflectedEntity = new \ReflectionClass($entity);
        $entityObject = $reflectedEntity->newInstance();

        $this->dm->getHydratorFactory()->hydrate($entityObject, $data);

        $this->dm->persist($entityObject);
        $this->dm->flush();

        if (method_exists($entityObject, 'getId')) {
            $id = $entityObject->getId();
            $this->debug("$entity entity created with id:$id");
            return $id;
        }
    }

    /**
     * Flushes changes to database executes a query defined by array.
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->seeInRepository('User', array('name' => 'hlogeon'));
     * $I->seeInRepository('User', array('name' => 'hlogeon', 'Company' => array('name' => 'Codegyre')));
     * $I->seeInRepository('Client', array('User' => array('Company' => array('name' => 'Codegyre')));
     * ?>
     * ```
     *
     * Fails if record for given criteria can\'t be found,
     *
     * @param $entity
     * @param array $params
     */
    public function seeInRepository($entity, $params = [])
    {
        $res = $this->dm->getRepository($entity)->findBy($params);
        $this->assertNotEmpty($res);
    }

    /**
     * Flushes changes to database and performs ->findOneBy() call for current repository.
     *
     * @param $entity
     * @param array $params
     */
    public function dontSeeInRepository($entity, $params = [])
    {
        $res = $this->dm->getRepository($entity)->findBy($params);
        $this->assertEmpty($res);
    }

    /**
     * Selects field value from repository.
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $email = $I->grabFromRepository('User', 'email', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @version 1.1
     * @param $entity
     * @param $field
     * @param array $params
     * @return mixed
     */
    public function grabFromRepository($entity, $field, $params = [])
    {
        return ReflectionHelper::readPrivateProperty($this->grabEntityFromRepository($entity, $params), $field);
    }

    /**
     * Selects entities from repository.
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $users = $I->grabEntitiesFromRepository('AppBundle:User', array('name' => 'davert'));
     * ?>
     * ```
     *
     * @version 1.1
     * @param $entity
     * @param array $params
     * @return array
     */
    public function grabEntitiesFromRepository($entity, $params = [])
    {
        // we need to store to database...
        $this->dm->flush();

        return $this->dm->getRepository($entity)->findBy($params);
    }

    /**
     * Selects a single entity from repository.
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $user = $I->grabEntityFromRepository('User', array('id' => '1234'));
     * ?>
     * ```
     *
     * @version 1.1
     * @param $entity
     * @param array $params
     * @return object
     */
    public function grabEntityFromRepository($entity, $params = [])
    {
        // we need to store to database...
        $this->dm->flush();

        return $this->dm->getRepository($entity)->findOneBy($params);
    }

    /**
     * Deletes entity by its id
     * It builds query based on array of parameters.
     * You can use entity associations to build complex queries.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $I->removeEntity('User', ['_id' => '123']);
     * ?>
     * ```
     *
     * @version 1.1
     * @param $entity
     * @param array $params
     */
    public function removeEntity($entity, $params)
    {
        $res = $this->dm->getRepository($entity)->findBy($params);

        foreach ($res as $r) {
            $this->dm->remove($r);
        }
        $this->dm->flush($res);
    }

    /**
     * Drops collection
     *
     * @param $className
     */
    public function dropCollection($className)
    {
        $this->dm->getDocumentCollection($className)->drop();
    }

    /**
     * @return DocumentManager
     */
    public function _getEntityManager()
    {
        return $this->dm;
    }

    /**
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->dm;
    }
}
