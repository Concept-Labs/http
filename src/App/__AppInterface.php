<?php
namespace Concept\Http\App;

interface AppInterface
{
    const CONFIG_NODE = 'app';

    public function run(): void;
}