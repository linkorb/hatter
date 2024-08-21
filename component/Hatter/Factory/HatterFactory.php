<?php

namespace LinkORB\Component\Hatter\Factory;

use LinkORB\Component\Hatter\Hatter;
use Symfony\Component\Yaml\Yaml;

class HatterFactory
{
    public static function fromFilenames(array $filenames): Hatter
    {
        $fullConfig = [];
        // Read the YAML files
        foreach ($filenames as $filename) {
            if (!file_exists($filename)) {
                throw new \InvalidArgumentException('File not found: ' . $filename);
            }
            $config = Yaml::parseFile($filename);

            // Post-process includes
            foreach ($config['includes'] ?? [] as $includeFilename) {
                $includeFilename = dirname($filename) . '/' . $includeFilename;
                // echo $includeFilename . PHP_EOL;
                $includeFilenames = glob($includeFilename);
                if (count($includeFilenames) == 0) {
                    throw new \InvalidArgumentException('Include file not found: ' . $includeFilename);
                }
                foreach ($includeFilenames as $includeFilename) {
                    $includeYaml = file_get_contents($includeFilename);
                    // echo ' - ' . $includeFilename . PHP_EOL;
                    $includeConfig = Yaml::parse($includeYaml);
                    $config = array_merge_recursive($config, $includeConfig);
                }
            }

            // using replace vs merge to avoid strings to turn into arrays, for example column.generator etc
            $fullConfig = array_replace_recursive($fullConfig, $config);
        }

        // print_r($fullConfig); exit();
 
        $hatter = Hatter::fromArray($fullConfig);
        return $hatter;
    }
}
