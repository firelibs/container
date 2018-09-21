<?php
/**
 * Created by PhpStorm.
 * User: fczyrnek
 * Date: 12.09.18
 * Time: 19:06
 */

namespace FiReLibs\Container\Tests;

use FiReLibs\Container\ContainerInterface;
use FiReLibs\Container\ContainerOptionsInterface;
use FiReLibs\Container\Examples\Factory;
use FiReLibs\Container\Examples\FactoryOptions;
use FiReLibs\Container\Examples\SampleContainer;
use FiReLibs\Container\Examples\Service;
use FiReLibs\Container\Examples\ServiceOptions;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testContainerCreation(){

        $container = new SampleContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container,
            "Container implements ContainerInterface");

        $config = [
            "FiReLibs\Container\Examples\ServiceOptions" => [
                "constructorParam" => "constructorParam",
                "numericParam" => 1
            ],
            "FiReLibs\Container\Examples\FactoryOptions" => [
                "constructorParam" => "constructorParam",
                "numericParam" => 1
            ]
        ];

        $this->assertEquals($config, $container->generateConfig(),
            "Configuration is automatically generated");
    }

    public function testSingleOptionsInstanceIsGenerated(){
        $container = new SampleContainer();

        $opt1 = ServiceOptions::factory($container);
        $opt2 = ServiceOptions::factory($container);
        $opt3 = new ServiceOptions($container);
        $this->assertEquals($opt1, $opt2,
            "Same instance of configuration should be returned when using the same container with and a factory() method.");

        $opt1->constructorParam = 'modified';

        $this->assertEquals($opt1->constructorParam, $opt2->constructorParam,
            "Modifying parameter in 1 instance");
        $this->assertNotEquals($opt3, $opt2,
            "Possibility to create another instance without factory() method.");

    }

    public function testConfigurationParameters(){

        $config = [
            "FiReLibs\Container\Examples\ServiceOptions" => [
                "constructorParam" => "modifiedParam",
            ]
        ];

        $container = new SampleContainer($config);
        $opt = ServiceOptions::factory($container);

        $this->assertEquals("modifiedParam", $opt->constructorParam,
            "Options should be overwritten by a config.");
        $this->assertEquals("modifiedParam", $container->getService()->getConstructorParam(),
            "Modified values should be used for service creation.");

    }

    public function testFactoryCreation(){

        $container = new SampleContainer();
        $factory1 = $container->getFactory();

        $config = FactoryOptions::factory($container);

        $this->assertInstanceOf(Factory::class, $factory1,
            "Factory should be an instance of a Factory class.");
        $this->assertEquals($factory1->getConstructorParam(), $config->constructorParam,
            "Option parameter should be passed to constructor.");
        $this->assertEquals($factory1->getNumericParam(), $config->numericParam,
            "Additional parameter should be set.");

        $config->numericParam++;

        $factory2 = $container->getFactory();

        $this->assertInstanceOf(Factory::class, $factory2,
            "Factory should be an instance of a Factory class.");

        $this->assertEquals($factory2->getConstructorParam(), $config->constructorParam,
            "Option parameter should be passed to constructor.");
        $this->assertEquals($factory2->getNumericParam(), $config->numericParam,
            "Additional parameter should be set.");

        $this->assertNotEquals($factory1->getNumericParam(), $factory2->getNumericParam(),
            "As we changed a parameter before creating new instance. Values should not match.");

        $this->assertFalse($factory1 === $factory2,
            "Factory creates unique instance.");
        $this->assertTrue($container->has(Factory::class),
            "Factory is registered");
    }

    public function testServiceCreation(){

        $container = new SampleContainer();

        $service1 = $container->getService();
        $config = ServiceOptions::factory($container);
        $config->numericParam++;
        $service2 = $container->getService();

        $this->assertInstanceOf(Service::class, $service1,
            "Service should be an instance of a Service class.");
        $this->assertInstanceOf(Service::class, $service2,
            "Service should be an instance of a Service class.");
        $this->assertTrue($service1 === $service2,
            "Both should be the same instances.");

        $this->assertEquals($service1->getConstructorParam(), $config->constructorParam,
            "Service should have a constructor parameter passed.");
        $this->assertNotEquals($service1->getNumericParam(), $config->numericParam,
        "Modifying parameters after service was created will have no effect.");

        $this->assertEquals($service1->getNumericParam(), $service2->getNumericParam(),
            "Both parameters should be the same.");
        $this->assertTrue($container->has(Service::class),
            "Service is registered");

    }

    public function testProtectingOptions(){

        $container = new SampleContainer();
        $config = ServiceOptions::factory($container);
        $this->assertInstanceOf(ContainerOptionsInterface::class, $config);
        $opt = $config->toArray();
        $config = $config->protect();
        $this->assertInstanceOf(ContainerOptionsInterface::class, $config);
        $opt2 = $config->toArray();
        $this->assertEquals($opt2, $opt);

    }
    public function testModifyingProtectingOptions(){

        $container = new SampleContainer();
        $config = ServiceOptions::factory($container);
        $this->assertInstanceOf(ContainerOptionsInterface::class, $config);
        $config = $config->protect();
        $this->assertInstanceOf(ContainerOptionsInterface::class, $config);
        $this->expectException(\RuntimeException::class);
        $config->constructorParam = "1";
    }

    public function testExtendingServices(){
        $container = new SampleContainer();
        $container->extend(Service::class, $container::SERVICE, function($original, $container, $name){
            /**
             * @var Service $service;
             */
            $service = $original($container, $name);
            $service->setNumericParam($service->getNumericParam()+1);
            return $service;
        });
        $container->extend(Service::class, $container::SERVICE, function($original, $container, $name){
            /**
             * @var Service $service;
             */
            $service = $original($container, $name);
            $service->setNumericParam($service->getNumericParam()+1);
            return $service;
        });

        $service = $container->getService();
        $options = ServiceOptions::factory($container);
        $this->assertInstanceOf(Service::class, $service,
            "Service should be an instance of a Service class.");

        $this->assertEquals($options->numericParam + 2, $service->getNumericParam(),
            "Modifying parameters when function was extended will have effect");


    }

    public function testExtendingServiceWithWrongType(){
        $container = new SampleContainer();
        $this->expectException(\RuntimeException::class);
        $container->extend(Service::class, $container::FACTORY, function($original, $container, $name){
            /**
             * @var Service $service;
             */
            $service = $original($container, $name);
            $service->setNumericParam($service->getNumericParam()+1);
            return $service;
        });
    }

    public function testExtendingFactoryWithWrongType(){
        $container = new SampleContainer();
        $this->expectException(\RuntimeException::class);
        $container->extend(Factory::class, $container::SERVICE, function($original, $container, $name){
            /**
             * @var Factory $factory;
             */
            $factory = $original($container, $name);
            $factory->setNumericParam($factory->getNumericParam()+1);
            return $factory;
        });
    }

    public function testExtendingServicesInUse(){
        $container = new SampleContainer();
        $container->getService();
        $this->expectException(\RuntimeException::class);
        $container->extend(Service::class, $container::SERVICE, function($original, $container, $name){
            /**
             * @var Service $service;
             */
            $service = $original($container, $name);
            $service->setNumericParam($service->getNumericParam()+1);
            return $service;
        });
    }

    public function testExtendingFactories(){
        $container = new SampleContainer();
        $container->extend(Factory::class, $container::FACTORY, function($original, $container, $name){
            /**
             * @var Factory $service;
             */
            $service = $original($container, $name);
            $service->setNumericParam($service->getNumericParam()+1);
            return $service;
        });
        $container->extend(Factory::class, $container::FACTORY, function($original, $container, $name){
            /**
             * @var Factory $service;
             */
            $service = $original($container, $name);
            $service->setNumericParam($service->getNumericParam()+1);
            return $service;
        });

        $factory = $container->getFactory();
        $options = FactoryOptions::factory($container);
        $this->assertInstanceOf(Factory::class, $factory,
            "Factory should be an instance of a Factory class.");

        $this->assertEquals($options->numericParam + 2, $factory->getNumericParam(),
            "Modifying parameters when function was extended will have effect");


    }
    public function testExtendingFactoriesInUse(){
        $container = new SampleContainer();
        $container->getFactory();
        $this->expectException(\RuntimeException::class);
        $container->extend(Factory::class, $container::SERVICE, function($original, $container, $name){
            /**
             * @var Factory $factory;
             */
            $factory = $original($container, $name);
            $factory->setNumericParam($factory->getNumericParam()+1);
            return $factory;
        });
    }
}
