<?php

namespace InstagramAPI;

/*
 * This new class is "perfect" apart from a huge flaw. ;-)
 *
 * WARNING/TODO/FIXME: Because all objects are lazy-loaded and generated every
 * time you use a getter, there's absolutely NO link between sub-objects and
 * their parent objects. So you cannot do $foo->getBar()->setXyz('x'), since the
 * "bar" object has no relation to the "$foo" object. We should fix that by
 * thinking of a solution, such as the one described here:
 * https://github.com/mgp25/Instagram-API/pull/1534#issuecomment-331674820
 *
 * We don't seem to do that in our code (ag -s "get.+set"), but users may be doing it.
 *
 * TODO LIST BEFORE THIS CLASS IS SAFE TO USE:
 *
 * - Implement a setter-solution (a cache of resolved objects that are always
 *   re-used when set/get is used? make all sub-objects references to a branch
 *   of the root's _jsonData? make all functions modify their own _jsonData AND
 *   that of their parent all the way up the stack?) to solve the problem above
 *   somehow...
 * - Manually convert all classes to this new base-class, and rewrite any custom
 *   setters/getters of those classes to use the new property access functions.
 * - Make a copy of "generateAutoPropertyHandlerDocs.php" named
 *   "generateAutoJsonMapperDocs.php", but still keep the old one in case we use
 *   the old AutoPropertyHandler in the future for anything.
 *
 */

