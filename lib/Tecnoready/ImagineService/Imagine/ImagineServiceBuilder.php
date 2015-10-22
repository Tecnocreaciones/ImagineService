<?php

/*
 * This file is part of the BtoB Rewards package.
 * 
 * (c) www.btobrewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\ImagineService\Imagine;

use Imagine\Gd\Imagine as Imagine2;
use Imagine\Imagick\Imagine;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Tecnoready\ImagineService\Binary\SimpleMimeTypeGuesser;
use Tecnoready\ImagineService\Imagine\Cache\CacheManagerInterface;
use Tecnoready\ImagineService\Imagine\Cache\Signer;
use Tecnoready\ImagineService\Imagine\Cache\SignerInterface;
use Tecnoready\ImagineService\Imagine\Data\DataManager;
use Tecnoready\ImagineService\Imagine\Filter\FilterConfiguration;
use Tecnoready\ImagineService\Imagine\Filter\FilterManager;

/**
 * Ayudante para construir servicio de imagnees
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class ImagineServiceBuilder 
{
    const DRIVE_GD = "gd";
    const DRIVE_IMAGICK = "imagick";
    
    /**
     * Secret signer
     * @var String
     */
    private $secret;
    
    /**
     *
     * @var SignerInterface
     */
    private $signer;
    private $imagine;
    private $drive;
    
    /**
     *
     * @var FilterConfiguration
     */
    private $filterConfig;
    
    /**
     *
     * @var CacheManagerInterface
     */
    private $cacheManager;
    private $cacheManagerClass;

    public function withDrive($drive) {
        $this->drive = $drive;
        
        if($drive == self::DRIVE_GD){
            $this->imagine = new Imagine2();
        }else if($drive == self::DRIVE_IMAGICK){
            $this->imagine = new Imagine();
        }
        
        return $this;
    }
    
    public function withSecret($secret) {
        $this->secret = $secret;
        return $this;
    }
        
    public function withFilterConfig(FilterConfiguration $filterConfig) {
        $this->filterConfig = $filterConfig;
        return $this;
    }

    public function withCacheManagerClass($cacheManagerClass) {
        $this->cacheManagerClass = $cacheManagerClass;
        return $this;
    }
            
    public function withImagine($imagine) {
        $this->imagine = $imagine;
        return $this;
    }
    
    public function withSigner(SignerInterface $signer) {
        $this->signer = $signer;
        return $this;
    }
    
    /**
     * 
     * @return \Tecnoready\ImagineService\Imagine\ImagineService
     */
    public function build()
    {
        if(!$this->signer){
            $this->signer = new Signer($this->secret);
        }
        
        $filterConfig = $this->filterConfig;
        $signer = $this->signer;
        
        $cacheManagerClass = $this->cacheManagerClass;
        if($this->cacheManagerClass){
            $this->cacheManager = new $cacheManagerClass($filterConfig, $signer);
        }
        $cacheManager = $this->cacheManager;

        $simpleMimeTypeGuesser = new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());
        $extensionGuesser = ExtensionGuesser::getInstance();
        $dataManager = new DataManager($simpleMimeTypeGuesser, $extensionGuesser, $filterConfig);

        $filterManager = new FilterManager($filterConfig, $this->imagine, $simpleMimeTypeGuesser);

        $app = new ImagineService($cacheManager,$dataManager,$filterManager,$signer);
        
        return $app;
    }
}
