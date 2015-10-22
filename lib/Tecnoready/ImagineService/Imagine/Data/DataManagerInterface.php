<?php

/*
 * This file is part of the BtoB Rewards package.
 * 
 * (c) www.btobrewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\ImagineService\Imagine\Data;

/**
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
interface DataManagerInterface 
{
    public function addLoader($filter, \Tecnoready\ImagineService\Binary\Loader\LoaderInterface $loader);
    
    public function getLoader($filter);
    
    public function find($filter, $path);
    
    public function getDefaultImageUrl($filter);
}
