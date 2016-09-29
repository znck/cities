<?php
namespace Znck\Cities;

use Illuminate\Support\Str;

class Translator
{
    /**
     * @var FileLoader
     */
    protected $loader;

    /**
     * @var string
     */
    protected $fallbackLocale;

    /**
     * @var array
     */
    protected $loaded = [];

    /**
     * Translator constructor.
     *
     * @param FileLoader $loader
     * @param string     $locale
     */
    public function __construct(FileLoader $loader, string $locale)
    {
        $this->loader = $loader;
        $this->fallbackLocale = $locale;
    }

    /**
     * @param string $country
     * @param string $state
     * @param string $locale
     *
     * @return bool
     */
    protected function isLoaded(string $country, string $state, string $locale)
    {
        return isset($this->loaded[$country][$state][$locale]);
    }

    /**
     * @param string $key
     *
     * @return array
     */
    protected function parseKey(string $key)
    {
        return preg_split('/[ .]/', Str::upper($key));
    }

    /**
     * @param string      $key
     * @param string|null $locale
     *
     * @return string
     */
    public function get(string $key, string $locale = null)
    {
        list($country, $state, $city) = $this->parseKey($key);

        $locale = $locale ?? $this->fallbackLocale;

        $this->load($country, $state, $locale);

        if ($this->has($country, $state, $locale, $city)) {
            return $this->loaded[$country][$state][$locale][$city];
        }

        return $key;
    }

    /**
     * @param string      $key
     * @param string|null $locale
     *
     * @return string
     */
    public function getName(string $key, string $locale = null)
    {
        return $this->get($key, $locale);
    }

    /**
     * @param string $country
     * @param string $state
     * @param string $locale
     */
    protected function load(string $country, string $state, string $locale)
    {
        if ($this->isLoaded($country, $state, $locale)) {
            return;
        }

        $this->loaded[$country][$state][$locale] = $this->loader->load($country, $state, $locale);
    }

    /**
     * @param string $country
     * @param string $state
     * @param string $locale
     * @param string $city
     *
     * @return bool
     */
    protected function has(string $country, string $state, string $locale, string $city)
    {
        return isset($this->loaded[$country][$state][$locale][$city]);
    }
}
