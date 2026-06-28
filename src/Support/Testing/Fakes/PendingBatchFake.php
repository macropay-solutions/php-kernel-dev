<?php

namespace MacropaySolutions\KernelDev\Support\Testing\Fakes;

use MacropaySolutions\Kernel\Bus\PendingBatch;
use MacropaySolutions\Kernel\Support\Collection;
use MacropaySolutions\KernelDev\Support\Testing\Fakes\BusFake;

class PendingBatchFake extends PendingBatch
{
    /**
     * The fake bus instance.
     *
     * @var \MacropaySolutions\KernelDev\Support\Testing\Fakes\BusFake
     */
    protected $bus;

    /**
     * Create a new pending batch instance.
     *
     * @param \MacropaySolutions\KernelDev\Support\Testing\Fakes\BusFake $bus
     * @param \MacropaySolutions\Kernel\Support\Collection $jobs
     * @return void
     */
    public function __construct(BusFake $bus, Collection $jobs)
    {
        $this->bus = $bus;
        $this->jobs = $jobs;
    }

    /**
     * Dispatch the batch.
     *
     * @return \MacropaySolutions\Kernel\Bus\Batch
     */
    public function dispatch()
    {
        return $this->bus->recordPendingBatch($this);
    }

    /**
     * Dispatch the batch after the response is sent to the browser.
     *
     * @return \MacropaySolutions\Kernel\Bus\Batch
     */
    public function dispatchAfterResponse()
    {
        return $this->bus->recordPendingBatch($this);
    }
}
