<?php
/**
 * Container interface
 */

namespace FiReLibs\Container;


interface ContainerInterface
{
    /**
     * SERVICE type: Generates a single instance for every request.
     */
    const SERVICE = 0;
    /**
     * FACTORY type: Generates a unique instance for every request.
     */
    const FACTORY = 1;
    /**
     * Provider options method suffix is used to determine method names used to generate config file.
     */
    const OPTION_SUFFIX = "ProviderOptions";
    /**
     * Provider init suffix is used to determine method names used to initialize container.
     */
    const INIT_SUFFIX = "ProviderInit";

    /**
     * Register option class with container.
     * @param ContainerOptionsInterface $options Class to be registered.
     * @param bool $update Update class with container configuration
     * @return ContainerOptionsInterface
     */
    public function addOptions(ContainerOptionsInterface $options, $update = true);

    /**
     * Checks if option class is registered
     * @param string $className
     * @return boolean
     */
    public function hasOptions($className);

    /**
     * Retrieves option class
     * @param string $className Class name to be retrieved
     * @param bool $autoload Autoload if not present
     * @throws \RuntimeException If class is not registered or can't be auto-loaded.
     * @return ContainerOptionsInterface
     */
    public function getOptions($className, $autoload = true);

    /**
     * Makes option class read-only.
     * @param ContainerOptionsInterface $options
     * @return ProtectedContainerOptions
     */
    public function protectOptions(ContainerOptionsInterface $options);

    /**
     * Checks if SERVICE or FACTORY is registered.
     * @param string $name
     * @return boolean
     */
    public function has($name);

    /**
     * Checks if SERVICE is registered and is actually a SERVICE.
     * @param string $name
     * @return boolean
     */
    public function isService($name);
    /**
     * Checks if FACTORY is registered and is actually a FACTORY.
     * @param string $name
     * @return boolean
     */
    public function isFactory($name);

    /**
     * Checks if given type is registered.
     * @param string $name
     * @param int $type
     * @return boolean
     */
    public function isType($name, $type);

    /**
     * Define / retrieve service.
     * If callback parameter is given this method will define a SERVICE.
     * RuntimeException will be thrown if service is already defined.
     * Invoking it without callback parameter will create a service.
     *
     * @param string $name Key associated with given service
     * @param null|callable $callback Callback method to create service function(ContainerInterface $container, $name)
     * @throws \RuntimeException
     * @return mixed
     */
    public function service($name, $callback = null);
    /**
     * Define / retrieve factory.
     * If callback parameter is given this method will define a FACTORY.
     * RuntimeException will be thrown if factory is already defined.
     * Invoking it without callback parameter will create a factory.
     *
     * @param string $name Key associated with given factory
     * @param null|callable $callback Callback method to create factory function(ContainerInterface $container, $name)
     * @throws \RuntimeException
     * @return mixed
     */
    public function factory($name, $callback = null);

    /**
     * Extend already defined service or factory.
     * RuntimeException will be thrown if types not match or service was not defined.
     * @param string $name
     * @param int $type
     * @param callable $callback Callback function: function(callable $original, ContainerInterface $container, $name)
     * @throws \RuntimeException
     * @return mixed
     */
    public function extend($name, $type, callable $callback);

    /**
     * Retrieves raw callable for given service.
     * @param string $name
     * @return callable
     */
    public function raw($name);

    /**
     * Generates a template configuration for this container.
     * @return array
     */
    public function generateConfig();

}