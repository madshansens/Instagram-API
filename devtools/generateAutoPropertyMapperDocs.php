<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/*
 * This tool must be executed periodically. Run it every time a response or
 * model changes or is added.
 *
 * It automatically builds up-to-date class documentation for all classes that
 * derive from AutoPropertyMapper, and documents their getters, setters and
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
     * Generate PHPDoc for all magic methods.
     *
     * @param ReflectionClass $reflection
     *
     * @return string
     */
    private function _documentMagicMethods(
        ReflectionClass $reflection)
    {
        // If this class cannot be instantiated (such as being "abstract"), just
        // skip it. We don't need to document it. We'll document its children.
        // TODO: Perhaps we CAN document abstract classes too somehow, but it
        // doesn't really matter... We'd need to run mapcompiler manually.
        if (!$reflection->isInstantiable()) {
            // printf("Skipping non-instantiable class: %s\n", $reflection->getName());
            return '';
        }

        // Create a new instance of the class. Throws if its map cannot compile.
        $classObj = $reflection->newInstance(); // Throws.

        // Export all properties, with RELATIVE class-paths when possible.
        // NOTE: We will document ALL properties. Even ones inherited from
        // parents/imported maps. This ensures that users who are manually
        // reading the source code can see EVERYTHING without needing an IDE.
        $properties = $classObj->exportPropertyDescriptions(true);

        $result = [];

        // We will only document these functions (not the "has", since they're
        // useless for properties that are fully defined in the class map).
        foreach (['get', 'set', 'is', 'unset'] as $type) {
            foreach ($properties as $property) {
                // Generate the function name, such as "getSomething", and skip
                // this function if it's already defined as a REAL (overridden)
                // function in either this class or any of its parents.
                $functionName = $type.$property->func_case;
                if ($reflection->hasMethod($functionName)) {
                    // TODO: Currently this ONLY stops "getMessage", which comes
                    // from our base "Response" class. It's a bit weird that we
                    // stop getMessage but not its setters etc. Improve this...
                    // For now, let's manually re-add a "getMessage" definition
                    // until we figure out a better solution... The problem is
                    // that property maps MAY be inherited from parents OR
                    // imported, and it would be very costly to construct
                    // every parent to compare the property-lists of each... It
                    // would need a totally redesigned structure in this script,
                    // which would keep track of everything's parents and build
                    // everything parent-first... and only output definitions
                    // when they differ from its parent. But then we get back to
                    // the original problem: Users would be UNABLE to see all
                    // inherited property methods unless they have an IDE.
                    // So I guess for now we can just have this workaround which
                    // re-adds "getMessage" to all Response classes.
                    if ($classObj instanceof \InstagramAPI\Response
                        && $functionName === 'getMessage') {
                        // TODO: We hardcode it as "string", which is right...
                        $result[$functionName] = ' * @method string getMessage()';
                        continue;
                    } else {
                        // TODO: This is just protection against future
                        // functions, in case we need to tweak the system above.
                        // For now, everything above is fine...
                        printf("Unknown overridden function '%s' in '%s'\n", $functionName, get_class($classObj));
                        continue;
                    }
                }

                // Alright, the function doesn't exist as a real function. Only
                // as a virtual one. Document it via its calculated signature...
                // NOTE: Classtypes use relative paths related to current class!
                $functionSignature = $property->{'function_'.$type};
                $result[$functionName] = sprintf(' * @method %s', $functionSignature);
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
            if ($reflection->isSubclassOf('\InstagramAPI\AutoPropertyMapper')) {
                $hasProblems = $this->_processFile($filePath, $reflection);
                if ($hasProblems) {
                    $filesWithProblems[] = $filePath;
                }
            }
        }

        return $filesWithProblems;
    }
}
