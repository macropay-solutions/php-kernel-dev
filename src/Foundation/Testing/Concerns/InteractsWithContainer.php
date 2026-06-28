<?php

namespace MacropaySolutions\KernelDev\Foundation\Testing\Concerns;

use Closure;
use MacropaySolutions\Kernel\Support\HtmlString;
use Mockery;

trait InteractsWithContainer
{
    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function swap($abstract, $instance)
    {
        return $this->instance($abstract, $instance);
    }

    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function instance($abstract, $instance)
    {
        $this->app->instance($abstract, $instance);

        return $instance;
    }

    /**
     * Mock an instance of an object in the container.
     *
     * @param string $abstract
     * @param \Closure|null $mock
     * @return \Mockery\MockInterface
     */
    protected function mock($abstract, ?Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }

    /**
     * Mock a partial instance of an object in the container.
     *
     * @param string $abstract
     * @param \Closure|null $mock
     * @return \Mockery\MockInterface
     */
    protected function partialMock($abstract, ?Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args()))->makePartial());
    }

    /**
     * Spy an instance of an object in the container.
     *
     * @param string $abstract
     * @param \Closure|null $mock
     * @return \Mockery\MockInterface
     */
    protected function spy($abstract, ?Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::spy(...array_filter(func_get_args())));
    }

    /**
     * Instruct the container to forget a previously mocked / spied instance of an object.
     *
     * @param string $abstract
     * @return $this
     */
    protected function forgetMock($abstract)
    {
        $this->app->forgetInstance($abstract);

        return $this;
    }
}
