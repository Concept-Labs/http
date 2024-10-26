<?php
namespace Concept\Http\Router\Route;

interface RouteFactoryInterface
{
    public function create(): RouteInterface;
}