<?php namespace Znck\Tests\Cities;

use Illuminate\Filesystem\Filesystem;
use Znck\Cities\FileLoader;
use Znck\Cities\Translator;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_works()
    {
        $translator = new Translator(new FileLoader(new Filesystem(), dirname(__DIR__).'/data'), 'en');

        $this->assertEquals('Guwahati', $translator->getName('in.as.guw'));
    }

    public function test_it_works_too()
    {
        $translator = new Translator(new FileLoader(new Filesystem(), dirname(__DIR__).'/data'), 'en');

        $this->assertEquals('Guwahati', $translator->get('in.as.guw'));
        $this->assertEquals('Guwahati', $translator->get('in as guw'));
        $this->assertEquals('Guwahati', $translator->get('IN AS GUW'));

        $this->assertEquals('in.as.guwa', $translator->get('in.as.guwa'));
    }
}
