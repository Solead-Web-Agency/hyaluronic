<?php

namespace Vivawallet\VivawalletPhp\Http\Authentication;

interface Authentication
{
    public function getHeader();

    public function getEnvironment();
}
