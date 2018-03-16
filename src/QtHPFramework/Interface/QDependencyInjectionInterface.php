<?php

interface QDependencyInjectionInterface {
    public function setDi(QDependencyInjectorInterface $di);
    public function di();
}

?>