<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Attributes;

use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\IObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Defines the constraint registrant for attributes
 */
final class AttributeObjectConstraintsRegistrant implements IObjectConstraintsRegistrant
{
    /** @var list<string> The paths to check for constraints */
    private readonly array $paths;

    /**
     * @param string|list<string> $paths The path or paths to check for constraints
     * @param ITypeFinder $typeFinder The type finder
     */
    public function __construct(string|array $paths, private readonly ITypeFinder $typeFinder = new TypeFinder())
    {
        $this->paths = \is_array($paths) ? $paths : [$paths];
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if a class could not be reflected
     */
    public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void
    {
        foreach ($this->typeFinder->findAllClasses($this->paths, true) as $class) {
            $reflectionClass = new ReflectionClass($class);
            $propertyConstraints = $methodConstraints = [];

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                self::addConstraints($reflectionProperty, $propertyConstraints);
            }

            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                self::addConstraints($reflectionMethod, $methodConstraints);
            }

            $objectConstraints->registerObjectConstraints(new ObjectConstraints(
                $class,
                $propertyConstraints,
                $methodConstraints
            ));
        }
    }

    /**
     * Adds constraints to a map for a reflected method or property
     *
     * @param ReflectionMethod|ReflectionProperty $reflection The reflected method or property
     * @param array<string, list<IConstraint>> $map The map to add constraints to
     */
    private static function addConstraints(ReflectionMethod|ReflectionProperty $reflection, array &$map): void
    {
        foreach ($reflection->getAttributes(IConstraintAttribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $attributeInstance = $attribute->newInstance();
            $key = $reflection->getName();

            if (!isset($map[$key])) {
                $map[$key] = [];
            }

            $map[$key][] = $attributeInstance->createConstraintFromAttribute();
        }
    }
}
