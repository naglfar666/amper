<?php
define('GLOBAL_DIR', __DIR__);
require_once(GLOBAL_DIR.'/config/bootstrapper.php');
// To hold your loop 24/7 and control memory leaks use pm2. Example: "pm2 start queue.php".
while (true) {
  Amper\Queue::dispatchAll(); // Dispatch all tasks by priority
//  Amper\Queue::dispatch($dispatcher, $priority); // Use this if you want to separate dispatching in multiple processes
}