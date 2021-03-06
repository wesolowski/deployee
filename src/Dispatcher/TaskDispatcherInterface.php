<?php


namespace Deployee\Dispatcher;


use Deployee\Tasks\TaskInterface;

interface TaskDispatcherInterface
{
    /**
     * @param TaskInterface $task
     * @return mixed
     */
    public function canDispatch(TaskInterface $task);

    /**
     * @param TaskInterface $task
     * @return TaskDispatchResultInterface
     */
    public function dispatch(TaskInterface $task);
}