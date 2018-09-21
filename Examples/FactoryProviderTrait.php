<?php
/**
 * Created by PhpStorm.
 * User: fczyrnek
 * Date: 12.09.18
 * Time: 19:38
 */

namespace FiReLibs\Container\Examples;


use FiReLibs\Container\ContainerInterface;

trait FactoryProviderTrait
{
    /**
     * Method names ending with ProviderOptions are used
     * to generate configuration template.
     *
     * @return FactoryOptions
     */
    protected function factoryProviderOptions(){
        /**
         * @var ContainerInterface $this
         */
        return FactoryOptions::factory($this);
    }

    protected function factoryProviderInit(){
        /**
         * @var ContainerInterface $this
         */
        $this->factory(Factory::class, function (ContainerInterface $container, $key){
            $config = $this->factoryProviderOptions();
            $service = new Factory($config->constructorParam);
            $service->setNumericParam($config->numericParam);
            return $service;
        });
    }

    /**
     * @return Factory
     */
    public function getFactory(){
        /**
         * @var ContainerInterface $this
         */
        return $this->factory(Factory::class );
    }

}