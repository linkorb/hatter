<?php

namespace LinkORB\Component\Hatter\Factory;

use LinkORB\Component\Hatter\Hatter;
use Symfony\Component\Yaml\Yaml;

class HatterFactory
{
    public static function fromFilename(string $filename): Hatter
    {
         // Read the YAML file
         if (!file_exists($filename)) {
             throw new \InvalidArgumentException('File not found: ' . $filename);
         }
         $config = Yaml::parseFile($filename);

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
        //  exit();
 
         $hatter = Hatter::fromArray($config);
         return $hatter;
    }
}
