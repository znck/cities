<?php
namespace Znck\Tests\Cities;

use Illuminate\Filesystem\Filesystem;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Znck\Cities\FileLoader;

class FileLoaderTest extends PHPUnit_Framework_TestCase
{
    public function test_it_can_load()
    {
        $loader = new FileLoader(new Filesystem(), dirname(__DIR__).'/data');

        $this->assertTrue(is_array($loader->load('in', 'as', 'en')));
    }

    public function test_it_throws_if_locale_does_not_exist()
    {
        $loader = new FileLoader(new Filesystem(), dirname(__DIR__).'/data');

        $this->expectException(NotFoundResourceException::class);

        $loader->load('in', 'as', 'does_not_exist');
    }

    public function test_it_should_throw_error_invalid_resource_exception()
    {
        $loader = new FileLoader(new Filesystem(), dirname(__DIR__).'/test-data');

        $this->expectException(InvalidResourceException::class);

        $loader->load('in', 'as', 'en');
    }

    public function test_it_should_throw_error_invalid_resource_exception_too()
    {
        $loader = new FileLoader(new Filesystem(), dirname(__DIR__).'/test-data');

        $this->expectException(InvalidResourceException::class);

        $loader->load('in', 'rj', 'en');
    }

    public function test_it_loads_correct_data()
    {
        $loader = new FileLoader(new Filesystem(), dirname(__DIR__).'/data');

        $this->assertArrayHasKey('BAD', $loader->load('in', 'as', 'en'));
    }

    public function test_it_can_load_with_slash_at_end_of_path()
    {
        $loader = new FileLoader(new Filesystem(), dirname(__DIR__).'/data/');

        $this->assertTrue(is_array($loader->load('in', 'as', 'en')));
    }
}
