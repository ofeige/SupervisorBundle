<?php

namespace YZ\SupervisorBundle\Manager;

use Supervisor\Exception\SupervisorException;
use Supervisor\Supervisor;

/**
 * GroupRestrictedSupervisor.
 */
class GroupRestrictedSupervisor
{
    protected array $groups;

    protected string $name;

    protected string $key;

    protected Supervisor $supervisor;

    /**
     * The constructor.
     *
     * @param Supervisor $supervisor
     * @param string $name
     * @param string $key
     * @param array<int, string>  $groups    Groups to limit this supervisor to
     */
    public function __construct(Supervisor $supervisor, string $name, string $key, array $groups = [])
    {
        $this->supervisor = $supervisor;
        $this->groups = array_filter($groups);
        $this->key = $key;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * getProcesses.
     *
     * @param array<int, string> $groups Only show processes in these process groups.
     *
     * @return SupervisorProcess[]
     */
    public function getProcesses($groups = []): array
    {
        $processes = [];
        $groups = $groups ?: $this->groups;

        $result = $this->supervisor->getAllProcessInfo();
        foreach ($result as $cnt => $process) {
            // Skip process when process group not listed in $groups
            if (!empty($groups) && !in_array($process['group'], $groups)) {
                continue;
            }

            $processes[$cnt] = $this->getProcessByNameAndGroup($process['name'], $process['group']);
        }

        return $processes;
    }

    public function getProcessByNameAndGroup(string $name, string $group): SupervisorProcess
    {
        return new SupervisorProcess($name, $group, $this->supervisor);
    }

    /**
     * Start all processes listed in the configuration file.
     *
     * @param bool $wait Wait for each process to be fully started
     *
     * @return array<int, bool> result An array containing start statuses
     */
    public function startAllProcesses($wait = true): array
    {
        if (empty($this->groups)) {
            return [$this->supervisor->startAllProcesses($wait)];
        }

        $results = [];

        foreach ($this->groups as $group) {
            $results = array_merge($results, $this->supervisor->startProcessGroup($group, $wait));
        }

        return $results;
    }

    /**
     * Stop all processes listed in the configuration file.
     *
     * @param bool $wait Wait for each process to be fully stoped
     *
     * @return array<int, bool> result An array containing start statuses
     */
    public function stopAllProcesses($wait = true)
    {
        if (empty($this->groups)) {
            return [$this->supervisor->stopAllProcesses($wait)];
        }

        $results = [];

        foreach ($this->groups as $group) {
            $results = array_merge($results, $this->supervisor->stopProcessGroup($group, $wait));
        }

        return $results;
    }

    public function checkConnection(): bool
    {
        try {
            $this->supervisor->getState();

            return true;
        } catch (SupervisorException $e) {
            return false;
        }
    }

    public function getSupervisorVersion(): string
    {
        return $this->supervisor->getSupervisorVersion();
    }

    public function getAPIVersion(): string
    {
        return $this->supervisor->getAPIVersion();
    }

    public function readLog(int $offset, int $limit): string
    {
        return $this->supervisor->readLog($offset, $limit);
    }

    public function clearLog(): bool
    {
        return $this->supervisor->clearLog();
    }
}
