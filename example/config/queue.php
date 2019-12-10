<?php
return [
  'max_priority' => 5, // Max allowed priority of task
  'max_dispatch_time' => 300, // Time to retry task in fail case
  'dispatched_amount' => 10, // Single-tick dispatched tasks amount
  'dispatchers' => [ // List of all dispatchers
    'ExampleDispatcher'
  ]

];
?>
