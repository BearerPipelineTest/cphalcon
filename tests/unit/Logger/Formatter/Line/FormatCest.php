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

namespace Phalcon\Tests\Unit\Logger\Formatter\Line;

use DateTimeImmutable;
use Phalcon\Logger;
use Phalcon\Logger\Formatter\Line;
use Phalcon\Logger\Item;
use UnitTester;

use function sprintf;

class FormatCest
{
    /**
     * Tests Phalcon\Logger\Formatter\Line :: format()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function loggerFormatterLineFormat(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - format()');

        $formatter = new Line();
        $time      = new DateTimeImmutable("now");
        $item      = new Item('log message', 'debug', Logger::DEBUG, $time);

        $expected = sprintf(
            '[%s][debug] log message',
            $time->format('c')
        );

        $actual = $formatter->format($item);

        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Logger\Formatter\Line :: format() -custom
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function loggerFormatterLineFormatCustom(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - format() - custom');

        $formatter = new Line('%message%-[%level%]-%date%');
        $time      = new DateTimeImmutable("now");
        $item      = new Item('log message', 'debug', Logger::DEBUG, $time);

        $expected = sprintf(
            'log message-[debug]-%s',
            $time->format('c')
        );

        $actual = $formatter->format($item);

        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Logger\Formatter\Line :: format() -custom with miliseconds
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-23
     */
    public function loggerFormatterLineFormatCustomWithMiliseconds(UnitTester $I)
    {
        $I->wantToTest('Logger\Formatter\Line - format() - custom - with milliseconds');

        $formatter = new Line('%message%-[%level%]-%date%', 'U.u');
        $time      = new DateTimeImmutable("now");
        $item      = new Item('log message', 'debug', Logger::DEBUG, $time);

        $result = $formatter->format($item);
        $parts  = explode('-', $result);
        $parts  = explode('.', $parts[2]);

        $I->assertCount(2, $parts);
        $I->assertGreaterThan(0, (int) $parts[0]);
        $I->assertGreaterThan(0, (int) $parts[1]);
    }
}
