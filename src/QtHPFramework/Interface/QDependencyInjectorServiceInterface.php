<?php

interface QDependencyInjectorServiceInterface {
    public function __construct($name, $def, $shared);
    public function getDefinition();
    public function getName();
    public function isShared();
    public function resolve();
    public function setDefinition($def);
    public function setShared($shared);
}

?>