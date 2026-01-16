<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

// Kernel is the main class of the application it is used to configure the application
// MicroKernelTrait is a trait that provides a way to configure the application
// BaseKernel is the base class of the application it is used to configure the application
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
