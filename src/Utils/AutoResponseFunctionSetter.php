<?php

namespace InstagramAPI;

class AutoResponseFunctionSetter
{
    public function __call($function, $args)
    {
        $underScoreNames = $this->camelCaseToUnderScore($function);
        if (strpos($underScoreNames, '_') === false) {
            return false;
        }
        list($functionType, $propName) = explode('_', $underScoreNames, 2);

        switch ($functionType) {
            case 'get':
                if (!property_exists($this, $propName)) {
                    throw new \Exception("Wrong function $function");
                }

                return $this->$propName;
                break;
            case 'set':
                if (!property_exists($this, $propName)) {
                    throw new \Exception("Wrong function $function");
                }
                $this->$propName = $args[0];
                break;
            case 'is':
                if (!property_exists($this, $underScoreNames)) {
                    throw new \Exception("Wrong function $function");
                }

                return $this->$underScoreNames;
                break;
        }
    }

    public function camelCaseToUnderScore($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }
}
