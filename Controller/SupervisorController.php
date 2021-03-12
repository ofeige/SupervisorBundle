<?php

namespace YZ\SupervisorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use YZ\SupervisorBundle\Manager\SupervisorManager;

/**
 * SupervisorController
 */
class SupervisorController extends Controller
{
    private static $publicInformations = ['description', 'group', 'name', 'state', 'statename'];

    private $supervisorManager;
    private $translator;

    public function __construct(SupervisorManager $supervisorManager, TranslatorInterface $translator)
    {
        $this->supervisorManager = $supervisorManager;
        $this->translator = $translator;
    }

    /**
     * indexAction
     */
    public function indexAction()
    {
        $uniqueSupervisors = [];
        $processGroups = [];
        $supervisorProcesses = [];

        $supervisorManager = $this->supervisorManager;
        $supervisorSupervisors = $supervisorManager->getSupervisors();
        $supervisorKeys = array_keys($supervisorSupervisors);

        foreach ($supervisorKeys as $value) {
            $uniqueSupervisors[] = $supervisorSupervisors[$value]->getProcesses();
        }

        foreach ($uniqueSupervisors as $value) {
            $supervisorProcesses = array_merge($supervisorProcesses, $value);
        }

        foreach ($supervisorProcesses as $value) {
            $processGroups[] = $value->getGroup();
        }

        return $this->render('@YZSupervisor/Supervisor/list.html.twig', array(
            'supervisors' => $supervisorManager->getSupervisors(),
            'groups' => array_unique($processGroups),
        ));
    }

    /**
     * startStopProcessAction
     *
     * @param string  $start 1 to start, 0 to stop it
     * @param string  $key   The key to retrieve a Supervisor object
     * @param string  $name  The name of a process
     * @param string  $group The group of a process
     * @param Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response represents an HTTP response.
     * @throws \Exception
     */
    public function startStopProcessAction($start, $key, $name, $group, Request $request)
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        $success = true;
        $process = $supervisor->getProcessByNameAndGroup($name, $group);
        try {
            if ($start == "1") {
                $success = $process->startProcess();
            } elseif ($start == "0") {
                $success = $process->stopProcess();
            } else {
                $success = false;
            }

        } catch (\Exception $e) {
            $success = false;
        }

