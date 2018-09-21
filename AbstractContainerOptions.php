<?php
/**
 * AbstractContainerOptions provides base configuration options functionality.
 * To create new option class simply extend this class and define public properties.
 */

namespace FiReLibs\Container;


abstract class AbstractContainerOptions implements ContainerOptionsInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @inheritdoc
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public static function factory(ContainerInterface $container)
    {
        $className = get_called_class();
        return $container->getOptions($className, true);
    }

    /**
     * @inheritdoc
     */
    public function getContainer(){
        return $this->container;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $className = get_called_class();
        $result = [];
        $result[$className] = [];
        $reflection = new \ReflectionObject($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            /**
             * @var \ReflectionProperty $property
             */
            $result[$className][$property->getName()] = $property->getValue($this);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function fromArray(array $data)
    {
        $className = get_called_class();
        if(array_key_exists($className, $data)) {
            foreach ($data[$className] as $property => $value) {
                if(!property_exists($this, $property)){
                    throw new \RuntimeException(
                        sprintf('Invalid configuration property "%s" for "%s".', $property, $className)
                    );
                }
                $this->{$property} = $value;
            }
        }
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function getConfigClassName()
    {
        return get_called_class();
    }
    /**
     * @inheritdoc
     */
    public function isProtected()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function protect(){
        return $this->getContainer()->protectOptions($this);
    }
}