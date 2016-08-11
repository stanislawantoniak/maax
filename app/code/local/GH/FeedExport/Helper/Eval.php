<?php

/**
 * Class GH_FeedExport_Helper_Eval
 */
class GH_FeedExport_Helper_Eval extends Mirasvit_FeedExport_Helper_Eval
{

    /**
     * @param $value
     * @param $formatterLine
     * @return mixed|string
     */
    public function execute($value, $formatterLine)
    {
        $formatter = explode(' ', $formatterLine);
        $method = $formatter[0];

        array_shift($formatter);
        $args = $formatter;
        if (!is_array($args)) {
            $args = array();
        }

        if (function_exists($method)) {
            foreach ($args as $key => $arg) {
                if (!is_numeric($arg)) {
                    $args[$key] = "'".$arg."'";
                }
            }

            if($method == "str_replace"){
                $cmd = 'return '.$method.'(';

                if (count($args)) {
                    $cmd .= implode(',', $args).'';
                }
                $cmd .= ',"'.addslashes($value).'");';
            } else {
                $cmd = 'return '.$method.'("'.addslashes($value).'"';
                if (count($args)) {
                    $cmd .= ','.implode(',', $args).'';
                }
                $cmd .= ');';
            }
            $value = @eval($cmd);
        } elseif (method_exists($this, $method)) {
            array_unshift($args, $value);
            $value = call_user_func_array(array($this, $method), $args);
        } else {
            $value .= $formatterLine;
        }

        return $value;
    }

    /**
     * Custom formatter to encode url "query" part
     * @param $value
     * @return string
     */
    function url_encoded($value)
    {
        $parsed_url = parse_url($value);

        if (isset($parsed_url["query"])) {
            $parsed_url["query"] = urlencode($parsed_url["query"]);

            //build_url with encoded query
            $value = $this->build_url($parsed_url);
        }

        return $value;
    }


    function build_url(array $parsed_url) {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}