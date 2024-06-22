<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class HelloWorldController
{
    /**
     * Index action.
     *
     * @return Response HTTP response
     */

    #[\Symfony\Component\Routing\Attribute\Route(
        '/hello/{name}',
        name: 'hello_index',
        requirements: ['name' => '[a-zA-Z]+'],
        defaults: ['name' => 'World'],
        methods: 'GET'
    )]
    public function index(string $name): Response
    {

        return new Response('Dobranoc '.$name.' <3');
    }
}