<?php

class CRM_Purgelogs_Config {

  private static $_singleton = NULL;
  private $params = array();

  public static function &singleton() {
    if (self::$_singleton === NULL) {
      // first, attempt to get configuration object from cache
      $cache = CRM_Utils_Cache::singleton();
      self::$_singleton = $cache->get('CRM_Purgelogs_Config');
      // if not in cache, fire off config construction
      if (!self::$_singleton) {
        self::$_singleton = new CRM_Purgelogs_Config();
        self::$_singleton->_initialize();
        $cache->set('CRM_Purgelogs_Config', self::$_singleton);
      }
      else {
        self::$_singleton->_initialize();
      }
    }
    return self::$_singleton;
  }

  private function _initialize() {
    $this->params = Civi::settings()->get('purgelogs_config');
  }

  public function getParams($param = NULL) {
    if (isset($param)) {
      return isset($this->params[$param]) ? $this->params[$param] : NULL;
    }
    else {
      return $this->params;
    }
  }

  public function setParams($params = []) {
    Civi::settings()->set('purgelogs_config', $params);
  }

}
