<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSP_Symfony\Component\Process\Tests;

use PHPUnit\Framework\TestCase;
use FSP_Symfony\Component\Process\ExecutableFinder;

/**
 * @author Chris Smith <chris@cs278.org>
 */
class ExecutableFinderTest extends TestCase
{
    private $path;

    protected function tearDown()
    {
        if ($this->path) {
            // Restore path if it was changed.
            putenv('PATH='.$this->path);
        }
    }

    private function setPath($path)
    {
        $this->path = getenv('PATH');
        putenv('PATH='.$path);
    }

    public function testFind()
    {
        if (ini_get('open_basedir')) {
            $this->markTestSkipped('Cannot test when open_basedir is set');
        }

        $this->setPath(dirname(PHP_BINARY));

        $finder = new ExecutableFinder();
        $result = $finder->find($this->getPhpBinaryName());

        $this->assertSamePath(PHP_BINARY, $result);
    }

    public function testFindWithDefault()
    {
        if (ini_get('open_basedir')) {
            $this->markTestSkipped('Cannot test when open_basedir is set');
        }

        $expected = 'defaultValue';

        $this->setPath('');

        $finder = new ExecutableFinder();
        $result = $finder->find('foo', $expected);

        $this->assertEquals($expected, $result);
    }

    public function testFindWithExtraDirs()
    {
        if (ini_get('open_basedir')) {
            $this->markTestSkipped('Cannot test when open_basedir is set');
        }

        $this->setPath('');

        $extraDirs = array(dirname(PHP_BINARY));

        $finder = new ExecutableFinder();
        $result = $finder->find($this->getPhpBinaryName(), null, $extraDirs);

        $this->assertSamePath(PHP_BINARY, $result);
    }

    public function testFindWithOpenBaseDir()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot run test on windows');
        }

        if (ini_get('open_basedir')) {
            $this->markTestSkipped('Cannot test when open_basedir is set');
        }

        $this->iniSet('open_basedir', dirname(PHP_BINARY).(!defined('HHVM_VERSION') || HHVM_VERSION_ID >= 30800 ? PATH_SEPARATOR.'/' : ''));

        $finder = new ExecutableFinder();
        $result = $finder->find($this->getPhpBinaryName());

        $this->assertSamePath(PHP_BINARY, $result);
    }

    public function testFindProcessInOpenBasedir()
    {
        if (ini_get('open_basedir')) {
            $this->markTestSkipped('Cannot test when open_basedir is set');
        }
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Cannot run test on windows');
        }

        $this->setPath('');
        $this->iniSet('open_basedir', PHP_BINARY.(!defined('HHVM_VERSION') || HHVM_VERSION_ID >= 30800 ? PATH_SEPARATOR.'/' : ''));

        $finder = new ExecutableFinder();
        $result = $finder->find($this->getPhpBinaryName(), false);

        $this->assertSamePath(PHP_BINARY, $result);
    }

    private function assertSamePath($expected, $tested)
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertEquals(strtolower($expected), strtolower($tested));
        } else {
            $this->assertEquals($expected, $tested);
        }
    }

    private function getPhpBinaryName()
    {
        return basename(PHP_BINARY, '\\' === DIRECTORY_SEPARATOR ? '.exe' : '');
    }
}
