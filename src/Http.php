<?php

class Http
{
    public function httpged($var)
    {
        global $HTTP_GET_VARS;

        $res = isset($_GET[$var]) ? $_GET[$var] : false;
        if ($res === false) {
            $res = isset($HTTP_GET_VARS[$var]) ? $HTTP_GET_VARS[$var] : false;
        }
        return $res;
    }
}