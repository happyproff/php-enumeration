<?php
declare(strict_types=1);

namespace Dbalabka\Tests;

use Dbalabka\Enumeration;
use Dbalabka\EnumerationException;
use Dbalabka\InvalidArgumentException;
use Dbalabka\Tests\Fixtures\Action;
use Dbalabka\Tests\Fixtures\ActionWithPublicConstructor;
use Dbalabka\Tests\Fixtures\Flag;
use PHPUnit\Framework\TestCase;

class EnumerationTest extends TestCase
{
    public function testInstantiate()
    {
        $this->expectException(\Error::class);
        new Action();
    }

    public function testAnonEnum()
    {
        $this->expectException(\Error::class);
        new class extends Enumeration {};
    }

    public function testInstantiateWithPublicConstructor()
    {
        $this->assertInstanceOf(Enumeration::class, new ActionWithPublicConstructor());
    }

    public function testAccessNotInitilizedEnumItemWithTypedProperties()
    {
        $this->expectException(\Error::class);
        Action::$view;
    }

    public function testOrdinals()
    {
        Action::initialize();
        $this->assertSame(0, Action::$view->ordinal());
        $this->assertSame(1, Action::$edit->ordinal());
    }

    public function testAccessOrdinalsInConstructor()
    {
        Flag::initialize();
        $this->assertSame(1, Flag::$noState->getFlagValue());
        $this->assertSame(2, Flag::$ok->getFlagValue());
        $this->assertSame(4, Flag::$notOk->getFlagValue());
        $this->assertSame(8, Flag::$unavailable->getFlagValue());
    }

    public function testName()
    {
        Flag::initialize();

        $this->assertSame('ok', Flag::$ok->name());
        $this->assertSame('notOk', Flag::$notOk->name());
    }

    public function testToString()
    {
        Flag::initialize();

        $this->assertSame('ok', '' . Flag::$ok);
        $this->assertSame('notOk', '' . Flag::$notOk);
    }

    public function testEquals()
    {
        Flag::initialize();

        $notOk = Flag::$notOk;
        $this->assertSame($notOk, Flag::$notOk);
        $this->assertTrue($notOk === Flag::$notOk);
    }

    public function testSerialization()
    {
        Flag::initialize();

        $this->expectException(EnumerationException::class);
        serialize(Flag::$notOk);
    }

    public function testUnserialization()
    {
        $this->expectException(EnumerationException::class);
        unserialize('O:28:"Dbalabka\Tests\Fixtures\Flag":2:{s:39:" Dbalabka\Tests\Fixtures\Flag flagValue";i:4;s:10:" * ordinal";i:2;}');
    }

    public function testClone()
    {
        Flag::initialize();

        $this->expectException(EnumerationException::class);
        $cloned = clone Flag::$notOk;
    }

    public function testValueOf()
    {
        Flag::initialize();

        $this->assertSame(Flag::$ok, Flag::valueOf('ok'));
        $this->assertSame(Flag::$notOk, Flag::valueOf('notOk'));
    }

    public function testValueOfNotExistingName()
    {
        Flag::initialize();

        $this->expectException(InvalidArgumentException::class);
        Flag::valueOf('does not exists');
    }

    public function testValues()
    {
        Flag::initialize();

        $values = Flag::values();

        $this->assertSame(
            $values,
            [
                'noState' => Flag::$noState,
                'ok' => Flag::$ok,
                'notOk' => Flag::$notOk,
                'unavailable' => Flag::$unavailable,
            ]
        );
    }

    public function testSwitchSupport()
    {
        Action::initialize();
        $someAction = Action::$view;
        switch ($someAction) {
            case Action::$edit:
                $this->fail('Edit is not equal to view');
                break;
            case Action::$view:
                $this->addToAssertionCount(1);
                break;
            default:
                $this->fail('Default should not be called');
        }
    }

    public function testCompareTo()
    {
        Flag::initialize();

        $this->assertSame(2, Flag::$unavailable->compareTo(Flag::$ok));
        $this->assertSame(1, Flag::$ok->compareTo(Flag::$noState));
        $this->assertSame(0, Flag::$ok->compareTo(Flag::$ok));
        $this->assertSame(-1, Flag::$ok->compareTo(Flag::$notOk));
        $this->assertSame(-2, Flag::$ok->compareTo(Flag::$unavailable));
    }
}
