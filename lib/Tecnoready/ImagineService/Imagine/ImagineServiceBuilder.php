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
    const DRIVE_GMAGICK = "gmagick";
    
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
    
    /**
     * Default resolver path
     * @var Cache\Resolver\ResolverInterface
     */
    private $defaultResolver;
    
    /**
     * Raiz de la carpeta publica
     * @var string 
     */
    private $webRootDir;
    
    private $options;
    
    public function __construct(array $options = array()) {
        $this->setOptions($options);
    }
    
    public function withDrive($drive) {
        $this->drive = $drive;
        
        if($drive == self::DRIVE_GD){
            $this->imagine = new \Imagine\Gd\Imagine();
        }else if($drive == self::DRIVE_IMAGICK){
            $this->imagine = new \Imagine\Imagick\Imagine();
        }else if($drive == self::DRIVE_GMAGICK){
            $this->imagine = new \Imagine\Gmagick\Imagine();
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
    
    public function withWebRootDir($webRootDir) {
        $this->webRootDir = $webRootDir;
        return $this;
    }
        
    public function withDefaultResolver(Cache\Resolver\ResolverInterface $defaultResolver) {
        $this->defaultResolver = $defaultResolver;
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
        if(!$this->defaultResolver){
            $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            $filesystem = new \Symfony\Component\Filesystem\Filesystem();
            $this->defaultResolver = new Cache\Resolver\WebPathResolver(
                    $filesystem, 
                    $request, 
                    $this->options['web_root_dir'],
                    $this->options['cache_prefix']
                );
        }
        
        $cacheManager = $this->cacheManager;
        $cacheManager->addResolver("default", $this->defaultResolver);

        $mimeTypeGuesser = new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());
        $extensionGuesser = ExtensionGuesser::getInstance();
        
        $loader = new \Tecnoready\ImagineService\Binary\Loader\FileSystemLoader(MimeTypeGuesser::getInstance(),$extensionGuesser,$this->options['web_root_dir']);
        $dataManager = new DataManager($mimeTypeGuesser, $extensionGuesser, $filterConfig,'default');
        $dataManager->addLoader("default", $loader);

        //init filters image
        $filterManager = new FilterManager($filterConfig, $this->imagine, $mimeTypeGuesser);
        $filterManager->addLoader('auto_rotate', new Filter\Loader\AutoRotateFilterLoader());
        $filterManager->addLoader('background', new Filter\Loader\BackgroundFilterLoader($this->imagine));
        $filterManager->addLoader('crop', new Filter\Loader\CropFilterLoader());
        $filterManager->addLoader('interlace', new Filter\Loader\InterlaceFilterLoader());
        $filterManager->addLoader('paste', new Filter\Loader\PasteFilterLoader($this->imagine, $this->options['web_root_dir']));
        $filterManager->addLoader('relative_resize', new Filter\Loader\RelativeResizeFilterLoader());
        $filterManager->addLoader('resize', new Filter\Loader\ResizeFilterLoader());
        $filterManager->addLoader('rotate', new Filter\Loader\RotateFilterLoader());
        $filterManager->addLoader('strip', new Filter\Loader\StripFilterLoader());
        $filterManager->addLoader('thumbnail', new Filter\Loader\ThumbnailFilterLoader());
        $filterManager->addLoader('upscale', new Filter\Loader\UpscaleFilterLoader());
        $filterManager->addLoader('watermark', new Filter\Loader\WatermarkFilterLoader($this->imagine, $this->options['web_root_dir']));

        $app = new ImagineService($cacheManager,$dataManager,$filterManager,$signer);
        
        return $app;
    }
    
    /**
     * Sets options.
     *
     * Available options:
     *
     *   * cache_dir:     The cache directory (or null to disable caching)
     *   * debug:         Whether to enable debugging or not (false by default)
     *   * resource_type: Type hint for the main resource (optional)
     *
     * @param array $options An array of options
     *
     * @throws \InvalidArgumentException When unsupported option is provided
     */
    public function setOptions(array $options)
    {
        $this->options = array(
            'cache_prefix' => 'media/cache',
            'web_root_dir' => null,
            'debug' => false,
        );

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = array();
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new \InvalidArgumentException(sprintf('The Image Service does not support the following options: "%s".', implode('", "', $invalid)));
        }
    }
}
