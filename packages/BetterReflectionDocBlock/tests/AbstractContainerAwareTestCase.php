<?php declare(strict_types=1);

namespace Symplify\BetterReflectionDocBlock\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symplify\BetterReflectionDocBlock\DependencyInjection\ContainerFactory;

abstract class AbstractContainerAwareTestCase extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param mixed[] $data
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->container = (new ContainerFactory())->create();

        parent::__construct($name, $data, $dataName);
    }
}
