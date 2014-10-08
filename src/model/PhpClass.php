<?php
namespace gossi\codegen\model;

use Doctrine\Common\Annotations\PhpParser;
use gossi\docblock\DocBlock;
use gossi\codegen\model\parts\InterfacesTrait;
use gossi\codegen\model\parts\AbstractTrait;
use gossi\codegen\model\parts\FinalTrait;
use gossi\codegen\model\parts\ConstantsTrait;
use gossi\codegen\model\parts\PropertiesTrait;
use gossi\codegen\model\parts\TraitsTrait;

class PhpClass extends AbstractPhpStruct implements GenerateableInterface, TraitsInterface, ConstantsInterface {
	
	use InterfacesTrait;
	use AbstractTrait;
	use FinalTrait;
	use ConstantsTrait;
	use PropertiesTrait;
	use TraitsTrait;

	private $parentClassName;

	public static function fromReflection(\ReflectionClass $ref) {
		$class = new static();
		$class->setQualifiedName($ref->name)->setAbstract($ref->isAbstract())->setFinal($ref->isFinal())->setConstants($ref->getConstants());
		
		if (null === self::$phpParser) {
			self::$phpParser = new PhpParser();
		}
		
		$class->setUseStatements(self::$phpParser->parseClass($ref));
		
		if ($ref->getDocComment()) {
			$docblock = new DocBlock($ref);
			$class->setDocblock($docblock);
			$class->setDescription($docblock->getShortDescription());
			$class->setLongDescription($docblock->getLongDescription());
		}
		
		foreach ($ref->getMethods() as $method) {
			$class->setMethod(static::createMethod($method));
		}
		
		foreach ($ref->getProperties() as $property) {
			$class->setProperty(static::createProperty($property));
		}
		
		return $class;
	}

	public function __construct($name = null) {
		parent::__construct($name);
	}

	public function getParentClassName() {
		return $this->parentClassName;
	}

	/**
	 *
	 * @param string|null $name        	
	 */
	public function setParentClassName($name) {
		$this->parentClassName = $name;
		
		return $this;
	}

	public function generateDocblock() {
		$docblock = parent::generateDocblock();
		
		foreach ($this->constants as $constant) {
			$constant->generateDocblock();
		}
		
		foreach ($this->properties as $prop) {
			$prop->generateDocblock();
		}
		
		$this->setDocblock($docblock);
		
		return $docblock;
	}

}
