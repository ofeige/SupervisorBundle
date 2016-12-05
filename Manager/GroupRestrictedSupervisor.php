<?php

namespace YZ\SupervisorBundle\Manager;

use Supervisor\Supervisor;

/**
 * GroupRestrictedSupervisor.
 */
class GroupRestrictedSupervisor extends Supervisor
{
    /**
     * @var array
     */
    protected $groups;

    /**
     * The constructor.
     *
     * @param string $name
     * @param string $ipAddress The server ip adress
     * @param string $username  Default set to null
     * @param string $password  Default set to null
     * @param int    $port      Default set to null
     * @param array  $groups    Groups to limit this supervisor to
     */
    public function __construct($name, $ipAddress, $username = null, $password = null, $port = null, $groups = [])
    {
        $this->groups = array_filter($groups);

        parent::__construct($name, $ipAddress, $username, $password, $port);
    }

    /**
     * getProcesses.
     *
     * @param array $groups Only show processes in these process groups.
     *
     * @return Process[]
     */
    public function getProcesses($groups = [])
    {
        return parent::getProcesses(empty($groups) ? $this->groups : $groups);
    }

    /**
     * Start all processes listed in the configuration file.
     *
     * @param bool $wait Wait for each process to be fully started
     *
     * @return array result An array containing start statuses
     */
    public function startAllProcesses($wait = true)
    {
        if (empty($this->groups)) {
            return parent::startAllProcesses($wait);
        }

        $results = [];

        foreach ($this->groups as $group) {
            $results = array_merge($results, parent::startProcessGroup($group, $wait));
        }

        return $results;
    }

    /**
     * Stop all processes listed in the configuration file.
     *
     * @param bool $wait Wait for each process to be fully stoped
     *
     * @return array result An array containing start statuses
     */
    public function stopAllProcesses($wait = true)
    {
        if (empty($this->groups)) {
            return parent::stopAllProcesses($wait);
        }

        $results = [];

        foreach ($this->groups as $group) {
            $results = array_merge($results, parent::stopProcessGroup($group, $wait));
        }

        return $results;
    }
}
