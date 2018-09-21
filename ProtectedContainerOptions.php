<?php
/**
 * ProtectedContainerOptions class is used to mark options as read-only.
 */

namespace FiReLibs\Container;


class ProtectedContainerOptions implements ContainerOptionsInterface
{
    /**
     * @var ContainerOptionsInterface
     */
    protected $protectedObject;

    /**
     * ProtectedContainerOptions constructor.
     * This method parameter is ignored but required by the interface.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {

    }

    /**
     * Sets a parent option object that needs to be protected.
     * @param ContainerOptionsInterface $obj
     */
    public function setProtectedObject(ContainerOptionsInterface $obj){
        if(is_null($this->protectedObject)){
            $this->protectedObject = $obj;
        }
    }

    /**
     * Factory method is disabled for protected objects.
     *
     * @param ContainerInterface $container
     * @return mixed|void
     */
    public static function factory(ContainerInterface $container)
    {
        throw new \RuntimeException('Factory method can\'t be invoked on protected option class.');
    }

    /**
     * Redirect toArray method to original object.
     * @return array
     */
    public function toArray()
    {
        return $this->protectedObject->toArray();
    }

    /**
     * Modification is disabled and will trigger a RuntimeException
     * @param array $data
     * @throws \RuntimeException
     * @return ContainerOptionsInterface|void
     */
    public function fromArray(array $data)
    {
        throw new \RuntimeException(sprintf('This instance of "%s" is protected and can\'t be modified.', get_class($this->protectedObject)));
    }

    /**
     * Redirect getContainer method to original object.
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->protectedObject->getContainer();
    }

    /**
     * Returns property value if exists.
     *
     * @param $name
     * @throws \RuntimeException Exception is thrown if property does not exist in original object.
     * @return mixed
     */
    public function __get($name){
        if(!property_exists($this->protectedObject, $name)) {
            throw new \RuntimeException(sprintf('Property "%s" does not exist in class "%s".', $name, get_class($this->protectedObject)));
        }
        return $this->protectedObject->{$name};
    }

    /**
     * Setter is disabled.
     * @param $name
     * @throws \RuntimeException
     * @param $value
     */
    public function __set($name, $value)
    {
        throw new \RuntimeException(sprintf('This instance of "%s" is protected and can\'t be modified.', get_class($this->protectedObject)));
    }

    /**
     * Calling methods is allowed and redirected to original object.
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if(!method_exists($this->protectedObject, $name)) {
            throw new \RuntimeException(sprintf('Method "%s" does not exist in class "%s".', $name, get_class($this->protectedObject)));
        }
        return call_user_func_array([$this->protectedObject, $name], $arguments);
    }

    /**
     * Returns original class name.
     *
     * @return string
     */
    public function getConfigClassName()
    {
       return $this->protectedObject->getConfigClassName();
    }

    /**
     * Returns true as this is a protected instance.
     * @return bool
     */
    public function isProtected()
    {
        return true;
    }

    /**
     * The same instance is returned as it is already protected.
     * @return $this|ContainerOptionsInterface
     */
    public function protect()
    {
        return $this;
    }
}