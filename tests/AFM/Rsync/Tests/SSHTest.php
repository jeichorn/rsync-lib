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

use AFM\Rsync\SSH;

class SSHTest extends \PHPUnit\Framework\TestCase
{
	public function testValidConfiguration()
	{
		$fakePublicKey = __DIR__ . '/fake_key.pub';

		touch($fakePublicKey);

		new SSH(array('port' => 1443, 'public_key' => $fakePublicKey));

		$this->assertTrue(true);

		unlink($fakePublicKey);
	}

	public function testInvalidPublicKey()
	{
        $this->expectException(\InvalidArgumentException::class);
		new SSH(array('public_key' => '/cant/read!'));
	}

	public function testInvalidPortNumber()
	{
        $this->expectException(\InvalidArgumentException::class);
		new SSH(array('port' => 'not_a_number'));
	}

	public function testGetConnectionString()
	{
		$ssh = new SSH(array('username' => 'test', 'host' => 'test.com'));

		$actual = $ssh->getCommand();
		$expected = "ssh test@test.com";

		$this->assertEquals($expected, $actual);
	}

	public function testGetConnectionNonStandardPort()
	{
		$ssh = new SSH(array('username' => 'test', 'host' => 'test.com', 'port' => 231));

		$actual = (string)$ssh->getCommand();
		$expected = "ssh -p 231 test@test.com";

		$this->assertEquals($expected, $actual);
	}

	public function testGetConnectionWithPublicKey()
	{
		$publicKey = "./key.pub";
		$publicKeyWithSpaces = "./key key.pub";

		touch($publicKey);
		touch($publicKeyWithSpaces);

		$ssh = new SSH(array('username' => 'test', 'host' => 'test.com', 'public_key' => $publicKey));

		$actual = (string)$ssh->getCommand();
		$expected = "ssh -i '" .$publicKey. "' test@test.com";

		$this->assertEquals($expected, $actual);

		$ssh->setPublicKey($publicKeyWithSpaces);

		$actual = (string)$ssh->getCommand();
		$expected = "ssh -i '" .$publicKeyWithSpaces. "' test@test.com";

		$this->assertEquals($expected, $actual);

		unlink($publicKey);
		unlink($publicKeyWithSpaces);
	}

	public function testGetHostConnection()
	{
		$ssh = new SSH(array('username' => 'test', 'host' => 'test.com'));

		$actual = $ssh->getHostConnection();
		$expected = "test@test.com";

		$this->assertEquals($expected, $actual);
	}

	public function testGetConnectionOptions()
	{
		$ssh = new SSH(array('username' => 'test', 'host' => 'test.com', 'port' => 231, 'public_key' => '/dev/null'));

		$actual = $ssh->getConnectionOptions();
		$expected = "ssh -p 231 -i '/dev/null'";

		$this->assertEquals($expected, $actual);
	}

	public function testGetConnectionNoUsername()
	{
        $this->expectException(\InvalidArgumentException::class);
		$ssh = new SSH;

		$ssh->getCommand();
	}

	public function testGetConnectionNoHost()
	{
        $this->expectException(\InvalidArgumentException::class);
		$ssh = new SSH(array('username' => 'test'));

		$ssh->getCommand();
	}

	public function testSetExecutable()
	{
  		$ssh = new SSH(array('username' => 'test', 'host' => 'test.com', 'port' => 231, 'executable' => 'c:/cygwin/bin/ssh.exe'));

	        $actual   = $ssh->getConnectionOptions();
	        $expected = "c:/cygwin/bin/ssh.exe -p 231";

        	$this->assertEquals($expected, $actual);
	}
}

