<?php
namespace Apitude\Core\API;


use Apitude\Core\Annotations\API\Entity;
use Apitude\Core\Annotations\API\Property;
use Doctrine\Common\Annotations\Reader;// interface
use Metadata\Driver\DriverInterface;

class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return \Metadata\ClassMetadata
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new ClassMetadata($class->getName());

        // should class be exposed?
        $annotation = $this->reader->getClassAnnotation(
            $class,
            Entity\Expose::class
        );
        $classMetadata->setExposed($annotation !== null);

        // not exposed so just return here
        if (!$classMetadata->isExposed()) {
            return $classMetadata;
        }

        if ($annotation->name) {
            $classMetadata->setExposedName($annotation->name);
        }

        foreach ($class->getProperties() as $reflProperty) {
            $propMeta = new PropertyMetadata($class->getName(), $reflProperty->getName());

            // is property exposed?
            /** @var Property\Expose $annotation */
            $annotation = $this->reader->getPropertyAnnotation(
                $reflProperty,
                Property\Expose::class
            );
            if ($annotation) {
                $propMeta->setExposed(true);

                if ($annotation->name) {
                    $propMeta->setExposedName($annotation->name);
                }
                // set property renderer
                /** @var Property\Renderer $annotation */
                $annotation = $this->reader->getPropertyAnnotation(
                    $reflProperty,
                    Property\Renderer::class
                );
                if ($annotation) {
                    if ($annotation->renderService) {
                        $propMeta->setRenderService($annotation->renderService);

                        if ($annotation->renderMethod) {
                            $propMeta->setRenderMethod($annotation->renderMethod);
                        }
                    }
                }

                // property getter?
                /** @var Property\GetterMethod $annotation */
                $annotation = $this->reader->getPropertyAnnotation(
                    $reflProperty,
                    Property\GetterMethod::class
                );
                if ($annotation) {
                    if ($annotation->method) {
                        $propMeta->setGetterMethod($annotation->method);
                    } else {
                        // standard getter
                        $propMeta->setGetterMethod(
                            'get'.ucfirst($propMeta->getName())
                        );
                    }
                } elseif (!$reflProperty->isPublic()) {
                    // assuming standard getter
                    $propMeta->setGetterMethod(
                        'get'.ucfirst($propMeta->getName())
                    );
                }
            }
            $classMetadata->addPropertyMetadata($propMeta);
        }

        return $classMetadata;
    }
}
