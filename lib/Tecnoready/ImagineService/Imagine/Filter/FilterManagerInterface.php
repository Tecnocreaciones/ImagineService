<?php

/*
 * This file is part of the BtoB Rewards package.
 * 
 * (c) www.btobrewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\ImagineService\Imagine\Filter;

use Tecnoready\ImagineService\Binary\BinaryInterface;
use Tecnoready\ImagineService\Imagine\Filter\Loader\LoaderInterface;
use Tecnoready\ImagineService\Imagine\Filter\PostProcessor\PostProcessorInterface;

/**
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
interface FilterManagerInterface {
    public function addLoader($filter, LoaderInterface $loader);
    
    public function addPostProcessor($name, PostProcessorInterface $postProcessor);
    
    /**
     * @return FilterConfiguration Description
     */
    public function getFilterConfiguration();
    
    public function apply(BinaryInterface $binary, array $config);
    
    public function applyPostProcessors(BinaryInterface $binary, $config);
    
    public function applyFilter(BinaryInterface $binary, $filter, array $runtimeConfig = array());
}
