<?php
/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\EventListener;

use Bukashk0zzz\LiipImagineSerializationBundle\Annotation\LiipImagineSerializableField;
use Doctrine\Common\Annotations\CachedReader;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Routing\RequestContext;
use Doctrine\Common\Persistence\Proxy;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * JmsSerializeListenerAbstract
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class JmsSerializeListenerAbstract
{
    /**
     * @var RequestContext $requestContext Request context
     */
    protected $requestContext;

    /**
     * @var CachedReader $annotationReader Cached annotation reader
     */
    protected $annotationReader;

    /**
     * @var CacheManager $cacheManager LiipImagineBundle Cache Manager
     */
    protected $cacheManager;

    /**
     * @var StorageInterface $storage Vich storage
     */
    protected $vichStorage;

    /**
     * @var array $config Bundle config
     */
    protected $config;

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * JmsSerializeListenerAbstract constructor.
     *
     * @param RequestContext   $requestContext
     * @param CachedReader     $annotationReader
     * @param CacheManager     $cacheManager
     * @param StorageInterface $vichStorage
     * @param array            $config
     */
    public function __construct(
        RequestContext $requestContext,
        CachedReader $annotationReader,
        CacheManager $cacheManager,
        StorageInterface $vichStorage,
        array $config
    ) {
        $this->requestContext = $requestContext;
        $this->annotationReader = $annotationReader;
        $this->cacheManager = $cacheManager;
        $this->vichStorage = $vichStorage;
        $this->config = $config;
    }

    /**
     * @param ObjectEvent $event Event
     * @return mixed
     */
    protected function getObject(ObjectEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof Proxy
            && ! $object->__isInitialized()
        ) {
            $object->__load();
        }

        return $object;
    }

    /** @noinspection GenericObjectTypeUsageInspection
     * @param LiipImagineSerializableField $liipAnnotation
     * @param object $object Serialized object
     * @param string $value Value of field
     * @return array|string
     */
    protected function serializeValue(LiipImagineSerializableField $liipAnnotation, $object, $value)
    {
        if ($vichField = $liipAnnotation->getVichUploaderField()) {
            $value = $this->vichStorage->resolveUri($object, $vichField);
        }

        $result = [];
        if (array_key_exists('includeOriginal', $this->config) && $this->config['includeOriginal']) {
            if (array_key_exists('includeHost', $this->config) && $this->config['includeHost']) {
                $result['original'] = $this->getHostUrl().$value;
            } else {
                $result['original'] = $value;
            }
        }

        $filters = $liipAnnotation->getFilter();
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $result[$filter] = $this->cacheManager->getBrowserPath($value, $filter);
            }

            return $result;
        }

        $filtered = $this->cacheManager->getBrowserPath($value, $filters);
        if (count($result) !== 0) {
            $result[$filters] = $filtered;

            return $result;
        }

        return $filtered;
    }

    /**
     * Get host url (scheme, host, port)
     *
     * @return string Host url
     */
    protected function getHostUrl()
    {
        $url = $this->requestContext->getScheme().'://'.$this->requestContext->getHost();

        if ($this->requestContext->getScheme() === 'http' && $this->requestContext->getHttpPort() && $this->requestContext->getHttpPort() !== 80) {
            $url .= ':'.$this->requestContext->getHttpPort();
        } elseif ($this->requestContext->getScheme() === 'https' && $this->requestContext->getHttpsPort() && $this->requestContext->getHttpsPort() !== 443) {
            $url .= ':'.$this->requestContext->getHttpsPort();
        }

        return $url;
    }
}
