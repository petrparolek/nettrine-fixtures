<?php declare(strict_types = 1);

namespace Tests\Nettrine\Fixtures\Cases\DI;

use Doctrine\Common\Persistence\ManagerRegistry;
use Mockery;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Fixtures\Command\LoadDataFixturesCommand;
use Nettrine\Fixtures\DI\FixturesExtension;
use Nettrine\Fixtures\Loader\FixturesLoader;
use Tests\Nettrine\Fixtures\Cases\Fixture\ContainerFixture;
use Tests\Nettrine\Fixtures\Cases\TestCase;

final class FixturesExtensionTest extends TestCase
{

	public function testRegister(): void
	{
		$managerRegistry = Mockery::mock(ManagerRegistry::class);

		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(function (Compiler $compiler): void {
			//Fixtures
			$compiler->getContainerBuilder()
				->addDefinition('managerRegistry')
				->setClass(ManagerRegistry::class)
				->setDynamic(true);
			$compiler->addExtension('fixtures', new FixturesExtension());
		}, '1a');

		/** @var Container $container */
		$container = new $class();
		$container->addService('managerRegistry', $managerRegistry);

		/** @var FixturesLoader $loader */
		$loader = $container->getByType(FixturesLoader::class);
		self::assertInstanceOf(FixturesLoader::class, $loader);

		$loader->loadPaths([__DIR__ . '/../Fixture']);
		/** @var ContainerFixture $containerFixture */
		$containerFixture = $loader->getFixture(ContainerFixture::class);
		self::assertInstanceOf(ContainerFixture::class, $containerFixture);
		self::assertInstanceOf(Container::class, $containerFixture->getContainer());

		/** @var LoadDataFixturesCommand $command */
		$command = $container->getByType(LoadDataFixturesCommand::class);
		self::assertInstanceOf(LoadDataFixturesCommand::class, $command);
	}

}
