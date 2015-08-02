<?php
namespace Apitude\API;


use Metadata\MergeableClassMetadata;

class ClassMetadata extends MergeableClassMetadata
{
    private $exposed = false;
    private $exposedName;

    public function setExposed($bool)
    {
        $this->exposed = $bool;
    }

    public function isExposed()
    {
        return $this->exposed;
    }

    public function setExposedName($name)
    {
        $this->exposedName = $name;
    }

    /**
     * Returns either the configured exposure name or the dotted class name by default
     * @return string
     */
    public function getExposedName()
    {
        return $this->exposedName ?: str_replace('\\', '.', trim($this->name, '\\'));
    }
}