        if (!$success) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->translator->trans(
                    ($start == "1" ? 'process.start.error' : 'process.stop.error'),
                    array(),
                    'YZSupervisorBundle'
                )
            );
        }

        if ($request->isXmlHttpRequest()) {
            $processInfo = $process->getProcessInfo();
            $res = json_encode([
                'success'       => $success,
                'message'       => implode(', ', $this->get('session')->getFlashBag()->get('error', array())),
                'processInfo'   => $processInfo
            ]);

            return new Response($res, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }

    /**
     * startStopAllProcessesAction
     *
     * @param Request $request
     * @param string $start 1 to start, 0 to stop it
     * @param string $key The key to retrieve a Supervisor object
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function startStopAllProcessesAction(Request $request, $start, $key)
    {
        $supervisor = $this->supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        $processesInfo = true;
        if ($start == "1") {
            $processesInfo = $supervisor->startAllProcesses(false);
        } elseif ($start == "0") {
            $processesInfo = $supervisor->stopAllProcesses(false);
        }

        if ($request->isXmlHttpRequest()) {
            $res = json_encode([
                'processesInfo' => $processesInfo
            ]);

            return new Response($res, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }

    /**
     * showSupervisorLogAction
     *
     * @param string $key The key to retrieve a Supervisor object
     */
    public function showSupervisorLogAction($key)
    {
        $supervisorManager = $this->supervisorManager;
        $supervisor = $supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        $logs = $supervisor->readLog(0, 0);

        return $this->render('@YZSupervisor/Supervisor/showLog.html.twig', array(
            'log' => $logs,
        ));
    }

    /**
     * clearSupervisorLogAction
     *
     * @param string $key The key to retrieve a Supervisor object
     */
    public function clearSupervisorLogAction($key)
    {
        $supervisorManager = $this->supervisorManager;
        $supervisor = $supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        if ($supervisor->clearLog() !== true) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->translator->trans('logs.delete.error', array(), 'YZSupervisorBundle')
            );
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }

    /**
     * showProcessLogAction
     *
     * @param string $key   The key to retrieve a Supervisor object
     * @param string $name  The name of a process
     * @param string $group The group of a process
     */
    public function showProcessLogAction($key, $name, $group)
    {
        $supervisorManager = $this->supervisorManager;
        $supervisor = $supervisorManager->getSupervisorByKey($key);
        $process = $supervisor->getProcessByNameAndGroup($name, $group);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        $result = $process->tailProcessStdoutLog(0, 1);
        $stdout = $process->tailProcessStdoutLog(0, $result[1]);

        return $this->render('@YZSupervisor/Supervisor/showLog.html.twig', array(
            'log' => $stdout[0],
        ));
    }

    /**
     * showProcessLogErrAction
     *
     * @param string $key   The key to retrieve a Supervisor object
     * @param string $name  The name of a process
     * @param string $group The group of a process
     */
    public function showProcessLogErrAction($key, $name, $group)
    {
        $supervisorManager = $this->supervisorManager;
        $supervisor = $supervisorManager->getSupervisorByKey($key);
        $process = $supervisor->getProcessByNameAndGroup($name, $group);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        $result = $process->tailProcessStderrLog(0, 1);
        $stderr = $process->tailProcessStderrLog(0, $result[1]);

        return $this->render('@YZSupervisor/Supervisor/showLog.html.twig', array(
            'log' => $stderr[0],
        ));
    }

    /**
     * clearProcessLogAction
     *
     * @param string $key   The key to retrieve a Supervisor object
     * @param string $name  The name of a process
     * @param string $group The group of a process
     */
    public function clearProcessLogAction($key, $name, $group)
    {
        $supervisorManager = $this->supervisorManager;
        $supervisor = $supervisorManager->getSupervisorByKey($key);
        $process = $supervisor->getProcessByNameAndGroup($name, $group);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        if ($process->clearProcessLogs() !== true) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->translator->trans('logs.delete.error', array(), 'YZSupervisorBundle')
            );
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }

    /**
     * showProcessInfoAction
     *
     * @param string  $key   The key to retrieve a Supervisor object
     * @param string  $name  The name of a process
     * @param string  $group The group of a process
     * @param Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response represents an HTTP response.
     * @throws \Exception
     */
    public function showProcessInfoAction($key, $name, $group, Request $request)
    {
        $supervisorManager = $this->supervisorManager;
        $supervisor = $supervisorManager->getSupervisorByKey($key);
        $process = $supervisor->getProcessByNameAndGroup($name, $group);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        $infos = $process->getProcessInfo();

        if ($request->isXmlHttpRequest()) {
            $processInfo = [];
            foreach (self::$publicInformations as $public) {
                $processInfo[$public] = $infos[$public];
            }

            $res = json_encode([
                'supervisor'    => $key,
                'processInfo'   => $processInfo,
                'controlLink'   => $this->generateUrl('supervisor.process.startStop', [
                    'key'   => $key,
                    'name'  => $name,
                    'group' => $group,
                    'start' => ($infos['state'] == 10 || $infos['state'] == 20 ? '0' : '1')
                ])
            ]);

            return new Response($res, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $this->render('@YZSupervisor/Supervisor/showInformations.html.twig', array(
            'informations' => $infos,
        ));
    }

    /**
     * showProcessAllInfoAction
     *
     * @param string  $key The key to retrieve a Supervisor object
     * @param Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response represents an HTTP response.
     * @throws \Exception
     */
    public function showProcessInfoAllAction($key, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new \Exception('Ajax request expected here');
        }

        $supervisorManager = $this->supervisorManager;
        $supervisor = $supervisorManager->getSupervisorByKey($key);

        if (!$supervisor) {
            throw new \Exception('Supervisor not found');
        }

        $processes = $supervisor->getProcesses();
        $processesInfo = [];
        foreach ($processes as $process) {
            $infos = $process->getProcessInfo();
            $processInfo = [];
            foreach (self::$publicInformations as $public) {
                $processInfo[$public] = $infos[$public];
            }

            $processesInfo[$infos['name']] = [
                'supervisor'    => $key,
                'processInfo'   => $processInfo,
                'controlLink'   => $this->generateUrl('supervisor.process.startStop', [
                    'key'   => $key,
                    'name'  => $infos['name'],
                    'group' => $infos['group'],
                    'start' => ($infos['state'] == 10 || $infos['state'] == 20 ? '0' : '1')
                ])
            ];
        }

        $res = json_encode($processesInfo);

        return new Response($res, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * startStopGroupProcesses
     *
     * @param string  $start 1 to start, 0 to stop it
     * @param string  $group The group of a process
     * @param Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response represents an HTTP response.
     * @throws \Exception
     */
    public function startStopGroupProcess($group, $start, Request $request)
    {
        $supervisorProcesses = [];

        $supervisorManager = $this->supervisorManager;
        $supervisorKeys = array_keys($supervisorManager->getSupervisors());

        foreach ($supervisorKeys as $value) {
            $supervisorProcesses[] = $supervisorManager->getSupervisors()[$value]->getProcesses();
        }

        $selectedProcesses = [];

        foreach ($supervisorProcesses as $value) {
            foreach ($value as $subValue) {
                $selectedGroup = $subValue->getGroup();
                if ($selectedGroup === $group) {
                    $selectedProcesses[] = $subValue;
                }
            }
        }

        if (!$supervisorManager) {
            throw new \Exception('Supervisor not found');
        }

        $success = true;
        try {
            foreach ($selectedProcesses as $value) {
                if ($start == "1") {
                    $success &= $value->startProcess();
                } elseif ($start == "0") {
                    $success &= $value->stopProcess();
                } else {
                    $success = false;
                }
            }

        } catch (\Exception $e) {
            $success = false;
        }

        if (!$success) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->translator->trans(
                    ($start == "1" ? 'process.start.error' : 'process.stop.error'),
                    array(),
                    'YZSupervisorBundle'
                )
            );
        }

        $processInfo = [];

        if ($request->isXmlHttpRequest()) {
            foreach ($selectedProcesses as $value) {
                $processInfo[] = $value->getProcessInfo();
            }
            $res = json_encode([
                'success'       => $success,
                'message'       => implode(', ', $this->get('session')->getFlashBag()->get('error', array())),
                'processInfo'   => $processInfo
            ]);

            return new Response($res, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $this->redirect($this->generateUrl('supervisor'));
    }
}
