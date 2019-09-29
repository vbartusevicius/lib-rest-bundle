<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Tests\Functional\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Paysera\Bundle\NormalizationBundle\PayseraNormalizationBundle;
use Maba\Bundle\RestBundle\PayseraRestBundle;
use Maba\Bundle\RestBundle\Tests\Functional\Fixtures\FixtureTestBundle\PayseraFixtureTestBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class TestKernel extends Kernel
{
    private $configFile;
    private $commonFile;

    public function __construct($testCase, $commonFile = 'common.yml')
    {
        parent::__construct((string)crc32($testCase . $commonFile), true);
        $this->configFile = $testCase . '.yml';
        $this->commonFile = $commonFile;
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new DoctrineBundle(),
            new PayseraNormalizationBundle(),
            new PayseraRestBundle(),
            new PayseraFixtureTestBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/' . $this->commonFile);
        $loader->load(__DIR__ . '/config/' . $this->configFile);
    }
}
