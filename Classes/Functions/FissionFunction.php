<?php

namespace Flammel\Fission\Functions;

use Flammel\Fission\Exception\FissionException;

interface FissionFunction
{
    /**
     * @param array $args
     * @return mixed|void
     * @throws FissionException
     */
    public function invoke(array $args);
}
