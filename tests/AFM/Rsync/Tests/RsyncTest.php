<?php

/*
 * This file is part of rsync-lib
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace AFM\Rsync\Tests;

use AFM\Rsync\Rsync;

class RsyncTest extends \PHPUnit\Framework\TestCase
{
    private static $targetDir;

    private static $sourceDir;

    public function setUp(): void
    {
        @rrmdir(self::$targetDir);
    }

    public static function setUpBeforeClass(): void
    {
        self::$sourceDir = __DIR__ . '/dir1';
        self::$targetDir = __DIR__ . '/dir2';

        @mkdir(self::$targetDir);
    }

    public static function tearDownAfterClass(): void
    {
        @rrmdir(self::$targetDir);
    }

    public function testValidExecutableLocation()
    {
        $rsync = new Rsync();
        $rsync->setExecutable('/usr/bin/rsync');

        $this->assertTrue(true);
    }

    public function testInvalidExecutableLocation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $rsync = new Rsync();
        $rsync->setExecutable('/usr/not/exists/rsync!!');
    }

    public function testFollowSymlinkOptions()
    {
        $rsync = new Rsync(['follow_symlinks' => true]);

        $this->assertTrue($rsync->getFollowSymLinks());
    }

    public function testBasicSync()
    {
        $rsync = new Rsync();

        $rsync->sync($this->getSourceDir() . '/*', $this->getTargetDir());

        $this->assertTrue(compare_directories($this->getSourceDir(), $this->getTargetDir()));
    }

    public function testRsyncWithSSHConnection()
    {
        $config = [
            'ssh' => [
                'username' => 'test',
                'host' => 'test.com',
                'port' => 2342,
            ],
        ];

        $rsync = new Rsync($config);

        $command = $rsync->getCommand('.', '/home/test/');

        $actual = $command->getCommand();
        $expected = "/usr/bin/rsync -La --rsh 'ssh -p 2342' . test@test.com:/home/test/";

        $this->assertEquals($expected, $actual);

        $this->markTestIncomplete('Tested SSH connection string, but cannot test real SSH connection sync!');
    }

    public function testRsyncWithSingleExclude()
    {
        $rsync = new Rsync();
        $rsync->setExclude(['exclude1']);

        $expected = "/usr/bin/rsync -La --exclude 'exclude1' /origin /target";
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithMultipleExcludes()
    {
        $rsync = new Rsync();
        $rsync->setExclude(['exclude1', 'exclude2', 'exclude3']);

        $expected = "/usr/bin/rsync -La --exclude 'exclude1' --exclude 'exclude2' --exclude 'exclude3' /origin /target";
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithExcludeFrom()
    {
        $rsync = new Rsync();
        $rsync->setExcludeFrom('rsync_exclude.txt');

        $expected = "/usr/bin/rsync -La --exclude-from 'rsync_exclude.txt' /origin /target";
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithTimes()
    {
        $rsync = new Rsync();
        $rsync->setTimes(true);

        $expected = '/usr/bin/rsync -La --times /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithCompression()
    {
        $rsync = new Rsync();
        $rsync->setCompression(true);

        $expected = '/usr/bin/rsync -Lza /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithOptionalParametersArray()
    {
        $rsync = new Rsync();
        $rsync->setOptionalParameters(['z', 'p']);

        $expected = '/usr/bin/rsync -Lzpa /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();
    }

    public function testRsyncWithOptionalParametersString()
    {
        $rsync = new Rsync();
        $rsync->setOptionalParameters('zp');

        $expected = '/usr/bin/rsync -Lzpa /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();
    }

    public function testRsyncWithInfo()
    {
        $rsync = new Rsync();
        $rsync->setInfo('all0');

        $expected = "/usr/bin/rsync -La --info 'all0' /origin /target";
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithCompareDest()
    {
        $rsync = new Rsync();
        $rsync->setCompareDest('/Path/To/File');

        $expected = "/usr/bin/rsync -La --compare-dest '/Path/To/File' /origin /target";
        $actual   = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithRemoveSourceFile()
    {
        $rsync = new Rsync();
        $rsync->setRemoveSource(true);

        $expected = '/usr/bin/rsync -La --remove-source-files /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithPruneEmptyDIrs()
    {
        $rsync = new Rsync();
        $rsync->setPruneEmptyDirs(true);

        $expected = '/usr/bin/rsync -La --prune-empty-dirs /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithOmitDirTimes()
    {
        $rsync = new Rsync();
        $rsync->setOmitDirTimes(true);

        $expected = '/usr/bin/rsync -La --omit-dir-times /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithDevices()
    {
        $rsync = new Rsync();
        $rsync->setDevices(true);

        $expected = '/usr/bin/rsync -La --devices /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithSpecials()
    {
        $rsync = new Rsync();
        $rsync->setSpecials(true);

        $expected = '/usr/bin/rsync -La --specials /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithLinks()
    {
        $rsync = new Rsync();
        $rsync->setLinks(true);

        $expected = '/usr/bin/rsync -La --links /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function testRsyncWithBwLimit()
    {
        $rsync = new Rsync();
        $rsync->setBwLimit('1000');

        $expected = '/usr/bin/rsync -La --bwLimit \'1000\' /origin /target';
        $actual = $rsync->getCommand('/origin', '/target')->getCommand();

        $this->assertEquals($expected, $actual);
    }

    public function getTargetDir()
    {
        return self::$targetDir;
    }

    public function getSourceDir()
    {
        return self::$sourceDir;
    }
}
