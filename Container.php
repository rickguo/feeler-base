<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Base;

use Feeler\Base\Exception\{InvalidClassException, InvalidDataDomainException};

class Container extends BaseClass{
    const TYPE_CLASS = 1;
    const TYPE_OBJ = 2;
    const TYPE_CALLBACK = 3;

    protected $dependenciesMap = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $type
     * @return string
     */
    protected function getDependentModelName(int $type): string{
        $dict = [
            self::TYPE_CLASS => "CLASS",
            self::TYPE_OBJ => "OBJECT",
            self::TYPE_CALLBACK => "CALLBACK",
        ];

        return $dict[$type];
    }

    protected function getDependencyDictStruct(int $type): array{
        return [
            "type" => $type,
            "model_name" => $this->getDependentModelName($type),
            "sign" => null,
            "dependency" => null,
            "dependency_class" => null,
            "params" => null,
            "is_initialized" => null,
        ];
    }

    /**
     * @param $class
     * @param $dependency
     * @param $params
     * @param $type
     * @throws \Feeler\Base\Exception\InvalidDataTypeException
     */
    protected function setDependenciesMap(string $class, $dependency, array $params, int $type): void{
        $dependencyDict = $this->getDependencyDictStruct($type);

        if($type == self::TYPE_OBJ){
            $dependencyDict["dependency"] = $dependency;
            $dependencyDict["is_initialized"] = true;
        }
        else if($type == self::TYPE_CLASS || $type == self::TYPE_CALLBACK){
            if(isset($this->dependenciesMap[$class])){
                if($this->dependenciesMap[$class]["is_initialized"] == true){
                    $sign = md5("{$params}");

                    if($this->dependenciesMap[$class]["sign"] !== $sign){
                        $dependencyDict = $this->getDependencyDictStruct($type);
                    }
                    else{ //repetitive register
                        return;
                    }
                }
                else{ //repetitive register
                    return;
                }
            }

            $dependencyDict["dependency"] = $dependency;
            $dependencyDict["params"] = $params;
            $dependencyDict["is_initialized"] = false;
        }

        self::setDict($class, $dependencyDict, $this->dependenciesMap);
    }

    /**
     * @param string $class
     * @param $dependency
     * @param array $params
     * @throws InvalidClassException
     * @throws InvalidDataDomainException
     * @throws \Feeler\Base\Exception\InvalidDataTypeException
     */
    public function registerDependency(string $class, $dependency, array $params = []): void{
        if(!class_exists($class)){
            throw new InvalidClassException("Non-existent Class");
        }

        if(is_string($dependency) && ($dependency = trim($dependency)) && class_exists($dependency)){
            $this->setDependenciesMap($class, $dependency, $params, self::TYPE_CLASS);
        }
        else if(is_object($dependency)){
            $this->setDependenciesMap($class, $dependency, $params, self::TYPE_OBJ);
        }
        else if(self::isClosure($dependency)){
            $this->setDependenciesMap($class, $dependency, $params, self::TYPE_CALLBACK);
        }
        else{
            throw new InvalidDataDomainException("Unknown dependency type");
        }
    }

    /**
     * @param $class
     * @return object
     * @throws InvalidClassException
     * @throws InvalidDataDomainException
     * @throws \ReflectionException
     */
    public function genPreparedObj(string $class): object {
        if(!class_exists($class)){
            throw new InvalidClassException("Non-existent Class in the dependencies tree");
        }

        if(!isset($this->dependenciesMap[$class])){
            throw new InvalidDataDomainException("One or more Classes have not registered a dependency in the dependencies tree");
        }

        $dependencyDict = &$this->dependenciesMap[$class];
        $type = $dependencyDict["type"];
        $dependency = &$dependencyDict["dependency"];
        $isInitialized = &$dependencyDict["is_initialized"];
        $dependencyClassName = &$dependencyDict["dependency_class"];
        $params = &$dependencyDict["params"];
        $sign = &$dependencyDict["sign"];

        if($type == self::TYPE_CLASS){
            $thisObjSign = md5("{$params}");

            if(!$isInitialized || $thisObjSign !== $sign){
                $dependencyClassName = $dependency;
                $rObj = new \ReflectionClass($dependencyClassName);
                $dependency = $rObj->newInstanceArgs($params);
                $isInitialized = true;
                $sign = $thisObjSign;
            }
        }
        else if($type == self::TYPE_CALLBACK){
            $thisObjSign = md5("{$params}");

            if(!$isInitialized || $thisObjSign !== $sign){
                $dependency = call_user_func_array($dependency, $params);
                $isInitialized = true;
                $sign = $thisObjSign;
            }
        }
        else if($type == self::TYPE_OBJ){
            $rObj = new \ReflectionClass($dependency);
            $dependencyClassName = $rObj->getName();
        }
        else{
            throw new InvalidClassException("Unknown initialization situation in the dependencies tree");
        }

        if($dependencyClassName){
            $this->genPreparedObj($dependencyClassName);
        }

        return $dependency;
    }
}