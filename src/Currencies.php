<?php

namespace PostScripton\Money;

use Illuminate\Support\Collection;
use PostScripton\Money\Enums\CurrencyList;

class Currencies
{
    private static ?Collection $currencies = null;

    public static function get(): Collection
    {
        if (is_null(self::$currencies)) {
            self::$currencies = self::createCurrencies(self::loadCurrencies());
        }

        return self::$currencies;
    }

    public static function getCodesArray(): array
    {
        return self::get()
            ->map(fn(Currency $currency) => $currency->getCode())
            ->toArray();
    }

    private static function loadCurrencies(): Collection
    {
        $list = config('money.currency_list');

        if (!is_array($list)) {
            return self::getList($list);
        }

        // Custom currency list (array of strings)
        return self::getList(CurrencyList::All)->filter(function (array $currency) use (&$list) {
            if (empty($list)) {
                return false;
            }

            foreach ($list as $item) {
                if ($currency['iso_code'] === $item || $currency['num_code'] === $item) {
                    $list = array_diff($list, [$item]);
                    return true;
                }
            }

            return false;
        });
    }

    private static function createCurrencies(Collection $currencies): Collection
    {
        return $currencies->map(fn(array $currency) => new Currency($currency));
    }

    private static function getList(CurrencyList $currencyList): Collection
    {
        $list = collect(require $currencyList->path());

        if ($currencyList !== CurrencyList::Custom) {
            $customCurrencies = collect(config('money.custom_currencies'));
            return $list->map(function (array $currency) use ($customCurrencies) {
                $customCurrency = $customCurrencies->first(function (array $customCurrency) use ($currency) {
                    return strtoupper($customCurrency['iso_code']) === strtoupper($currency['iso_code']) ||
                        $customCurrency['num_code'] === $currency['num_code'];
                });
                return $customCurrency ?: $currency;
            });
        }

        return $list;
    }
}