<?php

namespace MacropaySolutions\KernelDev\Database\Obvious\Factories;

/**
 * Use this only locally in your model! Do not commit its usage!
 */
trait HasFactory
{
    /**
     * Get a new factory instance for the model.
     *
     * @param callable|array|int|null $count
     * @param callable|array $state
     * @return \MacropaySolutions\KernelDev\Database\Obvious\Factories\Factory<static>
     */
    public static function factory($count = null, $state = [])
    {
        $factory = static::newFactory() ?: Factory::factoryForModel(get_called_class());

        return $factory
            ->count(is_numeric($count) ? $count : null)
            ->state(is_callable($count) || is_array($count) ? $count : $state);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \MacropaySolutions\KernelDev\Database\Obvious\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        //
    }
}
