<?php

class SessionMock {

    protected $session = array();

    public function has($key)
    {
        return isset($this->session[$key]);
    }

    public function get($key)
    {
        return (isset($this->session[$key])) ? $this->session[$key] : null;
    }

    public function put($key, $value)
    {
        $this->session[$key] = $value;
    }
}