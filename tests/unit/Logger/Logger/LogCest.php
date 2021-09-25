<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Logger\Logger;

use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Adapter\Syslog;
use Phalcon\Logger\Formatter\Line;
use Psr\Log\LogLevel;
use UnitTester;

use function logsDir;
use function sprintf;
use function strtoupper;
use function uniqid;

class LogCest
{
    /**
     * Tests Phalcon\Logger :: log()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-10-21
     */
    public function loggerLog(UnitTester $I)
    {
        $I->wantToTest('Logger - log()');

        $logPath  = logsDir();

        $levels = [
            Logger::ALERT     => 'alert',
            Logger::CRITICAL  => 'critical',
            Logger::DEBUG     => 'debug',
            Logger::EMERGENCY => 'emergency',
            Logger::ERROR     => 'error',
            Logger::INFO      => 'info',
            Logger::NOTICE    => 'notice',
            Logger::WARNING   => 'warning',
            Logger::CUSTOM    => 'custom',
            'alert'           => 'alert',
            'critical'        => 'critical',
            'debug'           => 'debug',
            'emergency'       => 'emergency',
            'error'           => 'error',
            'info'            => 'info',
            'notice'          => 'notice',
            'warning'         => 'warning',
            'custom'          => 'custom',
            'unknown'         => 'custom',
        ];

        foreach ($levels as $level => $levelName) {
            $fileName = $I->getNewFileName('log', 'log');
            $adapter  = new Stream($logPath . $fileName);

            $logger = new Logger(
                'my-logger',
                [
                    'one' => $adapter,
                ]
            );

            $logger->log($level, 'Message ' . $levelName);

            $I->amInPath($logPath);
            $I->openFile($fileName);

            $expected = sprintf(
                '[%s] Message %s',
                strtoupper($levelName),
                $levelName
            );

            $I->seeInThisFile($expected);

            $adapter->close();
            $I->safeDeleteFile($fileName);
        }
    }

    /**
     * Tests Phalcon\Logger :: log() - logLevel
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-10-21
     */
    public function loggerLogLogLevel(UnitTester $I)
    {
        $I->wantToTest('Logger - log() - logLevel');

        $logPath   = logsDir();
        $levelsYes = [
            Logger::ALERT     => 'alert',
            Logger::CRITICAL  => 'critical',
            Logger::EMERGENCY => 'emergency',
            'alert'           => 'alert',
            'critical'        => 'critical',
            'emergency'       => 'emergency',
        ];

        $levelsNo = [
            Logger::DEBUG   => 'debug',
            Logger::ERROR   => 'error',
            Logger::INFO    => 'info',
            Logger::NOTICE  => 'notice',
            Logger::WARNING => 'warning',
            Logger::CUSTOM  => 'custom',
            'debug'         => 'debug',
            'error'         => 'error',
            'info'          => 'info',
            'notice'        => 'notice',
            'warning'       => 'warning',
            'custom'        => 'custom',
            'unknown'       => 'custom',
        ];

        foreach ($levelsYes as $level => $levelName) {
            $fileName = $I->getNewFileName('log', 'log');
            $adapter  = new Stream($logPath . $fileName);

            $logger = new Logger(
                'my-logger',
                [
                    'one' => $adapter,
                ]
            );

            $logger->setLogLevel(Logger::ALERT);

            $logger->log($level, 'Message ' . $levelName);

            $I->amInPath($logPath);
            $I->openFile($fileName);

            $expected = sprintf(
                '[%s] Message %s',
                strtoupper($levelName),
                $levelName,
            );
            $I->seeInThisFile($expected);

            $adapter->close();
            $I->safeDeleteFile($fileName);
        }

        foreach ($levelsNo as $level => $levelName) {
            $fileName = $I->getNewFileName('log', 'log');
            $adapter  = new Stream($logPath . $fileName);

            $logger = new Logger(
                'my-logger',
                [
                    'one' => $adapter,
                ]
            );

            $logger->setLogLevel(Logger::ALERT);

            /**
             * Adding an ALERT here because otherwise the log will not
             * be created since these are non logging events
             */
            $logger->alert('Message ALERT');
            $logger->log($level, 'Message ' . $levelName);

            $I->amInPath($logPath);
            $I->openFile($fileName);

            $expected = sprintf(
                '[%s] Message %s',
                strtoupper($levelName),
                $levelName,
            );
            $I->dontSeeInThisFile($expected);

            $adapter->close();
            $I->safeDeleteFile($fileName);
        }
    }

    /**
     * Tests Phalcon\Logger :: log()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-10-21
     */
    public function loggerLogSyslog(UnitTester $I)
    {
        $I->wantToTest('Logger - log() - syslog');

        $adapter = new Syslog("php:://memory");

        $logger = new Logger(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $logger->log(Logger::ERROR, 'Message Error');
    }

    /**
     * Tests Phalcon\Logger :: log() - logLevel
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-12-09
     * @issue  #15214
     */
    public function loggerLogLogLevelPsr(UnitTester $I)
    {
        $I->wantToTest('Logger - log() - logLevel');

        $unique    = uniqid();
        $logPath   = logsDir();
        $fileName  = $I->getNewFileName('log', 'log');
        $adapter   = new Stream($logPath . $fileName);

        $logger = new Logger(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $logger->log(Logger::INFO, 'info message ' . $unique);
        $logger->log(LogLevel::INFO, 'info message psr ' . $unique);

        $I->amInPath($logPath);
        $I->openFile($fileName);

        $expected = '[INFO] info message ' . $unique;
        $I->seeInThisFile($expected);
        $expected = '[INFO] info message psr ' . $unique;
        $I->seeInThisFile($expected);

        $adapter->close();
        $I->safeDeleteFile($fileName);
    }

    /**
     * Tests Phalcon\Logger :: log() - different line format
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2121-04-14
     * @issue  #15375
     */
    public function loggerLogLogLevelDifferentLineFormat(UnitTester $I)
    {
        $I->wantToTest('Logger - log() - different line format');

        $unique    = uniqid();
        $logPath   = logsDir();
        $fileName  = $I->getNewFileName('log', 'log');
        $format    = '[%date%] [%level%] %message%';
        $formatter = new Line($format);
        $adapter   = new Stream($logPath . $fileName);
        $adapter->setFormatter($formatter);

        $logger = new Logger(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $logger->log(Logger::INFO, 'info message ' . $unique);

        $I->amInPath($logPath);
        $I->openFile($fileName);

        $expected = '[INFO] info message ' . $unique;
        $I->seeInThisFile($expected);

        $adapter->close();
        $I->safeDeleteFile($fileName);
    }
}
