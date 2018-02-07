<?php

namespace Phalcon\Support;

use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\User\Component;

abstract class AbstractServiceProvider extends Component implements ServiceProviderInterface
{

    /**
     * AbstractServiceProvider constructor.
     *
     * @param DiInterface $di The Dependency Injector.
     */
    public function __construct(DiInterface $di)
    {
        $this->setDI($di);
    }



}
