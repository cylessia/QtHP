<?php

interface QDependencyInjectorInterface extends ArrayAccess {
    public function attempt($name, $def, $shared);
    public function get($name);
    public function getShared($name);
    public function has($name);
    public function remove($name);
    public function set($name, $def, $shared = false);
    public function setShared($name, $def);
}

?>