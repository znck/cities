<?php namespace Znck\Cities;

/**
 * Class City.
 *
 * @property string $name
 * @property string $code
 */
trait City
{
    /**
     * @var string
     */
    protected static $locale = 'en';

    /**
     * @var Translator
     */
    protected static $cities;

    /**
     * Boot city.
     */
    public static function bootCity()
    {
        static::$locale = config('app.locale', 'en');
        static::$cities = app('translator.cities');
    }

    /**
     * @param string $val
     *
     * @return string
     */
    public function getNameAttribute(string $val)
    {
        if (static::$locale === 'en') {
            return $val;
        }

        $name = static::$cities->getName($this->code, static::$locale);

        if ($name === $this->code) {
            return $val;
        }

        return $name;
    }
}
