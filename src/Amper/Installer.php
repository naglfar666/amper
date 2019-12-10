<?php


namespace Amper;


class Installer
{
  /**
   * Initiate process of project installation
   */
  public function start() : void
  {
    $this->seekDirectory('../../example', '');
  }

  /**
   * Recursive copy of files and directories
   * @param $Dir
   * @param $Prefix
   */
  private function seekDirectory($Dir, $Prefix) : void
  {
    $Files = scandir($Dir);

    foreach ($Files as $file) {
      if (is_dir($file)) {
        $Prefix = '/' . $file;
        $this->seekDirectory($Dir . $Prefix, $Prefix);
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
    if (!file_exists(GLOBAL_DIR . $Prefix . '/' . $File)) {
      if (copy($Dir . '/' . $File, GLOBAL_DIR . $Prefix . '/' . $File)) {
        echo ' + File ' . GLOBAL_DIR . $Prefix . '/' . $File . ' installed' . PHP_EOL;
      } else {
        echo ' - File ' . GLOBAL_DIR . $Prefix . '/' . $File . ' cannot be installed' . PHP_EOL;
      }
    } else {
      echo ' - File ' . GLOBAL_DIR . $Prefix . '/' . $File . ' already exists' . PHP_EOL;
    }
  }
}