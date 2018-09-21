<?php
/**
 * ContainerOptionsInterface
 *
 * This interface describes configuration classes for container services.
 */

namespace FiReLibs\Container;


interface ContainerOptionsInterface
{
    /**
     * ContainerOptionsInterface constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container);

    /**
     * Factory method.
     * It is recommended to use this method instead of constructor as it return the same
     * instance of the option class.
     * @param ContainerInterface $container
     * @return $this
     */
    public static function factory(ContainerInterface $container);

    /**
     * Returns container assigned to this config.
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * Serialize configuration to array.
     *
     * This method will create an array with all public properties of this class.
     * The following format will be generated:
     * [
     *  "FullClassNameWithNamespace" => [
     *    "publicProperty1" => "value1"
     *    "publicProperty2" => "value2"
     *    ....
     *  ]
     * ]
     * To change a root key ("FullClassNameWithNamespace") overwrite getConfigClassName method.
     * @return array
     */
    public function toArray();

    /**
     * Stets all public properties for this class from given array.
     * @return  $this
     */
    public function fromArray(array $data);

    /**
     * Returns full class name with namespace.
     * @return string
     */
    public function getConfigClassName();

    /**
     * Determines if options can be modified or not.
     * @return boolean
     */
    public function isProtected();

    /**
     * Returns instance of ProtectedContainerOptions binded to this object.
     * This will also set option class within container as protected.
     * In this doc-bloc $this is given as a return value to make sure auto-completion works as expected.
     * @return $this
     */
    public function protect();
}