<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/12/2015
 * Time: 10:23 PM
 */

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