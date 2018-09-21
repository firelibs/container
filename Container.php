<?php
/**
 * Container class
 */

namespace FiReLibs\Container;


class Container implements ContainerInterface
{
    /**
     * @var array Configuration array - allows to override options.
     */
    protected $configs = [];
    /**
     * @var array[ContainerOptionsInterface] Registered option classes
     */
    protected $options = [];
    /**
     * @var array Created services
     */
    protected $services = [];
    /**
     * @var array Raw methods for service/factory creations
     */
    protected $raw = [];
    /**
     * @var array Array with types
     */
    protected $types = [];
    /**
     * @var null|array List of init methods to be executed in constructor.
     */
    protected $initMethods;
    /**
     * @var null|array List of configuration methods used to generate config.
     */
    protected $optionMethods;

    /**
     * Container constructor.
     *
     * @param array $configs
     * @param null|array $initMethods Allows to filter initialisation methods.
     * @param null|array $optionMethods Allows to filter configuration methods.
     */
    public function __construct($configs = [], $initMethods = null, $optionMethods = null)
    {
        $this->configs = $configs;
        $this->initMethods = $initMethods;
        $this->optionMethods = $optionMethods;
        $this->initServiceProviders();
    }

    /**
     * Initialize service providers.
     */
    protected function initServiceProviders(){
        if(is_null($this->initMethods)){
            $this->initMethods = $this->extractInitMethods();
        }
        foreach ($this->initMethods as $method){
            $this->{$method}();
        }
    }

    /**
     * Returns list of methods matching INIT_SUFFIX pattern within this class.
     * @return array
     */
    protected function extractInitMethods(){
        $methods = [];
        $len = strlen(self::INIT_SUFFIX);
        foreach (get_class_methods($this) as $method) {
            if ((substr($method, -$len) === self::INIT_SUFFIX)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }
    /**
     * Returns list of methods matching OPTION_SUFFIX pattern within this class.
     * @return array
     */
    protected function extractOptionMethods(){
        $methods = [];
        $len = strlen(self::OPTION_SUFFIX);
        foreach (get_class_methods($this) as $method) {
            if ((substr($method, -$len) === self::OPTION_SUFFIX)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * @inheritdoc
     */
    public function addOptions(ContainerOptionsInterface $options, $update = true)
    {
        $className = get_class($options);
        if($this->hasOptions($className)){
            throw new \RuntimeException(sprintf('Configuration options already exists "%s".', $className));
        }

        if($update && array_key_exists($className, $this->configs)){
            $options->fromArray($this->configs);
        }
        $this->options[$className] = $options;
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function hasOptions($className)
    {
        return array_key_exists($className, $this->options);
    }
    /**
     * @inheritdoc
     */
    public function getOptions($className, $autoload = true)
    {
        if(!$this->hasOptions($className)){
            if(!$autoload || !class_exists($className) || !is_subclass_of($className, ContainerOptionsInterface::class)){
                throw new \RuntimeException(sprintf('Invalid configuration option class "%s".', $className));
            }
            $this->addOptions(new $className($this));
        }
        return $this->options[$className];
    }
    /**
     * @inheritdoc
     */
    public function protectOptions(ContainerOptionsInterface $options){

        if($options->getContainer() !== $this){
            throw new \RuntimeException(sprintf("Given option class \"%s\" is created with different container.", $options->getConfigClassName()));
        }

        if($options instanceof ProtectedContainerOptions || $this->options[$options->getConfigClassName()] instanceof ProtectedContainerOptions){
            throw new \RuntimeException(sprintf("Option class \"%s\" is already protected.", $options->getConfigClassName()));
        }

        $protected = new ProtectedContainerOptions($this);
        $protected->setProtectedObject($options);
        $this->options[$options->getConfigClassName()] = $protected;
        return $protected;

    }
    /**
     * @inheritdoc
     */
    public function factory($name, $callback = null)
    {
        if(!$this->has($name)){

            if(!is_callable($callback)){
                throw new \InvalidArgumentException(sprintf('Invalid callback for "%s" factory.', $name));
            }

            $this->types[$name] = static::FACTORY;
            $this->raw[$name] = $callback;
            return null;

        }
        if(!is_null($callback)){
            throw new \RuntimeException(sprintf('"%s" factory is already defined. Use extend instead.', $name));
        }
        if(!$this->isFactory($name)) {
            throw new \RuntimeException(sprintf('Can\'t access "%s" using FACTORY type.', $name));
        }
        $this->services[$name] = true;
        return $this->raw[$name]($this, $name);
    }
    /**
     * @inheritdoc
     */
    public function service($name, $callback = null)
    {
        if(!$this->has($name)){
            if(!is_callable($callback)){
                throw new \InvalidArgumentException(sprintf('Invalid callback for "%s" service.', $name));
            }
            $this->types[$name] = static::SERVICE;
            $this->raw[$name] = $callback;
            return null;

        }
        if(!is_null($callback)){
            throw new \RuntimeException(sprintf('"%s" service is already defined. Use extend instead.', $name));
        }

        if(!$this->isService($name)){

            throw new \RuntimeException(sprintf('Can\'t access "%s" using SERVICE type.', $name));
        }

        if(!isset($this->services[$name])){
            $this->services[$name] = $this->raw[$name]($this, $name);
        }

        return $this->services[$name];
    }
    /**
     * @inheritdoc
     */
    public function extend($name, $type, callable $callback){
        if(!isset($this->types[$name]) || $this->types[$name] !== $type){
            throw new \RuntimeException(sprintf("To extend \"%s\" SERVICE or FACTORY it need's to be defined and the same type.", $name));
        }
        if(isset($this->services[$name])){
            throw new \RuntimeException(sprintf("Extending is not possible once  \"%s\" SERVICE or FACTORY is already in use.", $name));
        }
        $original = $this->raw($name);
        $this->raw[$name] = function(ContainerInterface $container, $name) use($original, $callback) {
            return $callback($original, $container, $name);
        };
    }
    /**
     * @inheritdoc
     */
    public function raw($name){
        return $this->has($name) ? $this->raw[$name] :null;
    }
    /**
     * @inheritdoc
     */
    public function has($name)
    {
        return isset($this->types[$name]);
    }
    /**
     * @inheritdoc
     */
    public function isType($name, $type){
        return $this->has($name) && $this->types[$name] == $type;
    }
    /**
     * @inheritdoc
     */
    public function isService($name){
        return $this->has($name) && $this->types[$name] == static::SERVICE;
    }
    /**
     * @inheritdoc
     */
    public function isFactory($name){
        return $this->has($name) && $this->types[$name] == static::FACTORY;
    }
    /**
     * @inheritdoc
     */
    public function generateConfig(){
        $config = [];
        if(is_null($this->optionMethods)){
            $this->optionMethods = $this->extractOptionMethods();
        }
        foreach ($this->optionMethods as $method){
            $opt = $this->{$method}();
            if($opt instanceof ContainerOptionsInterface){
                $config = array_merge($config, $opt->toArray());
            }else {
                throw new \RuntimeException(sprintf('Method "%s" should return "%s" instance.', $method, ContainerOptionsInterface::class));
            }
        }
        return $config;
    }
}