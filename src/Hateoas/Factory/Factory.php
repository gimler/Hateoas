<?php

namespace Hateoas\Factory;

use Hateoas\Factory\Definition\CollectionDefinition;
use Hateoas\Factory\Definition\ResourceDefinition;
use Hateoas\Factory\Definition\LinkDefinition;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Factory implements FactoryInterface
{
    /**
     * @var array
     */
    private $resourceDefinitions;

    /**
     * @var array
     */
    private $collectionDefinitions;

    public function __construct(array $resourceDefinitions, array $collectionDefinitions = array())
    {
        $this->resourceDefinitions   = $resourceDefinitions;
        $this->collectionDefinitions = $collectionDefinitions;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceDefinition($data)
    {
        foreach ($this->resourceDefinitions as $class => $definition) {
            if ((is_object($data) && !$data instanceof $class) || (!is_object($data) && !is_subclass_of($data, $class) && $data != $class)) {
                continue;
            }

            if (!$definition instanceof ResourceDefinition) {
                $this->resourceDefinitions[$class] = $this->createResourceDefinition($definition, $class);
            }

            return $this->resourceDefinitions[$class];
        }

        throw new \RuntimeException(sprintf('No definition found for resource "%s".', is_object($data) ? get_class($data) : $data));
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectionDefinition($className)
    {
        foreach ($this->collectionDefinitions as $class => $definition) {
            if ($className !== $class) {
                continue;
            }

            if (!$definition instanceof CollectionDefinition) {
                $this->collectionDefinitions[$class] = $this->createCollectionDefinition($definition, $class);
            }

            return $this->collectionDefinitions[$class];
        }

        throw new \RuntimeException(sprintf('No definition found for collection of "%s".', $className));
    }

    protected function createLinkDefinition(array $definition)
    {
        if (!isset($definition['rel'])) {
            throw new \InvalidArgumentException('A link definition should define a "rel" value.');
        }

        $type = isset($definition['type']) ? $definition['type'] : null;

        return new LinkDefinition($definition['rel'], $type);
    }

    private function createResourceDefinition(array $definition, $class)
    {
        $links = array();
        foreach ($definition as $link) {
            if (!$link instanceof LinkDefinition) {
                $link = $this->createLinkDefinition($link);
            }

            $links[] = $link;
        }

        return new ResourceDefinition($class, $links);
    }

    private function createCollectionDefinition(array $definition, $class)
    {
        $links = array();

        if (isset($definition['links'])) {
            foreach ($definition['links'] as $link) {
                if (!$link instanceof LinkDefinition) {
                    $link = $this->createLinkDefinition($link);
                }

                $links[] = $link;
            }
        }

        $attributes = isset($definition['attributes']) ? $definition['attributes'] : array();

        return new CollectionDefinition($class, $links, $attributes);
    }
}
