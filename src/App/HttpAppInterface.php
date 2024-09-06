<?php
namespace Concept\Http\App;

interface HttpAppInterface
{
    const CONFIG_NODE = 'http';

    public function run(): void;
}