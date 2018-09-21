<?php
/**
 * Created by PhpStorm.
 * User: fczyrnek
 * Date: 12.09.18
 * Time: 19:43
 */

namespace FiReLibs\Container\Examples;


use FiReLibs\Container\AbstractContainerOptions;

class ServiceOptions extends AbstractContainerOptions
{
    /**
     * @var string Parameter passed to constructor
     */
    public $constructorParam = 'constructorParam';
    /**
     * @var int Numeric parameter
     */
    public $numericParam = 1;
}