<?php


namespace Server\Core;


use Symfony\Component\Yaml\Yaml;

/**
 * ------------------------------------------------------
 * Class YamlParser
 * ------------------------------------------------------
 *
 * Handles communication with Yaml Symfony Component
 * in order to retrieve variables of config files
 * and also to interpret env variables within yml
 * files
 *
 * TODO: Give better documentation of this class
 * TODO: Parser needs to be recursive
 *
 * @author Benjamin Gil Flores
 * @version NaN
 * @package Server\Core
 */
class YamlParser
{
    /**
     * Parses a yml file into an array converting
     * retrieving also the env variables within
     *
     * @param string $path
     * @return array
     */
    public function parseIt (string $path) : array
    {
        $ymlElements = Yaml::parseFile($path);

        foreach ($ymlElements as $key => $element) {
            if (!is_iterable($element)) {
                $envValue = $this->getEnvElement($element);

                if ($envValue) {
                    $ymlElements[$key] = $envValue;
                }
            }
        }

        return $ymlElements;
    }

    /**
     * Check if the element of a yml file is a env
     * variable in which case it retrieves it and
     * returns it
     *
     * @param string $element
     * @return string|null
     */
    private function getEnvElement (string $element) : ?string
    {
        if (strpos($element, "env") !== false) {
            if (preg_match('#\((.*?)\)#', $element, $match)) {
                return isset($_ENV[$match[1]]) ? $_ENV[$match[1]] : null;
            }

            return null;
        }

        return null;
    }
}