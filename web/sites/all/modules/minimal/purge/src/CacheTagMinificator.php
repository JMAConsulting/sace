<?php

namespace Drupal\purge;

/**
 * Cache tag minificator based on static dictionary.
 */
class CacheTagMinificator implements CacheTagMinificatorInterface {

  /**
   * {@inheritdoc}
   */
  public function minifyCacheTag($cache_tag) {
    $length = 4;
    // MD5 is the fastest algorithm beyond CRC32 (which is 30% faster, but high
    // collision risk), so this is the best bet for now. If collisions are going
    // to be a major problem in the future, we might have to consider a hash DB.
    $raw = hash('md5', $cache_tag, TRUE);
    // Convert to base64url format.
    $hash = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($raw));
    return substr($hash, 0, $length);
  }
}
