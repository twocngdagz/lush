<?php

namespace App\Traits;

trait UsesModelIdentifier {

    /**
     * Boot the trait and add model observers
     *
     * @return void
     **/
    public static function bootUsesModelIdentifier(): void
    {
        // Automatically create and add the identifier
        // to the model when it's being created
        static::creating(function($model){
            $model->identifier = $model::createModelIdentifier();
        });
    }

    /**
     * Search for a record based on the identifier
     *
     * @return self
     **/
    public static function identifier($identifier): self
    {
        return self::where('identifier', $identifier)->firstOrFail();
    }

    /**
     * Create a unique identifier for the model
     *
     * @return string
     **/
    public static function createModelIdentifier(): string
    {
        $identifier = [];
        for($i=0; $i < 4; $i++) {
            $identifier[] = self::randomString();
        }

        return implode('-', $identifier);
    }

    /**
     * Generate a random string of various lengths
     *
     * @return string
     **/
    public static function randomString($length = 4): string
    {
        $chr = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = [];
        for ($i = 0; $i < $length; $i++) {
            $result[] = $chr[rand(0, strlen($chr) - 1)];
        }
        return implode('', $result);
    }
}
