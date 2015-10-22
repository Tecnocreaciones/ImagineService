<?php

/*
 * This file is part of the BtoB Rewards package.
 * 
 * (c) www.btobrewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\ImagineService\Imagine\Cache;

/**
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
interface CacheManagerInterface 
{
    public function addResolver($filter, Resolver\ResolverInterface $resolver);
    
    function getResolver($filter);
    
    public function getBrowserPath($path, $filter, array $runtimeConfig = array());
    
    public function getRuntimePath($path, array $runtimeConfig);
    
    public function generateUrl($path, $filter, array $runtimeConfig = array());
    
    public function isStored($path, $filter);
    
    public function resolve($path, $filter);
    
    public function store(BinaryInterface $binary, $path, $filter);
    
    public function remove($paths = null, $filters = null);
}
