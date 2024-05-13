<?php

namespace Drupal\purge;

/**
 * Interface of a cache tag minificator.
 *
 * There is an upper limit in most web-servers / proxies on HTTP header length.
 * Thus we minify cache tags to fit as much as possible into the imposed limit.
 */
interface CacheTagMinificatorInterface {

  /**
   * Minify a given cache tag.
   *
   * Create a hash with the given input.
   *
   * @see hook_purge_cache_tag_minify_dictionary()
   *
   * @param string $cache_tag
   *   Cache tag to minify.
   *
   * @return string
   *   Minified cache tag.
   */
  public function minifyCacheTag($cache_tag);

}
