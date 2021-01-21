<?php

namespace WpkColoco\Wepika\Repository;

use ObjectModel;

/**
 * Class AbstractRepository
 */
abstract class AbstractRepository
{
    /**
     * @var string
     */
    private $entityType;

    /**
     * AbstractRepository constructor.
     *
     * @param string $entityType
     */
    public function __construct($entityType)
    {
        $this->setEntityType($entityType);
    }

    /**
     * @param string $entityType
     */
    private function setEntityType($entityType)
    {
        if (!class_exists($entityType)) {
            throw new \InvalidArgumentException("entityType must be a class name");
        }

        $this->entityType = $entityType;
    }

    /**
     * @return bool
     */
    abstract public function createTables();

    /**
     * @return bool
     */
    abstract public function dropTables();

    /**
     * @param \ObjectModel $entity
     * @return bool
     * @throws \PrestaShopException
     */
    public function add($entity)
    {
        $this->checkObjectModelInheritance($entity);

        return $entity->add();
    }

    /**
     * @param \ObjectModel $entity
     * @return bool
     */
    private function checkObjectModelInheritance($entity)
    {
        if (!($entity instanceof ObjectModel)) {
            throw new \InvalidArgumentException("Entity must be a child of ObjectModel class.");
        }

        return true;
    }

    /**
     * @param \ObjectModel $entity
     * @return bool
     * @throws \PrestaShopException
     */
    public function update($entity)
    {
        $this->checkObjectModelInheritance($entity);

        return $entity->update();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getById($id)
    {
        $className = $this->getEntityType();
        return new $className($id);
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @param \ObjectModel $entity
     * @return bool
     * @throws \PrestaShopException
     */
    public function delete($entity)
    {
        $this->checkObjectModelInheritance($entity);

        return $entity->delete();
    }

    /**
     * @param $entity
     * @return bool
     */
    public function save($entity)
    {
        $this->checkObjectModelInheritance($entity);

        return $entity->save();
    }
}