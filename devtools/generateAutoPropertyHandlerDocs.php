<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/*
 * This tool must be executed periodically. Run it every time a response or
 * model changes or is added.
 *
 * It automatically builds up-to-date class documentation for all classes that
 * derive from AutoPropertyHandler, and documents their getters, setters and
 * is-ers via adding "@method" declarations to the PHPdoc. That documentation is
 * necessary for things like code analysis tools and IDE autocomplete!
 *
 * Tip: Use the "--validate-only" param to check code without writing to disk.
 * A non-zero exit code will indicate that some files need new class docs.
 */

$opts = getopt('', ['validate-only']);
$isValidateRun = isset($opts['validate-only']);

$autodoc = new autodoc(__DIR__.'/../src/', !$isValidateRun);
$badFiles = $autodoc->run();

if ($isValidateRun && count($badFiles) > 0) {
    printf("The following %d files need updated class method documentation:\n", count($badFiles));
    foreach ($badFiles as $file) {
        printf("- \"%s\"\n", $file);
    }

    // Exit with non-zero code to signal that there are problems.
    exit(1);
}

class autodoc
{
    /**
     * @var string
     */
    private $_dir;

    /**
     * @var bool
     */
    private $_writeFiles;

    /**
     * Constructor.
     *
     * @param string $dir        Directory to process.
     * @param bool   $writeFiles If FALSE, only checks if existing docs are ok.
     */
    public function __construct(
        $dir,
        $writeFiles = true)
    {
        $this->_dir = realpath($dir);
        if ($this->_dir === false) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid path.', $dir));
        }
        $this->_writeFiles = $writeFiles;
    }

    /**
     * Convert file path to class name.
     *
     * @param string $filePath
     *
     * @return string
     */
    private function _extractClassName(
        $filePath)
    {
        return '\InstagramAPI'.str_replace('/', '\\', substr($filePath, strlen($this->_dir), -4));
    }

    /**
     * Extract property type from its PHPDoc.
     *
     * @param ReflectionProperty $property
     *
     * @return string
     */
    private function _getType(
        ReflectionProperty $property)
    {
        $phpDoc = $property->getDocComment();
        if ($phpDoc === false || !preg_match('#@var\s+([^\s]+)#i', $phpDoc, $matches)) {
            $type = 'mixed';
        } else {
            $type = $matches[1];
        }

        return $type;
    }

    /**
     * Converts underscores to camel cases.
     *
     * @param string $property
     *
     * @return string
     */
    private function _camelCase(
        $property)
    {
        // Trim any leading underscores and save their count, because it's a special case.
        $result = ltrim($property, '_');
        $leadingUnderscores = strlen($property) - strlen($result);
        if (strlen($result)) {
            // Convert all chars prefixed with underscore to upper case.
            $result = preg_replace_callback('#_([^_])#', function ($matches) {
                return strtoupper($matches[1]);
            }, $result);
            // Convert fist char to upper case.
            $result[0] = strtoupper($result[0]);
        }
        // Restore leading underscores (if any).
        if ($leadingUnderscores) {
            $result = str_pad($result, strlen($result) + $leadingUnderscores, '_', STR_PAD_LEFT);
        }

        return $result;
    }

    /**
     * Generate PHPDoc for all magic methods.
     *
     * @param ReflectionClass $reflection
     *
     * @return string
     */
    private function _documentMagicMethods(
        ReflectionClass $reflection)
    {
        $result = [];
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $parent = $reflection->getParentClass();
        foreach ($properties as $property) {
            // Skip properties available in parent class.
            if ($parent->hasProperty($property->getName())) {
                continue;
            }
            // Determine property type.
            $type = $this->_getType($property);
            // Normalize property name.
            $name = $this->_camelCase($property->getName());
            // getPropertyName() method.
            $getter = 'get'.$name;
            if (!$reflection->hasMethod($getter)) {
                $result[$getter] = sprintf(' * @method %s %s()', $type, $getter);
            }
            // isPropertyName() method.
            $iser = 'is'.$name;
            if (!$reflection->hasMethod($iser)) {
                $result[$iser] = sprintf(' * @method bool %s()', $iser);
            }
            // setPropertyName() method.
            $setter = 'set'.$name;
            if (!$reflection->hasMethod($setter)) {
                $result[$setter] = sprintf(' * @method %s(%s $value)', $setter, $type);
            }
        }
        // Reorder methods by name.
        ksort($result);

        return implode("\n", $result);
    }

    /**
     * Normalize and remove all "@method" lines from PHPDoc.
     *
     * @param string $doc
     *
     * @return string
     */
    private function _cleanClassDoc(
        $doc)
    {
        // Strip leading /** and trailing */ from PHPDoc.
        $doc = trim(substr($doc, 3, -2));
        $result = [];
        $lines = explode("\n", $doc);
        foreach ($lines as $line) {
            // Normalize line.
            $line = rtrim(' * '.ltrim(ltrim(trim($line), '*')));
            // Skip all existing methods.
            if (strncmp($line, ' * @method ', 11) === 0) {
                continue;
            }
            $result[] = $line;
        }
        // Skip all padding lines at the end.
        for ($i = count($result) - 1; $i >= 0; --$i) {
            if ($result[$i] === ' *') {
                unset($result[$i]);
            } else {
                break;
            }
        }

        return implode("\n", $result);
    }

    /**
     * Process single file.
     *
     * @param string          $filePath
     * @param ReflectionClass $reflection
     *
     * @return bool TRUE if the file needed new docs, otherwise FALSE.
     */
    private function _processFile(
        $filePath,
        ReflectionClass $reflection)
    {
        $needsNewDocs = false;
        $classDoc = $reflection->getDocComment();
        $methods = $this->_documentMagicMethods($reflection);
        if ($classDoc === false && strlen($methods)) {
            // We have no PHPDoc, but we found methods, so add new docs.
            $needsNewDocs = true;
            if ($this->_writeFiles) {
                $input = file($filePath);
                $startLine = $reflection->getStartLine();
                $output = implode('', array_slice($input, 0, $startLine - 1))
                        ."/**\n"
                        .$methods
                        ."\n */\n"
                        .implode('', array_slice($input, $startLine - 1));
                file_put_contents($filePath, $output);
            }
        } elseif ($classDoc !== false) {
            // We already have PHPDoc, so let's merge into it.
            $existing = $this->_cleanClassDoc($classDoc);
            if (strlen($existing) || strlen($methods)) {
                $output = "/**\n"
                    .(strlen($existing) ? $existing."\n" : '')
                    .((strlen($existing) && strlen($methods)) ? " *\n" : '')
                    .(strlen($methods) ? $methods."\n" : '')
                    ." */\n";
            } else {
                $output = '';
            }
            // Only write the new contents to disk if the docs have changed.
            if ($output !== $classDoc."\n") {
                $needsNewDocs = true;
                if ($this->_writeFiles) {
                    $contents = file_get_contents($filePath);
                    // Replace only first occurence (we append \n to the search
                    // string to be able to remove empty PHPDoc).
                    $contents = preg_replace('#'.preg_quote($classDoc."\n", '#').'#', $output, $contents, 1);
                    file_put_contents($filePath, $contents);
                }
            }
        }

        return $needsNewDocs;
    }

    /**
     * Process all *.php files in given path.
     *
     * @return string[] An array with all files that needed new docs.
     */
    public function run()
    {
        $directoryIterator = new RecursiveDirectoryIterator($this->_dir);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);
        $phpIterator = new RegexIterator($recursiveIterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        $filesWithProblems = [];
        foreach ($phpIterator as $filePath => $dummy) {
            $reflection = new ReflectionClass($this->_extractClassName($filePath));
            if ($reflection->isSubclassOf('\InstagramAPI\AutoPropertyHandler')) {
                $hasProblems = $this->_processFile($filePath, $reflection);
                if ($hasProblems) {
                    $filesWithProblems[] = $filePath;
                }
            }
        }

        return $filesWithProblems;
    }
}
