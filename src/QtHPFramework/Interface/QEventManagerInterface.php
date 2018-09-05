<?php

interface QEventManagerInterface {
    public function attach($event, $cb);
    public function detach($event);
    public function detachAll();
    public function fire($event, $params = array());
}

?>