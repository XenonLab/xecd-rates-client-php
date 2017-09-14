<?php

namespace Xe\Xecd\Client\Rates;

use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Xe\Framework\Client\BaseClient\Aspects\DeserializableAspect;
use Xe\Framework\Client\BaseClient\Aspects\SerializableAspect;

class XecdRatesClientAspectKernel extends AspectKernel
{
    /**
     * {@inheritdoc}
     */
    public function init(array $options = [])
    {
        $options['includePaths'] = isset($options['includePaths']) ? $options['includePaths'] : [];
        if (!in_array(__DIR__.'/XecdRatesClient.php', $options['includePaths'])) {
            $options['includePaths'][] = __DIR__.'/XecdRatesClient.php';
        }

        parent::init($options);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureAop(AspectContainer $container)
    {
        $container->registerAspect(new DeserializableAspect());
        $container->registerAspect(new SerializableAspect());
    }
}
