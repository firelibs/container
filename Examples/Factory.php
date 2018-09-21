<?php
/**
 * Created by PhpStorm.
 * User: fczyrnek
 * Date: 12.09.18
 * Time: 19:39
 */

namespace FiReLibs\Container\Examples;


class Factory
{
    /**
     * @var string Constructor parameter
     */
    protected $constructorParam="";
    /**
     * @var int Numeric parameter
     */
    protected $numericParam = 0;


    public function __construct(string $constructorParam)
    {
        $this->constructorParam = $constructorParam;
    }

    /**
     * Returns constructor parameter value
     *
     * @return string
     */
    public function getConstructorParam(){
        return $this->constructorParam;
    }

    /**
     * Return numeric parameter
     * @return int
     */
    public function getNumericParam()
    {
        return $this->numericParam;
    }

    /**
     * Set numeric parameter
     *
     * @param int $numericParam
     */
    public function setNumericParam(int $numericParam)
    {
        $this->numericParam = $numericParam;
    }

}