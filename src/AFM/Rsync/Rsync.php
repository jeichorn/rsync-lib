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
	protected $archive = true;

	/**
	 * @var bool
	 */
	protected $skipNewerFiles = false;

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
	 * @var string
	 */
	protected $excludeFrom = null;

	/**
	 * @var bool
	 */
	protected $recursive = true;

	/**
	 * @var bool
	 */
	protected $times = false;

	/**
	 * @var stats
	 */
	protected $stats = false;

	/**
	 * @var bool
	 */
	protected $showOutput = true;

	/**
	 * @var bool
	 */
	protected $compression = false;

	/**
	 * @var bool
	 */
	protected $remoteOrigin = false;

	/**
	 * @var bool
	 */
	protected $removeSource = false;

	/**
	 * @var bool
	 */
	protected $info = false;

	/**
	 * @var bool
	 */
	protected $compareDest = false;

	/**
	 * @var bool
	 */
	protected $pruneEmptyDirs = false;

	/**
	 * @var bool
	 */
	protected $omitDirTimes = false;

    /**
     * @var bool
     */
    protected $devices = false;

    /**
     * @var bool
     */
    protected $specials = false;

    /**
     * @var bool
     */
    protected $links = false;

	/**
	 * @var SSH
	 */
	protected $ssh;

    /**
     * @var string
     */
    protected $chmod;

    /**
     * @var string
     */
    protected $chown;

    /**
     * @var bool
     */
    protected $itemizeChanges = false;

	/**
	 * Injects and validates config
	 *
	 * @param array $options
	 */
	public function __construct(Array $options = array())
	{
		$this->setOption($options, 'executable', 'setExecutable');
		$this->setOption($options, 'archive', 'setArchive');
		$this->setOption($options, 'update', 'setSkipNewerFiles');
		$this->setOption($options, 'follow_symlinks', 'setFollowSymLinks');
		$this->setOption($options, 'dry_run', 'setDryRun');
		$this->setOption($options, 'option_parameters', 'setOptionalParameters');
		$this->setOption($options, 'verbose', 'setVerbose');
		$this->setOption($options, 'delete_from_target', 'setDeleteFromTarget');
		$this->setOption($options, 'delete_excluded', 'setDeleteExcluded');
		$this->setOption($options, 'exclude', 'setExclude');
		$this->setOption($options, 'excludeFrom', 'setExcludeFrom');
		$this->setOption($options, 'recursive', 'setRecursive');
		$this->setOption($options, 'omit_dir_times', 'setOmitDirTimes');
		$this->setOption($options, 'devices', 'setDevices');
		$this->setOption($options, 'specials', 'specials');
		$this->setOption($options, 'links', 'links');
		$this->setOption($options, 'times', 'setTimes');
		$this->setOption($options, 'stats', 'setStats');
		$this->setOption($options, 'show_output', 'setShowOutput');
		$this->setOption($options, 'ssh', 'setSshOptions');
		$this->setOption($options, 'compression', 'setCompression');
		$this->setOption($options, 'remote_origin', 'setRemoteOrigin');
		$this->setOption($options, 'remove_source', 'setRemoveSource');
		$this->setOption($options, 'info', 'setInfo');
		$this->setOption($options, 'compare_dest', 'setCompareDest');
		$this->setOption($options, 'prune_empty_dirs', 'setPruneEmptyDirs');
		$this->setOption($options, 'chmod', 'setChmod');
		$this->setOption($options, 'chown', 'setChown');
		$this->setOption($options, 'itemize_changes', 'setItemizeChanges');
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
	 * @return bool
	 */
	public function getArchive()
	{
		return $this->archive;
	}

	/**
	 * @param $archive
	 */
	public function setArchive($archive)
	{
		$this->archive = $archive;
	}

    /**
	 * @return bool
	 */
    public function getPruneEmptyDirs()
    {
        return $this->pruneEmptyDirs;
    }

    /**
	 * @param $pruneEmptyDirs
	 */
    public function setPruneEmptyDirs($pruneEmptyDirs)
    {
        $this->pruneEmptyDirs = $pruneEmptyDirs;
    }


	/**
	 * @param $skipNewerFiles
	 */
	public function setSkipNewerFiles($skipNewerFiles)
	{
		$this->skipNewerFiles = $skipNewerFiles;
	}

	/**
	 * @return bool
	 */
	public function getSkipNewerFiles()
	{
		return $this->skipNewerFiles;
	}

    public function getOmitDirTimes(): bool
    {
        return $this->omitDirTimes;
    }

    public function setOmitDirTimes(bool $omitDirTimes)
    {
        $this->omitDirTimes = $omitDirTimes;
    }

    public function getDevices(): bool
    {
        return $this->devices;
    }

    public function setDevices(bool $devices)
    {
        $this->devices = $devices;
    }

    public function getSpecials(): bool
    {
        return $this->specials;
    }

    public function setSpecials(bool $specials)
    {
        $this->specials = $specials;
    }

    public function getLinks(): bool
    {
        return $this->links;
    }

    public function setLinks(bool $links)
    {
        $this->links = $links;
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
	 * @param $exclude
	 */
	public function setExcludeFrom($excludeFrom)
	{
		$this->excludeFrom = $excludeFrom;
	}

	/**
	 * @return string
	 */
	public function getExcludeFrom()
	{
		return $this->excludeFrom;
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
	 * @param bool $times
	 */
	public function setTimes($times)
	{
		$this->times = $times;
	}

	/**
	 * @return bool
	 */
	public function getTimes()
	{
		return $this->times;
	}

	/**
	 * @param bool $stats
	 */
	public function setStats($stats)
	{
		$this->stats = $stats;
	}

	/**
	 * @return bool
	 */
	public function getStats()
	{
		return $this->stats;
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
	 * @param $compression
	 */
	public function setCompression($compression)
	{
		$this->compression = $compression;
	}

	/**
	 * @return bool
	 */
	public function getCompression()
	{
		return $this->compression;
	}

	/**
	 * @param $remoteOrigin
	 */
	public function setRemoteOrigin($remoteOrigin)
	{
		$this->remoteOrigin = (bool) $remoteOrigin;
	}

	/**
	 * @return bool
	 */
	public function getRemoteOrigin()
	{
		return $this->remoteOrigin;
	}

	/**
	 * @param $removeSource
	 */
	public function setRemoveSource($removeSource)
	{
		$this->removeSource = (bool) $removeSource;
	}

	/**
	 * @return bool
	 */
	public function getRemoveSource()
	{
		return $this->removeSource;
	}

	/**
	 * @param $info
	 */
	public function setInfo($info)
	{
		$this->info = $info;
	}

	/**
	 * @return bool
	 */
	public function getInfo()
	{
		return $this->info;
	}

	/**
	 * @param $dest
	 */
	public function setCompareDest($dest)
	{
        $this->compareDest = $dest;
	}

	/**
	 * @return string
	 */
	public function getCompareDest()
	{
        return $this->compareDest;
	}

	/**
	 * @param string
	 */
	public function setChown($chown)
	{
        $this->chown = $chown;
	}

	/**
	 * @return string
	 */
	public function getChown()
	{
        return $this->chown;
	}


	/**
	 * @param string
	 */
	public function setChmod($chmod)
	{
        $this->chmod = $chmod;
	}


	/**
	 * @return string
	 */
	public function getChmod()
	{
        return $this->chmod;
	}

    /**
	 * @param bool
	 */
	public function setItemizeChanges($itemizeChanges)
	{
        $this->itemizeChanges = $itemizeChanges;
	}


	/**
     * @return bool
     */
	public function getItemizeChanges()
	{
        return $this->itemizeChanges;
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

		if($this->skipNewerFiles)
			$command->addOption("u");

		if($this->followSymLinks)
			$command->addOption("L");

		if($this->dryRun)
			$command->addOption("n");

		if($this->verbose)
			$command->addOption("v");

		if($this->compression)
			$command->addOption("z");

		// add any optional options we've specified
		$extra_options = $this->getOptionalParameters();
		if(!empty($extra_options))
		{
			// if the extra options were given as a flat string, then convert it to an array
			if (is_string($extra_options))
				$extra_options = str_split($extra_options);

			// add each extra option we've defined.
			if (is_array($extra_options))
			{
				foreach($extra_options as $option)
				{
					$command->addOption($option);
				}
			}
		}

		if($this->times)
			$command->addArgument('times');

		if($this->stats)
			$command->addArgument('stats');

		if($this->deleteFromTarget)
			$command->setArgument('delete');

		if($this->removeSource)
			$command->addArgument('remove-source-files');

		if($this->deleteExcluded)
			$command->setArgument('delete-excluded');

		if($this->info)
			$command->addArgument('info', $this->info);

		if ($this->compareDest)
			$command->addArgument('compare-dest', $this->compareDest);

		if(!empty($this->exclude))
		{
			foreach($this->exclude as $excluded)
			{
				$command->addArgument('exclude', $excluded);
			}
		}

		if(!empty($this->excludeFrom))
		{
			$command->addArgument('exclude-from', $this->excludeFrom);
		}

		if($this->archive)
			$command->addOption("a");

		if(!$this->archive && $this->recursive)
			$command->addOption("r");

		if($this->pruneEmptyDirs)
			$command->addArgument('prune-empty-dirs');

		if($this->omitDirTimes)
			$command->addArgument('omit-dir-times');

		if($this->devices)
			$command->addArgument('devices');

		if($this->specials)
			$command->addArgument('specials');

		if($this->links)
			$command->addArgument('links');

        if ($this->chown)
            $command->addArgument('chown', $this->chown);

        if ($this->chmod)
            $command->addArgument('chmod', $this->chmod);

        if ($this->itemizeChanges)
            $command->addArgument('itemize-changes');

		if(!is_null($this->ssh))
		{
			$ssh = $this->ssh->getConnectionOptions();
			$command->setArgument("rsh", $ssh);
		}

		if(is_null($this->ssh))
		{
			$command->addParameter($origin);
			$command->addParameter($target);
		}
		elseif($this->remoteOrigin)
		{
			$command->addParameter($this->ssh->getHostConnection() . ":" .$origin);
			$command->addParameter($target);
		}
		else
		{
			$command->addParameter($origin);
			$command->addParameter($this->ssh->getHostConnection() . ":" .$target);
		}

		return $command;
	}
}
