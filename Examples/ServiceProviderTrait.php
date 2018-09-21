<?php
/**
 * Created by PhpStorm.
 * User: fczyrnek
 * Date: 12.09.18
 * Time: 19:38
 */

namespace FiReLibs\Container\Examples;


use FiReLibs\Container\ContainerInterface;

trait ServiceProviderTrait
{
    /**
     * Method names ending with ProviderOptions are used
     * to generate configuration template.
     *
     * @return ServiceOptions
     */
    protected function serviceProviderOptions(){
        /**
         * @var ContainerInterface $this
         */
        return ServiceOptions::factory($this);
    }

    protected function serviceProviderInit(){
        /**
         * @var ContainerInterface $this
         */
        $this->service(Service::class, function (ContainerInterface $container, $key){
            $config = $this->serviceProviderOptions();
            $service = new Service($config->constructorParam);
            $service->setNumericParam($config->numericParam);
            return $service;
        });
    }

    /**
     * @return Service
     */
    public function getService(){
        /**
         * @var ContainerInterface $this
         */
        return $this->service(Service::class);
    }
}