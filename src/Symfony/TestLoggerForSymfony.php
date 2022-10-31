<?php

declare(strict_types=1);

namespace DobroSite\PHPUnit\PSR3\Symfony;

use DobroSite\PHPUnit\PSR3\TestLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

trait TestLoggerForSymfony
{
    abstract protected static function getContainer(): ContainerInterface;

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    protected function getLogger(): TestLogger
    {
        $client = self::getContainer()->get(LoggerInterface::class);
        \assert(
            $client instanceof TestLogger,
            \sprintf(
                'Service "%s" should be an instance of %s, but it is %s. '
                . 'You should replace it in the test configuration.',
                LoggerInterface::class,
                TestLogger::class,
                $client::class
            )
        );

        return $client;
    }
}
