<?php

namespace PostScripton\Money\Tests\Feature\Services;

use Illuminate\Support\Facades\Config;
use PostScripton\Money\Currency;
use PostScripton\Money\Exceptions\ServiceDoesNotExistException;
use PostScripton\Money\Services\CurrencyLayerService;
use PostScripton\Money\Services\ExchangeRatesAPIService;
use PostScripton\Money\Tests\TestCase;

class ServicesTest extends TestCase
{
	private $backup_config;

	protected function setUp(): void
	{
		parent::setUp();
		$this->backup_config = Config::get('money');
		Currency::setCurrencyList(Currency::currentList());
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		Config::set('money', $this->backup_config);
	}

	/** @test */
	public function a_service_changes_depending_on_the_config_value_when_it_calls()
	{
		$money = money(1000);

		Config::set('money.service', 'currencylayer');
		$this->assertInstanceOf(CurrencyLayerService::class, $money->service());

		Config::set('money.service', 'exchangeratesapi');
		$this->assertInstanceOf(ExchangeRatesAPIService::class, $money->service());
	}

	/** @test */
	public function a_service_does_not_exist()
	{
		Config::set('money.service', 'qwerty');

		$this->expectException(ServiceDoesNotExistException::class);

		$money = money(1000);
		$money->convertInto(currency('rub'));
	}
}