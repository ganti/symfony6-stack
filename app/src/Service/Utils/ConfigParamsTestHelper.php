<?php

namespace App\Service\Utils;

use ErrorException;
use NotFoundException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Path;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;

class ConfigParamsTestHelper extends AbstractController
{
    
    protected const END_OF_PATH_DISTINCT_VALUE = '[END_OF_LINE]';
    
    /**
     * __construct
     *
     * @param  mixed $paramsFile e.g. parameters.yaml
     * @param  mixed $configDirectory e.g. parameters.yaml
     * @param  mixed $separator between elements
     * @param  mixed $pathPrefix define prefix, to use shorter paths
     * @return void
     */
    public function __construct($paramsFile = 'parameters.yaml', $configDirectory = 'parameters.yaml', $separator='.', $pathPrefix = 'parameters')
    {
        $this->separator = $separator;
        $this->pathPrefix = $pathPrefix;

        $this->projectDir = Path::canonicalize(__DIR__.'/../../..');
        $this->configDir = Path::canonicalize($this->projectDir.$configDirectory);
        $this->configDirTest = Path::canonicalize($this->projectDir.$configDirectory.'/test');
        $this->paramFileOrginal = Path::canonicalize($this->configDir .'/'.$paramsFile);
        $this->paramFileTest = Path::canonicalize($this->configDirTest .'/'.$paramsFile);

        $this->setup();
        
    }
    
    /**
     * setup
     * 
     * @return bool
     */
    public function setup() : ?bool
    {
        if(!$this->isParamFileOrginalExisting())
        {
            return false;
        }
        $this->copyParamFileTest();
        $this->loadTestFile();

        return null;
    }
 
    /**
     * isParamFileOrginalExisting checks wether /config/packages/parameters.yaml exists. 
     * paramsFile can be changed when creating
     *
     * @return bool
     */
    public function isParamFileOrginalExisting() : bool
    {
        $fs = new Filesystem();
        if( $fs->exists($this->configDir) )
        {
            if( $fs->exists($this->paramFileOrginal) )
            {
                return true;
            } else {
                throw new \ErrorException('Config file not found:'. $this->paramFileOrginal.'.');
            }
        } else {
            throw new \ErrorException('configDirectory not found:'. $this->configDir.'.');
            
        }
        return false;

    }

        
    /**
     * refreshParamFileTest
     * copies orignal param file to test file 
     * @return void
     */
    public function copyParamFileTest() : void
    {
        $fs = new Filesystem();
        $fs->remove($this->paramFileTest);
        $fs->copy($this->paramFileOrginal, $this->paramFileTest, true);
        $this->loadTestFile();
    }


    public function loadTestFile() : void
    {
        $this->yamlTest = Yaml::parseFile($this->paramFileTest);        
        $this->yamlTestPaths = $this->generateYamlPaths($this->yamlTest);
    }
    
    /**
     * generateYamlPaths
     * generate path, and sanetize it
     * @param  mixed $tree
     * @return array
     */
    protected function generateYamlPaths($tree) : ?array
    {
        $paths = $this->generateYamlPathsTree($tree);
        
        array_walk($paths, array($this, 'cleanupGeneratedYamlSubstringPaths'), $paths);
        $paths = array_filter($paths);

        $pattern = '/'.preg_quote(self::END_OF_PATH_DISTINCT_VALUE, '/').'$/';
        $paths = preg_replace($pattern, '', $paths);
        return $paths;
    }
    
    /**
     * generateYamlPathsTree
     * recoursive loop thru fields to greate paths
     * @param  mixed $tree nested arrray below
     * @param  mixed $parent parent above
     * @return Array
     */
    protected function generateYamlPathsTree($tree, $parent=null) : Array
    {
        $paths = array();
    
        if($parent !== null) {
            $parent = $parent.$this->separator;
        }
        foreach($tree as $k => $v) {
            if(is_array($v)) {
                $currentPath = $parent.$k;
                $paths[] = $currentPath;
                $paths = array_merge($paths, $this->generateYamlPathsTree($v, $currentPath));
            } else {
                $paths[] = $parent.$k.self::END_OF_PATH_DISTINCT_VALUE;
            }
        }
        return $paths;
    }
    
    /**
     * cleanupGeneratedYamlSubstringPaths
     * keep only last element of chain.
     * e.g will be kept 'house.room.table', but 'house'|'house.room' will be purged
     * 
     * @param  mixed $value
     * @param  mixed $key
     * @param  mixed $paths
     * @return void
     */
    protected function cleanupGeneratedYamlSubstringPaths(&$value, $key, $paths)
    {
        unset($paths[$key]);
        if( $this->substring_in_array($value, $paths) )
        {
            $value = null;
        }
    }
    
    /**
     * substring_in_array
     * is substring in found in array values
     *
     * @param  mixed $needle
     * @param  mixed $haystack
     * @return bool
     */
    protected function substring_in_array($needle, $haystack) : bool
    {
        $found_keys=[];
        foreach ($haystack as $key => $value) {
            if (false !== strpos($value, $needle)) {
                $found_keys[] = $key;
            }
        }
        return !empty($found_keys);
    }
    
    /**
     * updateValue
     * update yml by path and save it.
     * 
     * @param  mixed $path
     * @param  mixed $value
     * @return bool
     */
    public function updateValue(string $path, $value) : bool
    {
        $pathPrefix = (!empty($this->pathPrefix)) ? $this->pathPrefix.$this->separator : '';
        

        if(!in_array($pathPrefix.$path, $this->yamlTestPaths))
        {
            $pattern = '/^'.preg_quote($pathPrefix, '.').'/';
            $allowedPathsNoPrefix =preg_replace($pattern, '', $this->yamlTestPaths);
            $allowedPaths = join(PHP_EOL, $allowedPathsNoPrefix);
            throw new \LogicException('path not found: '. $path.PHP_EOL.'Allowed paths are:'.PHP_EOL.$allowedPaths);
        }

        $pathParts = explode( $this->separator, $path );
        $yaml = &$this->yamlTest;

        foreach($pathParts as $part){
            $yaml = &$yaml[$part];
        }
        $yaml = $value;
        return $this->saveTestFile();
    }
    
    /**
     * saveTestFile
     *
     * @return bool
     */
    public function saveTestFile() : bool
    {
        if(!empty($this->yamlTest)){
            $yaml = Yaml::dump($this->yamlTest, 10, 4);
            return file_put_contents($this->paramFileTest, $yaml.PHP_EOL.PHP_EOL) != false;
        }
        return false;
    }

}