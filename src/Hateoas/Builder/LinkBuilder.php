<?php

namespace Hateoas\Builder;

use Hateoas\Factory\Definition\RouteLinkDefinition;
use Hateoas\Factory\Definition\LinkDefinition;
use Hateoas\Link;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\Util\PropertyPath;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class LinkBuilder implements LinkBuilderInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromDefinition(LinkDefinition $definition, $data)
    {
        if (!$definition instanceof RouteLinkDefinition) {
            return;
        }

        $parameters = array();
        foreach ($definition->getParameters() as $name => $path) {
            if (is_numeric($name)) {
                $name = $path;
            }

            $propertyPath      = new PropertyPath($path);
            $parameters[$name] = $propertyPath->getValue($data);
        }

        return $this->create(
            $definition->getRoute(),
            $parameters,
            $definition->getRel(),
            $definition->getType()
        );
    }

    /**
     * @param  string $route
     * @param  array  $parameters
     * @param  string $rel
     * @param  string $type
     * @return Link
     */
    public function create($route, array $parameters = array(), $rel = Link::REL_SELF, $type = null)
    {
        $url = $this->router->generate($route, $parameters, true);

        return new Link($url, $rel, $type);
    }
}
