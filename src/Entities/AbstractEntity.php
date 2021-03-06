<?php
namespace Apitude\Core\Entities;


use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractEntity
 * @package Apitude\Core\Entities
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntity
{
    /**
     * Get Entity class
     * @return string
     */
    public function getEntityClass()
    {
        return $this instanceof Proxy ? get_parent_class($this) : self::class;
    }
}
