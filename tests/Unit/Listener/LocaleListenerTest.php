<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Tests\Unit\Listener;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Maba\Bundle\RestBundle\Listener\LocaleListener;
use Maba\Bundle\RestBundle\Service\RestRequestHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LocaleListenerTest extends MockeryTestCase
{
    /**
     * @dataProvider provider
     *
     * @param string $expectedLocale
     * @param array $locales
     * @param string $acceptLanguage
     * @param bool $rest
     */
    public function testOnKernelRequest(string $expectedLocale, array $locales, string $acceptLanguage, bool $rest)
    {
        $helper = Mockery::mock(RestRequestHelper::class);
        $kernel = Mockery::mock(HttpKernelInterface::class);
        $listener = new LocaleListener($helper, $locales);
        $request = new Request();
        $request->setLocale('unchanged');
        $request->headers->set('Accept-Language', $acceptLanguage);

        $helper->shouldReceive('isRestRequest')->with($request)->andReturn($rest);

        if (class_exists('Symfony\Component\HttpKernel\Event\RequestEvent')) {
            $event = new RequestEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        } else {
            $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);
        }

        $listener->onKernelRequest($event);

        $this->assertSame($expectedLocale, $request->getLocale());
    }

    public function provider()
    {
        return [
            [
                'unchanged',
                ['en'],
                'en',
                false,
            ],
            [
                'en',
                ['en'],
                'en',
                true,
            ],
            [
                'unchanged',
                ['de'],
                'en',
                true,
            ],
            [
                'de',
                ['de'],
                'en, lt;q=0.8, de;q=0.9',
                true,
            ],
            [
                'de',
                ['de'],
                'de-CH',
                true,
            ],
            [
                'de',
                ['de'],
                'en, de-CH',
                true,
            ],
            [
                'en',
                ['de', 'en'],
                'en, de-CH',
                true,
            ],
            'higher priority wins' => [
                'de',
                ['de', 'en'],
                'en-US,en;q=0.8, de-CH;q=0.9',
                true,
            ],
        ];
    }
}
