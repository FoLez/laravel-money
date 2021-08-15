<?php

namespace PostScripton\Money\Tests\Unit;

use PostScripton\Money\Currency;
use PostScripton\Money\Money;
use PostScripton\Money\MoneySettings;
use PostScripton\Money\Tests\TestCase;

class MoneyTest extends TestCase
{
    /** @test */
    public function allTheWaysToCreateMoney()
    {
        $money1 = Money::make(12345);
        $money2 = new Money(12345);

        $this->assertEquals($money1, $money2);
    }

    /** @test */
    public function baseWaysOfFormattingMoney()
    {
        $usd = Currency::code('USD');
        $rub = Currency::code('RUB');

        $this->assertEquals('$ 123', Money::make(1230, $usd)->toString());
        $this->assertEquals('$ 123.4', Money::make(1234, $usd)->toString());
        $this->assertEquals('$ 1 234', Money::make(12340, $usd)->toString());
        $this->assertEquals('$ 1 234.5', Money::make(12345, $usd)->toString());

        $this->assertEquals('123 ₽', Money::make(1230, $rub)->toString());
        $this->assertEquals('123.4 ₽', Money::make(1234, $rub)->toString());
        $this->assertEquals('1 234 ₽', Money::make(12340, $rub)->toString());
        $this->assertEquals('1 234.5 ₽', Money::make(12345, $rub)->toString());
    }

    /** @test */
    public function numbersCanBeFetchedOutOfTheMoney()
    {
        $money = Money::make(12345);

        $this->assertEquals('1 234.5', $money->getAmount());
        $this->assertEquals(12345.0, $money->getPureAmount());
    }

    /** @test */
    public function allCastsToString()
    {
        $money = Money::make(1234);

        $this->assertEquals('$ 123.4', $money->toString());
        $this->assertEquals('$ 123.4', strval($money));
        $this->assertEquals('$ 123.4', '' . $money);
        $this->assertEquals('$ 123.4', $money);
    }

    /** @test */
    public function originIntMoneyGetsRidOfDecimalsWithFloorMethod()
    {
        $money = new Money(132.76);

        $this->assertEquals(132.76, $money->getPureAmount());
        $this->assertEquals('$ 13.3', $money->toString());

        $money->floor();

        $this->assertEquals(130, $money->getPureAmount());
        $this->assertEquals('$ 13', $money->toString());
    }

    /** @test */
    public function originFloatMoneyGetsRidOfDecimalsWithFloorMethod()
    {
        $settings = new MoneySettings();
        $money = new Money(13.276, $settings->setOrigin(MoneySettings::ORIGIN_FLOAT));

        $this->assertEquals(13.276, $money->getPureAmount());
        $this->assertEquals('$ 13.3', $money->toString());

        $money->floor();

        $this->assertEquals(13, $money->getPureAmount());
        $this->assertEquals('$ 13', $money->toString());
    }

    /** @test */
    public function anErrorThatMoneyObjectsAreImmutable()
    {
        $johnReward = $bobReward = new Money(1000);

        // John has additional bonus $50
        $winCoupon = new Money(500);

        $johnReward->add($winCoupon);

        $this->assertTrue($johnReward->equals($bobReward));
    }

    /** @test */
    public function correctWayToHandleImmutableMoneyObjects()
    {
        $johnReward = new Money(1000);
        $bobReward = new Money(1000);

        // John has additional bonus $50
        $winCoupon = new Money(500);

        $johnReward = $johnReward->add($winCoupon);

        $this->assertEquals(1000, $bobReward->getPureAmount());
        $this->assertEquals(1500, $johnReward->getPureAmount());
        $this->assertNotTrue($johnReward->equals($bobReward));
        $this->assertNotTrue($johnReward->settings() === $bobReward->settings());
    }
}
