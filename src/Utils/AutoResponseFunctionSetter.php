<?php

namespace InstagramAPI;

class AutoResponseFunctionSetter
{
    // CALL is invoked when attempting to access missing functions.
    // This allows us to auto-map setters and getters for properties.
    public function __call($function, $args)
    {
        // Parse the name of the function they tried to call.
        $underScoreNames = $this->camelCaseToUnderScore($function);
        if (strpos($underScoreNames, '_') === false) {
            throw new \Exception("Unknown function {$function}.");
        }
        list($functionType, $propName) = explode('_', $underScoreNames, 2);

        // Return the kind of response expected by their function.
        switch ($functionType) {
            case 'get':
                if (!property_exists($this, $propName)) {
                    throw new \Exception("Unknown function {$function}.");
                }

                return $this->$propName;
                break;
            case 'set':
                if (!property_exists($this, $propName)) {
                    throw new \Exception("Unknown function {$function}.");
                }

                $this->$propName = $args[0];
                break;
            case 'is':
                if (!property_exists($this, $propName)) {
                    throw new \Exception("Unknown function {$function}.");
                }

                return ($this->$propName ? true : false);
                break;
        }
    }

    public function camelCaseToUnderScore($input)
    {
        // This is a highly optimized regexp which achieves the matching in very few steps. Do not touch!
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }
}
