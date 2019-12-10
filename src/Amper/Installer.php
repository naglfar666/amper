<?php


namespace Amper;


class Installer
{
  /**
   * Initiate process of project installation
   */
  public function start() : void
  {
    $this->seekDirectory(GLOBAL_DIR .'/vendor/naglfar/amper/example', '');
    $this->actualizeComposer();
  }

  /**
   * Recursive copy of files and directories
   * @param $Dir
   * @param $Prefix
   */
  private function seekDirectory($Dir, $Prefix) : void
  {
    echo 'Seek ' . $Dir . ' prefix ' . $Prefix . PHP_EOL;
    $Files = scandir($Dir);

    foreach ($Files as $file) {
      if ($file == '.' || $file == '..') continue;
      if (is_dir($Dir . '\\' . $file)) {
        $this->copyDir($Prefix . '\\' . $file);
        $this->seekDirectory($Dir . '\\' . $file, $Prefix . '\\' . $file);
      } else {
        $this->copyFile($Dir, $Prefix, $file);
      }
    }
  }

  /**
   * Copy file to project directory
   * @param $Dir
   * @param $Prefix
   * @param $File
   */
  private function copyFile($Dir, $Prefix, $File) : void
  {
    echo 'copyFile ' . $Dir . ' prefix ' . $Prefix . ' file ' . $File . PHP_EOL;
    if (!file_exists(GLOBAL_DIR . $Prefix . '\\' . $File)) {
      if (copy($Dir . '\\' . $File, GLOBAL_DIR . $Prefix . '\\' . $File)) {
        echo ' + File ' . GLOBAL_DIR . $Prefix . '\\' . $File . ' installed' . PHP_EOL;
      } else {
        echo ' - File ' . GLOBAL_DIR . $Prefix . '\\' . $File . ' cannot be installed' . PHP_EOL;
      }
    } else {
      echo ' - File ' . GLOBAL_DIR . $Prefix . '\\' . $File . ' already exists' . PHP_EOL;
    }
  }

  /**
   * Create directory for project
   * @param $Prefix
   */
  private function copyDir($Prefix) : void
  {
    echo 'copyDir ' . $Prefix . PHP_EOL;
    if (!is_dir(GLOBAL_DIR . $Prefix )) {
      echo ' + Create folder ' . GLOBAL_DIR . $Prefix . PHP_EOL;
      mkdir(GLOBAL_DIR . $Prefix);
    } else {
      echo ' - Folder ' . GLOBAL_DIR . $Prefix . ' already exists' . PHP_EOL;
    }
  }

  /**
   * Actualizing composer.json file
   */
  private function actualizeComposer()
  {
    $Data = json_decode(file_get_contents(GLOBAL_DIR . '/composer.json'), true);
    if (!$Data['autoload']['psr-4']['App\\']) {
      $Data['autoload']['psr-4']['App\\'] = 'app/';
      file_put_contents(GLOBAL_DIR . '/composer.json', json_encode($Data, JSON_UNESCAPED_UNICODE));
      exec('cd ' . GLOBAL_DIR . ' && composer dump-autoload -o');
      echo ' + Actualizing composer.json' . PHP_EOL;
    }
  }
}