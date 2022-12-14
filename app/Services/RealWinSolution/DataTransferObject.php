<?php

namespace App\Services\RealWinSolution;

use ReflectionProperty;

abstract class DataTransferObject
{
    public function __construct(array $parameters = [])
    {
        $class = new \ReflectionClass(static::class);
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty){
            $property = $reflectionProperty->getName();
            if ($this->{$property}=== null) {
                $this->{$property} = $parameters[$property];
            }
        }
    }
}
