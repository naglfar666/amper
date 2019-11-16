<?php
namespace Amper;

class Queue {
  /**
   * Проверка наличия в таблице обрабатываемых задач по ключу
   * @param string $key Ключ задачи
   */
  public static function searchHandled(string $key)
  {
    $Redis = Redis::create();
    return $Redis->hget('amper_queue_handled', $key);
  }
  /**
   * Обрабатываем массив задач
   */
  public static function handleTasks($tasks)
  {
    $Redis = Redis::create();
    if (
      is_array($tasks) 
      && count($tasks) > 0
    ) {
      foreach($tasks as $taskKey => $taskContent) {
        if (self::searchHandled($taskKey)) continue;
        $Redis->hset('amper_queue_handled', $taskKey, $taskContent);
        $Redis->hset('amper_queue_handle_start', $taskKey, time());
        $Redis->hdel('amper_queue_unhandled', $taskKey);

        $taskPayload = json_decode($taskContent, true);
        $dispatcherName = '\\App\\Dispatchers\\' . $taskPayload['dispatcher'];

        if (class_exists($dispatcherName)) {
          $Dispatcher = new $dispatcherName();
          if (method_exists($Dispatcher, 'handle')) {
            try {
              if ($Dispatcher->handle($taskPayload)) {
                $Redis->hdel('amper_queue_handled', $taskKey);
                $Redis->hdel('amper_queue_handle_start', $taskKey);
                continue;
              }
            } catch (\Throwable $th) {
              echo 'Задача ' . $taskKey . ' не была обработана по причине: ' . $th->getMessage() . PHP_EOL;
            }
            
          }
        }

        $Redis->hset('amper_queue_unhandled', $taskKey, $taskContent);
        $Redis->hdel('amper_queue_handled', $taskKey);
        $Redis->hdel('amper_queue_handle_start', $taskKey);
      }
    }
  }
  /**
   * Добавление операции в очередь на выполнение
   * @param string $dispatcher Обработчик задачи
   * @param int $priority Приоритет выполнения задачи
   * @param array $payload Полезная нагрузка, передаваемая в обработчик
   */
  public static function push(string $dispatcher, int $priority, array $payload) : void
  {
    if ($priority > Core::$QueueConfig['max_priority'] || $priority < 0) {
      throw new Exception(
        'Приоритет выполнения указан неверно. Максимальный - ' . Core::$QueueConfig['max_priority'] . ' Минимальный - 0'
      );
      
    }
    $Redis = Redis::create();

    $DispatcherName = Utils\QueueHandler::buildDispatcherName($dispatcher);

    $payload['dispatcher'] = $dispatcher;
    $payload['priority'] = $priority;

    $Redis->hset(
      'amper_queue_unhandled', 
      $DispatcherName . ':' . Utils\Crypto::uniqid(13)  . ':' . time() . ':' . $priority,
      json_encode($payload, JSON_UNESCAPED_UNICODE)
    );
  }
  /**
   * Проверяем все необработанные задачи
   */
  public static function checkUnhandled() : void
  {
    $Redis = Redis::create();
    $Tasks = $Redis->hscan('amper_queue_handle_start', 0);
    
    $maxDispatchTime = (Core::$QueueConfig['max_dispatch_time'])? Core::$QueueConfig['max_dispatch_time'] : 300;
    if (
      is_array($Tasks[1])
      && count($Tasks[1]) > 0
    ) {
      foreach ($Tasks[1] as $key => $value) {
        if (($value + $maxDispatchTime) > time()) continue;
        $task = $Redis->hget('amper_queue_handled', $key);
        if ($task) {
          $Redis->hset('amper_queue_unhandled', $key, $task);
          $Redis->hdel('amper_queue_handled', $key);
        }
        $Redis->hdel('amper_queue_handle_start', $key);
      }
    }
    
  }
  /**
   * Исполнение всех заданий в очереди
   */
  public static function dispatchAll() : void
  {
    self::checkUnhandled();
    $maxPriority = Core::$QueueConfig['max_priority'];
    $currentPriority = $maxPriority;

    $Redis = Redis::create();
    while ($currentPriority >= 0) {
      foreach (Core::$QueueConfig['dispatchers'] as $dispatcher) {
        $scanKeys = $Redis->hScan('amper_queue_unhandled', 0, [
          'match' => Utils\QueueHandler::buildDispatcherName($dispatcher) . ':*:*:' . $currentPriority, 
          'count' => Core::$QueueConfig['dispatched_amount']
        ]);
        self::handleTasks($scanKeys[1]);
      }
      $currentPriority--;
    }
  }
  /**
   * Исполнение задач по одному диспатчеру
   */
  public static function dispatchSingle(string $dispatcher, int $priority)
  {
    self::checkUnhandled();
    $Redis = Redis::create();
    $scanKeys = $Redis->hScan('amper_queue_unhandled', 0, [
      'match' => Utils\QueueHandler::buildDispatcherName($dispatcher) . ':*:*:' . $priority, 
      'count' => Core::$QueueConfig['dispatched_amount']
    ]);
    self::handleTasks($scanKeys[1]);
  }

}

?>