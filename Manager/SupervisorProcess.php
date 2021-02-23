<?php
namespace YZ\SupervisorBundle\Manager;

use Supervisor\Supervisor;

class SupervisorProcess
{
    /**
     * @var string
     */
    protected $processName;
    /**
     * @var string
     */
    protected $processGroup;
    
    protected Supervisor $supervisor;

    /**
     * The constructor
     *
     * @param string $processName  Name of the process (used to retrieve info)
     * @param string $processGroup Name of the process group
     */
    public function __construct($processName, $processGroup, Supervisor $supervisor)
    {
        $this->processName = $processName;
        $this->processGroup = $processGroup;
        $this->supervisor = $supervisor;
    }
    /**
     * Get process name
     *
     * @return string
     */
    public function getName()
    {
        return $this->processName;
    }
    /**
     * Get process group
     *
     *  @return string
     */
    public function getGroup()
    {
        return $this->processGroup;
    }
    /**
     * Get info about a process
     *
     * @return array result An array containing data about the process
     */
    public function getProcessInfo()
    {
        return $this->supervisor->getProcessInfo($this->processGroup.':'.$this->processName);
    }
    /**
     * Start a process
     *
     *  @param boolean $wait Wait for process to be fully started
     *
     *  @return boolean Always true unless error
     */
    public function startProcess($wait = true)
    {
        return $this->supervisor->startProcess($this->processGroup.':'.$this->processName, $wait);
    }

    /**
     * Stop a process
     *
     *  @param boolean $wait Wait for process to be fully started
     *
     *  @return boolean Always true unless error
     */
    public function stopProcess($wait = true)
    {
        return $this->supervisor->stopProcess($this->processGroup.':'.$this->processName, $wait);
    }
    /**
     * Start all processes in the group
     *
     * @param boolean $wait Wait for each process to be fully started
     *
     * @return bool
     */
    public function startProcessGroup($wait = true): bool
    {
        return $this->supervisor->startProcessGroup($this->processGroup, $wait);
    }
    /**
     * Stop all processes in the group
     *
     * @param boolean $wait Wait for each process to be fully started
     *
     * @return boolean Always return true unless error.
     */
    public function stopProcessGroup($wait = true)
    {
        return $this->supervisor->stopProcessGroup($this->processGroup, $wait);
    }
    /**
     * Send a string of chars to the stdin of the process name.
     * If non-7-bit data is sent (unicode), it is encoded to utf-8 before being sent to the process' stdin.
     * If chars is not a string or is not unicode, raise INCORRECT_PARAMETERS. If the process is not running, raise NOT_RUNNING.
     * If the process' stdin cannot accept input (e.g. it was closed by the child process), raise NO_FILE.
     *
     * @param string $data The character data to send to the process
     *
     * @return boolean result Always return True unless error
     */
    public function sendProcessStdin($data)
    {
        return $this->supervisor->sendProcessStdin($this->processGroup.':'.$this->processName, $data);
    }
    /**
     * @todo
     */
    public function addProcessGroup()
    {
        throw new \Exception('Todo');
    }
    /**
     * @todo
     */
    public function removeProcessGroup()
    {
        throw new \Exception('Todo');
    }
    /**
     * Read length bytes from name's stdout log starting at offset
     *
     * @param integer $offset offset to start reading from
     * @param integer $length number of bytes to read from the log
     *
     * @return string Bytes of log
     */
    public function readProcessStdoutLog($offset, $length)
    {
        return $this->supervisor->readProcessStdoutLog($this->processGroup.':'.$this->processName, $offset, $length);
    }
    /**
     * Read length bytes from name's stderr log starting at offset
     *
     * @param integer $offset offset to start reading from
     * @param integer $length number of bytes to read from the log
     *
     * @return string Bytes of log
     */
    public function readProcessStderrLog($offset, $length)
    {
        return $this->supervisor->readProcessStderrLog($this->processGroup.':'.$this->processName, $offset, $length);
    }
    /**
     * Provides a more efficient way to tail the (stdout) log than readProcessStdoutLog().
     * Use readProcessStdoutLog() to read chunks and tailProcessStdoutLog() to tail.
     *
     * @param integer $offset offset to start reading from
     * @param integer $length number of bytes to read from the log
     *
     * @return array [string bytes, integer offset, bool overflow]
     */
    public function tailProcessStdoutLog($offset, $length): array
    {
        return $this->supervisor->tailProcessStdoutLog($this->processGroup.':'.$this->processName, $offset, $length);
    }
    /**
     * Provides a more efficient way to tail the (stderr) log than readProcessStderrLog(). *
     * Use readProcessStderrLog() to read chunks and tailProcessStderrLog() to tail.
     *
     * @param integer $offset offset to start reading from
     * @param integer $length number of bytes to read from the log
     *
     * @return array result [string bytes, integer offset, bool overflow]
     */
    public function tailProcessStderrLog($offset, $length)
    {
        return $this->supervisor->tailProcessStderrLog($this->processGroup.':'.$this->processName, $offset, $length);
    }
    /**
     * Clear processLlogs
     *
     * @return boolean result Always True unless error
     */
    public function clearProcessLogs()
    {
        return $this->supervisor->clearProcessLogs($this->processGroup.':'.$this->processName);
    }
}