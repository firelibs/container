<?php
/**
 * Created by PhpStorm.
 * User: fczyrnek
 * Date: 12.09.18
 * Time: 19:38
 */

namespace FiReLibs\Container\Examples;


use FiReLibs\Container\Container;

class SampleContainer extends Container
{
    /*
     * This is how service provider are registered with container
     */
    use ServiceProviderTrait;
    use FactoryProviderTrait;

}