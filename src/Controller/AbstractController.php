<?php

namespace App\Controller;

use React\Http\Message\Response;
use React\Http\Message\Request;

/** Abstract Controller to Handle Controller Typing in System */
class AbstractController
{
    public function __invoke(?Request $request) : ?Response
    {
        throw new \Exception("Abstract invokation, The class :" . get_class() . ' has no __invoke method implemented');
    }
}