/**
 * Automatic JSON object mapper and method/property handler.
 *
 * By deriving from this base object, it will automatically create virtual
 * "getX()", "setX()" and "isX()" functions for all of your JSON properties,
 * as well as also supporting direct "$x->foo" property access (with support
 * for setting, getting, isset(), empty() and unset() on any property).
 *
 * All JSON parsing by this class is optimized for high performance and low
 * memory usage, and all object/variable conversions are done lazily on a
 * property-basis when that property is accessed, instead of wasting time
 * converting the whole object recursively (like the normal JsonMapper). And
 * unlike the norma JsonMapper, there's no need for any runtime reflection,
 * reflection caches, duplicated value-caches, etc.
 *
 * This class is intended to handle Instagram's server responses, so all of your
 * JSON properties must be named the same way as Instagram's standardized var
 * format, which is "some_value". That object property can then be magically
 * accessed via "getSomeValue()", "setSomeValue()" and "isSomeValue()".
 *
 * Examples (normal lowercase properties separated by underscores):
 * - "location" = getLocation(); isLocation(); setLocation(); $x->location;
 * - "is_valid" = getIsValid(); isIsValid(); setIsValid(); $x->is_valid;
 *
 * Examples (rare properties with a leading underscore):
 * - "_messages" = get_Messages(); is_Messages(); set_Messages(); $x->_messages;
 * - "_the_url" = get_TheUrl(); is_TheUrl(); set_TheUrl(); $x->_the_url;
 *
 * Examples (rare camelcase properties):
 * - "iTunesItem" = getITunesItem(); isITunesItem(); setITunesItem(); $x->iTunesItem;
 * - "linkType" = getLinkType(); isLinkType(); setLinkType(); $x->linkType;
 *
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class AutoJsonMapper
{
    /**
     * Whether we should cache all magic method translations.
     *
     * This costs a bit of RAM, but greatly speeds up magic function calls so
     * that they only take about 16% as long as they would without the cache.
     *
     * Tip: If you have very limited memory, you can easily disable this cache:
     * \InstagramAPI\AutoJsonMapper::$useCache = false;
     *
     * @var bool
     */
    public static $useCache = true;

    /**
     * Magic method lookup cache.
     *
     * Globally shared across all instances of AutoJsonMapper classes.
     *
     * @var array
     */
    protected static $_lookupCache = [];

    /**
     * Tells us how to map JSON properties to internal types or objects.
     *
     * This is the only variable that sub-classes must override. (However,
     * subclasses are of course welcome to override functions too. Especially
     * if you want custom handling inside certain setters/getters.)
     *
     * The first value is the type, and the second is whether the JSON key
     * is an array of that type or a single instance of that type.
     *
     * Note that if the type is set to an empty string (''), there won't be any
     * automatic type-conversion or type-checking of that property.
     *
     * When mapping to objects, those objects MUST inherit from AutoJsonMapper.
     * In the example definition below, "YourObject" extends AutoJsonMapper.
     *
     * Example:
     * [
     *     'some_string'      => ['string', false],
     *     'an_object'        => ['\YourProject\YourObject', false],
     *     'array_of_numbers' => ['int', true],
     *     'array_of_objects' => ['\YourProject\YourObject', true],
     *     'untyped_value'    => ['', false],
     * ]
     *
     * @var array
     */
    protected static $_jsonProperties = [];

    /**
     * The raw, internal JSON data array.
     *
     * @var array
     */
    protected $_jsonData;

    /**
     * Constructor.
     *
     * @param array $jsonData        Decoded JSON data as an array (NOT as an object).
     * @param bool  $requireAnalysis Whether to throw an exception if any of the raw
     *                               JSON properties aren't defined in the class hierarchy,
     *                               or if any of the encountered classes are bad/missing.
     *                               Useful for debugging when creating/updating classes.
     *
     * @throws \RuntimeException If definitions are required, and any definition is missing.
     */
    public function __construct(
        array $jsonData = [],
        $requireAnalysis = false)
    {
        $this->_jsonData = $jsonData;

        // Recursively look for any missing JSON properties, if scan requested.
        if ($requireAnalysis) {
            $result = $this->getClassAnalysis();
            $strChunks = [];
            if (count($result['missing_definitions'])) {
                // Build a nice string containing all missing class properties.
                $strSubChunks = [];
                foreach ($result['missing_definitions'] as $className => $properties) {
                    $strSubChunks[] = sprintf('"%s": ("%s")', $className, implode('", "', $properties));
                }
                $strChunks[] = sprintf('Missing JSON property definitions in %s.', implode(', and in ', $strSubChunks));
            }
            if (count($result['bad_classes'])) {
                // Build a nice string containing all encountered bad class msgs.
                $strChunks[] = sprintf('Bad object classes encountered: ["%s"].', implode('"], and ["', $result['bad_classes']));
            }
            if (count($strChunks)) {
                throw new \RuntimeException(implode(' ', $strChunks));
            }
        }
    }

    /**
     * Get the raw, internal JSON data array.
     *
     * @return array
     */
    public function asJson()
    {
        return $this->_jsonData;
    }

    /**
     * Print the contents of the entire JSON data array.
     */
    public function printJson()
    {
        var_dump($this->asJson());
    }

    /**
     * Analyze the entire object and check for undefined or bad JSON properties.
     *
     * This lets you analyze an object hierarchy to check for undefined JSON
     * fields that need to be defined in your classes (to become interactive).
     *
     * It also checks all of the other encountered classes within the object, to
     * ensure that they construct successfully and inherit from AutoJsonMapper.
     *
     * @param bool $recursiveScan Whether to also include missing properties
     *                            in sub-objects in the analysis. Recommended.
     *
     * @return array Array with keys for "missing_definitions" (an array of
     *               class names and what JSON properties they're missing)
     *               and "bad_classes" (classes that are badly coded or missing).
     */
    public function getClassAnalysis(
        $recursiveScan = true)
    {
        $result = [
            // Missing JSON property definitions.
            'missing_definitions' => [],
            // Bad classes that failed to construct or aren't based on AutoJsonMapper.
            'bad_classes'         => [],
        ];

        // We need to recursively check the entire tree to ensure that all
        // classes have defined all properties in the raw JSON data.
        foreach ($this->_jsonData as $name => $value) {
            try {
                // Check if the field is defined and get its type definition.
                list($type, $isArray) = $this->_getFieldDefinition($name);
                $isObjectType = ($type !== '' && $type[0] === '\\');

                // Recursively check all internal objects to make sure they
                // also have all properties from the raw JSON data.
                if ($recursiveScan && $isObjectType) {
                    try {
                        $value = $this->_getField($name, null);
                        if ($value !== null) {
                            if (!$isArray) {
                                $innerResult = $value->getClassAnalysis();
                            } else {
                                $innerResult = [];
                                foreach ($value as $innerObject) {
                                    $innerResult = array_merge_recursive(
                                        $innerResult, $innerObject->getClassAnalysis()
                                    );
                                }
                            }
                            $result = array_merge_recursive($result, $innerResult);
                        }
                    } catch (\Exception $e) {
                        // Unable to get the value of this field... which
                        // usually means the field's class is missing or bad,
                        // or that the field cannot be type-coerced as requested.
                        // We'll save the exception details message as-is...
                        $result['bad_classes'][] = $e->getMessage();
                    }
                }
            } catch (\Exception $e) {
                // We lack a definition for this property field.
                $owner = get_class($this);
                if (!array_key_exists($owner, $result['missing_definitions'])) {
                    $result['missing_definitions'][$owner] = [];
                }
                $result['missing_definitions'][$owner][] = $name;
            }
        }

        // Convert the inner arrays to sorted lists of missing properties.
        foreach ($result['missing_definitions'] as $owner => &$missingKeys) {
            $missingKeys = array_unique($missingKeys);
            natcasesort($missingKeys);
            $result['missing_definitions'][$owner] = $missingKeys;
        }

        // Sort the outer array (the class names).
        ksort($result['missing_definitions'], SORT_NATURAL | SORT_FLAG_CASE);

        // Get rid of duplicate bad_classes messages and sort them all.
        $result['bad_classes'] = array_unique($result['bad_classes']);
        natcasesort($result['bad_classes']);

        return $result;
    }

    /**
     * Checks if a field definition exists.
     *
     * @param string $name The property name.
     *
     * @return bool
     */
    protected function _hasFieldDefinition(
        $name)
    {
        return isset(static::$_jsonProperties[$name]);
    }

    /**
     * Get the field definition of a JSON property.
     *
     * @param string $name The property name.
     *
     * @throws \RuntimeException If the property isn't defined in the class.
     *
     * @return array A two-element array ['typeName', bool isArray].
     */
    protected function _getFieldDefinition(
        $name)
    {
        if (!$this->_hasFieldDefinition($name)) {
            throw new \RuntimeException(sprintf('No such JSON property "%s".', $name));
        }

        return static::$_jsonProperties[$name];
    }

    /**
     * Convert a raw JSON value to the requested class or built-in PHP type.
     *
     * If the value is a literal NULL or no type-conversion is assigned, then it
     * will be returned as-is instead of being converted to the requested type.
     *
     * @param string $name         The name of the property. For exception messages.
     * @param string $type         Requested output type for the value.
     * @param bool   $isObjectType Whether the type is a class object or a built-in type.
     * @param mixed  $value        The current, raw JSON value.
     *
     * @throws \RuntimeException If the value can't be turned into the
     *                           requested class or built-in PHP type.
     *
     * @return mixed The value as the requested type.
     */
    protected function _convertValueFromJson(
        $name,
        $type,
        $isObjectType,
        $value)
    {
        // Do nothing if the value is NULL or if no type conversion requested.
        if ($value === null || $type === '') {
            return $value;
        }

        if (!$isObjectType) {
            if (is_array($value)) { // Only Objects can have an array as their JSON value.
                throw new \RuntimeException(sprintf('Unable to convert JSON array value to non-Object property "%s".', $name));
            }

            // Cast the value to the target built-in PHP type. We cannot cast objects.
            if (is_object($value) || !@settype($value, $type)) {
                throw new \RuntimeException(sprintf('Unable to cast value of "%s" to built-in PHP type "%s".', $name, $type));
            }
        } else {
            if (!is_array($value)) { // Objects must have an array as their JSON value.
                throw new \RuntimeException(sprintf('Unable to convert JSON non-array value to Object property "%s".', $name));
            }

            // Validate that the class exists (uses autoloader to find it).
            // NOTE: This is necessary because PHP < 7 can't catch the fatal
            // error about trying to create a non-existent class. So we must
            // check its existence first.
            if (!class_exists($type)) {
                throw new \RuntimeException(sprintf('Class "%s" not found for property "%s".', $type, $name));
            }

            // Convert the value to the requested object and verify its validity.
            try {
                // Attempt to create the class instance. We don't know if it's a
                // valid AutoJsonMapper-derived class yet. So we'll catch any
                // constructor issues.
                $value = new $type($value);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf('Failed to create an instance of class "%s": "%s".', $type, $e->getMessage()));
            } catch (\Error $e) { // Only works in PHP 7+.
                throw new \RuntimeException(sprintf('Failed to create an instance of class "%s": "%s".', $type, $e->getMessage()));
            }

            // Validate that the class is correctly derived from AutoJsonMapper.
            if (!$value instanceof self) {
                throw new \RuntimeException(sprintf('Sub-class "%s" is not an instance of AutoJsonMapper.', $type));
            }
        }

        return $value;
    }

    /**
     * Convert a class object or a built-in PHP type to a raw JSON value.
     *
     * If the value is a literal NULL or no type-conversion is assigned, then it
     * will be returned as-is instead of being converted to the requested type.
     *
     * @param string $name         The name of the property. For exception messages.
     * @param string $type         Expected input type for the value.
     * @param bool   $isObjectType Whether the type is a class object or a built-in type.
     * @param mixed  $value        The input value to be converted to raw JSON.
     *
     * @throws \RuntimeException If the value can't be turned into the
     *                           a JSON data array type.
     *
     * @return mixed The value as its final JSON data array type.
     */
    protected function _convertValueToJson(
        $name,
        $type,
        $isObjectType,
        $value)
    {
        // Do nothing if the value is NULL or if no type conversion requested.
        if ($value === null || $type === '') {
            return $value;
        }

        // This function is for converting individual values. We don't handle arrays.
        if (is_array($value)) {
            throw new \RuntimeException(sprintf('Unexpected array value when converting "%s" to non-array type "%s".', $name, $type));
        }

        if (!$isObjectType) {
            // Cast non-object value to the target built-in PHP type. We cannot cast objects.
            if (is_object($value) || !@settype($value, $type)) {
                throw new \RuntimeException(sprintf('Unable to cast new value for "%s" to built-in PHP type "%s".', $name, $type));
            }
        } else {
            // Check that the object is an instance of the exact required class,
            // or that it's at least a subclass of that class. Warn about this 1st.
            if (!is_a($value, $type)) {
                throw new \RuntimeException(sprintf('The value for property "%s" must be an instance of class "%s".', $name, $type));
            }

            // Ensure that the object is legal (derived from AutoJsonMapper).
            if (!$value instanceof self) {
                throw new \RuntimeException(sprintf('The value for property "%s" must be an Object-instance of an AutoJsonMapper class.', $name));
            }

            // Use the pure, raw JSON array from the AutoJsonMapper object.
            $value = $value->asJson();
        }

        return $value;
    }

    /**
     * Get the value of a field, if a value exists in the JSON data.
     *
     * This function automatically reads the raw JSON data and converts the
     * value to the correct type on-the-fly.
     *
     * NOTE: If the loaded JSON data doesn't contain that field or it contains a
     * literal NULL value, it will be treated as missing and the $default value
     * will be returned instead.
     *
     * @param string $name    The property name.
     * @param mixed  $default What to return if the property is NULL or missing from the JSON data.
     *
     * @throws \RuntimeException If the property isn't defined in the class,
     *                           or if the value can't be turned into the
     *                           requested class or built-in PHP type.
     *
     * @return mixed The value as the correct type, or $default if no value exists.
     */
    protected function _getField(
        $name,
        $default = null)
    {
        // Check if the field is valid and get its type definition.
        list($type, $isArray) = $this->_getFieldDefinition($name);
        $isObjectType = ($type !== '' && $type[0] === '\\');

        // Handle JSON values that don't exist in the data or are literally NULL.
        if (!isset($this->_jsonData[$name])) {
            return $default;
        }

        // Map the value to the appropriate type.
        $value = $this->_jsonData[$name];
        if (!$isArray) {
            return $this->_convertValueFromJson($name, $type, $isObjectType, $value);
        } else {
            if (!is_array($value)) {
                throw new \RuntimeException(sprintf('Unable to convert JSON non-array value to array property "%s".', $name));
            }

            $items = []; // Create a new, temporary array to hold the converted items.
            foreach ($value as $k => $v) {
                $items[$k] = $this->_convertValueFromJson($name, $type, $isObjectType, $v);
            }

            return $items;
        }
    }

    /**
     * Checks if a JSON value exists and evaluates to true.
     *
     * @param string $name The property name.
     *
     * @throws \RuntimeException If the property isn't defined in the class.
     *
     * @return bool
     */
    protected function _isField(
        $name)
    {
        // Check if the field is valid and get its type definition.
        list($type, $isArray) = $this->_getFieldDefinition($name);
        $isObjectType = ($type !== '' && $type[0] === '\\');

        // Check if the field value evaluates to true.
        return isset($this->_jsonData[$name]) && (bool) $this->_jsonData[$name];
    }

    /**
     * Set a JSON property to a new value.
     *
     * @param string $name  The property name.
     * @param mixed  $value The new value for the property. NULL is always allowed.
     *
     * @throws \RuntimeException If the property isn't defined in the class,
     *                           or if the new value isn't legal for that property.
     *
     * @return $this
     */
    protected function _setField(
        $name,
        $value)
    {
        // Check if the field is valid and get its type definition.
        list($type, $isArray) = $this->_getFieldDefinition($name);
        $isObjectType = ($type !== '' && $type[0] === '\\');

        // Only perform value-conversion if the new value is non-NULL.
        if ($value !== null) {
            if (!$isArray) {
                if (is_array($value)) { // Safe, since real objects (via intended setter) are never arrays.
                    throw new \RuntimeException(sprintf('Unable to assign new array value for non-array property "%s".', $name));
                }

                $value = $this->_convertValueToJson($name, $type, $isObjectType, $value);
            } else { // This is an array-property.
                if (!is_array($value)) {
                    throw new \RuntimeException(sprintf('Unable to assign new non-array value for array-property "%s".', $name));
                }

                // Recursively convert and validate all array elements.
                // NOTE: This is VITAL in case it's an array of Objects, which
                // we MUST convert before storing in our internal JSON array.
                foreach ($value as $k => &$v) {
                    $v = $this->_convertValueToJson($name, $type, $isObjectType, $v);
                }
            }
        }

        // Assign the new value for the property.
        $this->_jsonData[$name] = $value;

        return $this;
    }

    /**
     * __CALL is invoked when attempting to access missing functions.
     *
     * This handler auto-maps setters, is-ers and getters for object properties.
     *
     * @param string $functionName Name of the method being called.
     * @param array  $arguments    Array of arguments passed to the method.
     *
     * @throws \RuntimeException If the function type or property name is invalid, or if
     *                           there's any problem with the conversion to/from the JSON value.
     *
     * @return mixed
     *
     * @see http://php.net/manual/en/language.oop5.magic.php
     */
    public function __call(
        $functionName,
        $arguments)
    {
        if (self::$useCache && isset(self::$_lookupCache[$functionName])) {
            // Read the processed result from the lookup cache.
            list($functionType, $propertyName, $camelPropertyName) = self::$_lookupCache[$functionName];
        } else {
            // Extract the components of the function they tried to call.
            $chunks = self::_explodeCamelCase($functionName);
            if ($chunks === false || count($chunks) < 2) {
                throw new \RuntimeException(sprintf('Unknown function "%s".', $functionName));
            }

            // Determine the type (such as "get") and the property (ie "is_valid").
            $functionType = array_shift($chunks);
            $propertyName = implode('_', $chunks); // "is_valid"

            // Some objects have rare camelcase properties, so instead of naming it
            // "i_tunes_item" they have "iTunesItem" as the property.
            $camelPropertyName = null;
            if (($len = count($chunks)) >= 2) {
                // Make word 2 and higher start with uppercase ("i,Tunes,Item").
                // NOTE: Instagram's rule so far is that the first word is always
                // lowercase when they use camelcase.
                for ($i = 1; $i < $len; ++$i) {
                    $chunks[$i] = ucfirst($chunks[$i]);
                }
                $camelPropertyName = implode('', $chunks); // "iTunesItem"
            }

            // Store the processed result in the lookup cache, if enabled.
            if (self::$useCache && !isset(self::$_lookupCache[$functionName])) {
                self::$_lookupCache[$functionName] = [$functionType, $propertyName, $camelPropertyName];
            }
        }

        // Check for the existence of a camelcase property and use it if found.
        if ($camelPropertyName !== null && $this->_hasFieldDefinition($camelPropertyName)) {
            $propertyName = $camelPropertyName; // Use this name instead.
        }

        // Make sure the requested function has a corresponding object property.
        if (!$this->_hasFieldDefinition($propertyName)) {
            throw new \RuntimeException(sprintf('Unknown function "%s".', $functionName));
        }

        // Return the kind of response expected by their desired function.
        switch ($functionType) {
        case 'get':
            return $this->_getField($propertyName);
            break;
        case 'set':
            return $this->_setField($propertyName, $arguments[0]);
            break;
        case 'is':
            return $this->_isField($propertyName);
            break;
        default:
            // Unknown function type prefix...
            throw new \RuntimeException(sprintf('Unknown function "%s".', $functionName));
        }
    }

    /**
     * Explodes a string on camelcase boundaries.
     *
     * Examples:
     * - "getSome0XThing" => "get", "some0", "x", "thing".
     * - "getSome0xThing" => "get", "some0x", "thing".
     *
     * @param string $inputString
     *
     * @return string[]|bool Array with parts if successful, otherwise FALSE.
     */
    protected static function _explodeCamelCase(
        $inputString)
    {
        // Split the input into chunks on all camelcase boundaries.
        // NOTE: The input must be 2+ characters AND have at least one uppercase.
        $chunks = preg_split('/(?=[A-Z])/', $inputString, -1, PREG_SPLIT_NO_EMPTY);
        if ($chunks === false) {
            return false;
        }

        // Process all individual chunks and make them all completely lowercase.
        // NOTE: Since all chunks are split on camelcase boundaries above, it
        // means that each chunk ONLY holds a SINGLE fragment which can ONLY
        // contain at most a SINGLE capital letter (the chunk's first letter).
        foreach ($chunks as &$chunk) {
            $chunk = lcfirst($chunk); // Only first letter may be uppercase.
        }

        // If there are 2+ chunks and the first (the function type) ends with
        // trailing underscores, it means that they wanted to access a property
        // beginning with underscores, so move those to the start of the 2nd
        // ("property name") chunk instead.
        if (count($chunks) >= 2) {
            $oldLen = strlen($chunks[0]);
            $chunks[0] = rtrim($chunks[0], '_'); // "get_" => "get".
            $lenDiff = $oldLen - strlen($chunks[0]);
            if ($lenDiff > 0) {
                // Move all underscores to prop: "messages" => "_messages":
                $chunks[1] = str_repeat('_', $lenDiff).$chunks[1];
            }
        }

        return $chunks;
    }

    /**
     * __SET is invoked when writing data to inaccessible properties.
     *
     * @param string $propertyName
     * @param mixed  $value
     *
     * @throws \RuntimeException If the property isn't defined in the class,
     *                           or if the new value isn't legal for that property.
     */
    public function __set(
        $propertyName,
        $value)
    {
        $this->_setField($propertyName, $value); // PHP ignores the return-value of __set().
    }

    /**
     * __GET is invoked when reading data from inaccessible properties.
     *
     * @param string $propertyName
     *
     * @throws \RuntimeException If the property isn't defined in the class,
     *                           or if the value can't be turned into the
     *                           requested class or built-in PHP type.
     *
     * @return mixed
     */
    public function __get(
        $propertyName)
    {
        return $this->_getField($propertyName);
    }

    /**
     * __ISSET is invoked by calling isset() or empty() on inaccessible properties.
     *
     * @param string $propertyName
     *
     * @throws \RuntimeException If the property isn't defined in the class.
     *
     * @return bool TRUE if the property exists in the JSON data and is non-NULL.
     */
    public function __isset(
        $propertyName)
    {
        if (!$this->_hasFieldDefinition($propertyName)) {
            throw new \RuntimeException(sprintf('No such JSON property "%s".', $propertyName));
        }

        return isset($this->_jsonData[$propertyName]);
    }

    /**
     * __UNSET is invoked by calling unset() on inaccessible properties.
     *
     * @param string $propertyName
     *
     * @throws \RuntimeException If the property isn't defined in the class.
     */
    public function __unset(
        $propertyName)
    {
        if (!$this->_hasFieldDefinition($propertyName)) {
            throw new \RuntimeException(sprintf('No such JSON property "%s".', $propertyName));
        }

        unset($this->_jsonData[$propertyName]);
    }
}
