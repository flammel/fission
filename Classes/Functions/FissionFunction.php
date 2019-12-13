<?php

namespace Flammel\Fission\Functions;

use Flammel\Fission\Exception\FissionException;

interface FissionFunction
{
    /**
     * @param mixed ...$args
     * @return mixed|void
     * @throws FissionException
     */
    public function invoke(...$args);
}
