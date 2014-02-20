<?php

/*
 * This file is part of rsync-lib
 *
 * (c) Alberto Fernández <albertofem@gmail.com>
 *
 * For the full copyright and license information, please read
 * the LICENSE file that was distributed with this source code.
 */

namespace AFM\Rsync;

/**
 * Rsync wrapper. Many options are not implemented,
 * but you can use setOptionalParameters
 *
 * @author Alberto Fernández <albertofem@gmail.com>
 */
class Rsync extends AbstractProtocol
{
	/**
	 * @var string
	 */
	protected $executable = "/usr/bin/rsync";

	/**
	 * @var bool
	 */
	protected $followSymLinks = true;

	/**
	 * @var bool
	 */
	protected $dryRun = false;

	/**
	 * @var array
	 */
	protected $optionalParameters = array();

	/**
	 * @var bool
	 */
	protected $verbose = false;

	/**
	 * @var bool
	 */
	protected $deleteFromTarget = false;

	/**
	 * @var bool
	 */
	protected $deleteExcluded = false;

	/**
	 * @var array
	 */
	protected $exclude = array();

	/**
	 * @var bool
	 */
	protected $recursive = true;

	/**
	 * @var bool
	 */
	protected $showOutput = true;

	/**
	 * @var SSH
	 */
	protected $ssh;

	/**
	 * Injects and validates config
	 *
	 * @param array $options
	 */
	public function __construct(Array $options = array())
	{
		$this->setOption($options, 'executable', 'setExecutable');
		$this->setOption($options, 'follow_symlinks', 'setFollowSymLinks');
		$this->setOption($options, 'dry_run', 'setDryRun');
		$this->setOption($options, 'option_parameters', 'setOptionalParameters');
		$this->setOption($options, 'verbose', 'setVerbose');
		$this->setOption($options, 'delete_from_target', 'setDeleteFromTarget');
		$this->setOption($options, 'delete_excluded', 'setDeleteExcluded');
		$this->setOption($options, 'exclude', 'setExclude');
		$this->setOption($options, 'recursive', 'setRecursive');
		$this->setOption($options, 'show_output', 'setShowOutput');
		$this->setOption($options, 'ssh', 'setSshOptions');
	}

	/**
	 * @param $options
	 */
	public function setSshOptions($options)
	{
		if(is_null($this->ssh))
			$this->ssh = new SSH($options);
	}

	/**
	 * Sync $origin directory with $target one.
	 * If SSH was configured, you must use absolute path
	 * in the target directory
	 *
	 * @param $origin
	 * @param $target
	 *
	 * @throws \InvalidArgumentException If the command failed
	 */
	public function sync($origin, $target)
	{
		$command = $this->getCommand($origin, $target);

		$command->execute($this->showOutput);
	}

	/**
	 * @return string
	 */
	public function getExecutable()
	{
		return $this->executable;
	}

	/**
	 * @param $followSymLinks
	 */
	public function setFollowSymLinks($followSymLinks)
	{
		$this->followSymLinks = $followSymLinks;
	}

	/**
	 * @return bool
	 */
	public function getFollowSymLinks()
	{
		return $this->followSymLinks;
	}

	/**
	 * @param $dryRun
	 */
	public function setDryRun($dryRun)
	{
		$this->dryRun = $dryRun;
	}

	/**
	 * @return bool
	 */
	public function getDryRun()
	{
		return $this->dryRun;
	}

	/**
	 * @param $optionalParameters
	 */
	public function setOptionalParameters($optionalParameters)
	{
		$this->optionalParameters = $optionalParameters;
	}

	/**
	 * @return array
	 */
	public function getOptionalParameters()
	{
		return $this->optionalParameters;
	}

	/**
	 * @param $verbose
	 */
	public function setVerbose($verbose)
	{
		$this->verbose = $verbose;
	}

	/**
	 * @return bool
	 */
	public function getVerbose()
	{
		return $this->verbose;
	}

	/**
	 * @param $deleteExcluded
	 */
	public function setDeleteExcluded($deleteExcluded)
	{
		$this->deleteExcluded = $deleteExcluded;
	}

	/**
	 * @return bool
	 */
	public function getDeleteExcluded()
	{
		return $this->deleteExcluded;
	}

	/**
	 * @param $deleteFromTarget
	 */
	public function setDeleteFromTarget($deleteFromTarget)
	{
		$this->deleteFromTarget = $deleteFromTarget;
	}

	/**
	 * @return bool
	 */
	public function getDeleteFromTarget()
	{
		return $this->deleteFromTarget;
	}

	/**
	 * @param $exclude
	 */
	public function setExclude($exclude)
	{
		$this->exclude = $exclude;
	}

	/**
	 * @return array
	 */
	public function getExclude()
	{
		return $this->exclude;
	}

	/**
	 * @param $recursive
	 */
	public function setRecursive($recursive)
	{
		$this->recursive = $recursive;
	}

	/**
	 * @return bool
	 */
	public function getRecursive()
	{
		return $this->recursive;
	}

	/**
	 * @param $showOutput
	 */
	public function setShowOutput($showOutput)
	{
		$this->showOutput = $showOutput;
	}

	/**
	 * @return bool
	 */
	public function getShowOutput()
	{
		return $this->showOutput;
	}

	/**
	 * Gets command generated for this current
	 * rsync configuration. You can use it to test
	 * or execute it later without using the sync method
	 *
	 * @param $origin
	 * @param $target
	 *
	 * @return Command
	 */
	public function getCommand($origin, $target)
	{
		$command = new Command($this->executable);

		if($this->followSymLinks)
			$command->addOption("L");

		if($this->dryRun)
			$command->addOption("n");

		if($this->verbose)
			$command->addOption("v");

		if($this->deleteFromTarget)
			$command->setArgument('delete');

		if($this->deleteExcluded)
			$command->setArgument('delete-excluded');

		if(!empty($this->exclude))
		{
			foreach($this->exclude as $excluded)
			{
				$command->addArgument('exclude', $excluded);
			}
		}

		if($this->recursive)
			$command->addOption("a");

		if(!is_null($this->ssh))
		{
			$ssh = $this->ssh->getConnectionOptions();
			$command->setArgument("rsh", $ssh);
		}

		$command->addParameter($origin);

		if(is_null($this->ssh))
			$command->addParameter($target);
		else
			$command->addParameter($this->ssh->getHostConnection() . ":" .$target);

		return $command;
	}
}
