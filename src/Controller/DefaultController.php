<?php

namespace App\Controller;

use App\Controller\AbstractController;
use React\Http\Message\Response;

/** Simple Hello World Controller to greet main page URL */
class DefaultController extends AbstractController
{
    public function index() : Response {
        return Response::plaintext(
            "Hello wörld!\n"
        );
    }

    public function favicon() : Response {
        $image = hex2bin('89504e470d0a1a0a0000000d494844520000000100000001010300000025db56ca00000003504c5445000000a77a3dda0000000174524e530040e6d8660000000a4944415408d76360000000020001e221bc330000000049454e44ae426082');
        // or base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');

        $response = new Response(
            Response::STATUS_OK,
            [
                'Content-Type' => 'image/png'
            ],
            $image
        );

        return $response;
    }
}