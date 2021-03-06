<?php
namespace Apitude\Core\API\Writer;


use Apitude\Core\API\ClassMetadata;
use Apitude\Core\API\MetadataFactory;
use Apitude\Core\API\PropertyMetadata;
use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;

class ArrayWriter implements WriterInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return MetadataFactory
     */
    protected function getMetadataFactory()
    {
        return $this->container[MetadataFactory::class];
    }

    protected function getMetadataForObject($object)
    {
        return $this->getMetadataFactory()->getMetadataForClass(get_class($object));
    }

    public function writeObject($object)
    {
        $data = [];
        $meta = $this->getMetadataForObject($object);

        if (!$meta || !$meta instanceof ClassMetadata || !$meta->isExposed()) {
            return null;
        }

        $data['@type'] = $meta->getExposedName();
        /** @var PropertyMetadata $propMeta */
        foreach ($meta->getPropertyMetadata() as $propMeta) {
            if (!$propMeta->isExposed()) {
                continue;
            }

            if ($propMeta->getGetterMethod()) {
                $getter = $propMeta->getGetterMethod();
                $value = $object->$getter();
            } else {
                $value = $object->{$propMeta->getName()};
            }

            if ($propMeta->getRenderService()) {
                $service = $this->container[$propMeta->getRenderService()];
                $method = $propMeta->getRenderMethod() ?: 'render';
                $value = $service->{$method}($value);
            }

            if (is_object($value)) {
                $value = $this->writeObject($value);
            }

            $data[$propMeta->getExposedName()] = $value;
        }

        return $data;
    }

    public function writeCollection(CollectionInterface $collection)
    {
        // TODO: Implement writeCollection() method.
    }
}
