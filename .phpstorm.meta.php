<?php

namespace PHPSTORM_META {

    override(\Raxos\OldRouter\Controller\ControllerContainer::get(), map([
        '' => '@'
    ]));

    override(\Raxos\OldRouter\Router::getParameter(), map([
        '' => '@',
        'request' => \Raxos\Http\HttpRequest::class
    ]));

}
