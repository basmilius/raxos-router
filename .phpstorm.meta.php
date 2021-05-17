<?php

namespace PHPSTORM_META {

    override(\Raxos\Router\Controller\ControllerContainer::get(), map([
        '' => '@'
    ]));

    override(\Raxos\Router\Router::getParameter(), map([
        '' => '@',
        'request' => \Raxos\Http\HttpRequest::class
    ]));

}
