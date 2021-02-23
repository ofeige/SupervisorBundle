<?php

namespace YZ\SupervisorBundle\Manager;

class SupervisorManager
{
    /**
     * @var GroupRestrictedSupervisor[]
     */
    private $supervisors = [];

    /**
     * Constuctor
     *
     * @param GroupRestrictedSupervisor[] $supervisors Configuration in the symfony parameters
     */
    public function __construct(array $supervisors)
    {
        foreach ($supervisors as $supervisor) {
            $this->addSupervisor($supervisor);
        }
    }

    private function addSupervisor(GroupRestrictedSupervisor $supervisor): void
    {
        $this->supervisors[$supervisor->getKey()] = $supervisor;
    }

    /**
     * Get all supervisors
     *
     * @return GroupRestrictedSupervisor[]
     */
    public function getSupervisors(): array
    {
        return $this->supervisors;
    }

    /**
     * Get Supervisor by key
     *
     * @param string $key
     *
     * @return GroupRestrictedSupervisor|null
     */
    public function getSupervisorByKey(string $key): ?GroupRestrictedSupervisor
    {
        if (isset($this->supervisors[$key])) {
            return $this->supervisors[$key];
        }

        return null;
    }
}
