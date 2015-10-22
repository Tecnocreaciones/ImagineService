<?php

namespace Tecnoready\ImagineService\Imagine\Cache;

interface CacheManagerAwareInterface
{
    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager);
}
