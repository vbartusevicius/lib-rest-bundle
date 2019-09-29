<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Listener;

use Maba\Bundle\RestBundle\Entity\RestRequestOptions;
use Maba\Bundle\RestBundle\Exception\ForbiddenApiException;
use Maba\Bundle\RestBundle\Exception\NotFoundApiException;
use Maba\Bundle\RestBundle\Service\ContentTypeMatcher;
use Maba\Bundle\RestBundle\Service\PathAttributeResolver\PathAttributeResolutionManager;
use Maba\Bundle\RestBundle\Service\RestRequestHelper;
use Maba\Bundle\RestBundle\Service\Validation\EntityValidator;
use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\ObjectWrapper\Exception\InvalidItemException;
use Symfony\Component\HttpFoundation\Request;
use Paysera\Component\Normalization\Exception\InvalidDataException;
use Maba\Bundle\RestBundle\Exception\ApiException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use stdClass;
use Exception;

/**
 * @internal
 */
class RestRequestListener
{
    private $coreDenormalizer;
    private $authorizationChecker;
    private $tokenStorage;
    private $requestHelper;
    private $entityValidator;
    private $contentTypeMatcher;
    private $pathAttributeResolutionManager;

    public function __construct(
        CoreDenormalizer $coreDenormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        RestRequestHelper $requestHelper,
        EntityValidator $entityValidator,
        ContentTypeMatcher $contentTypeMatcher,
        PathAttributeResolutionManager $pathAttributeResolutionManager
    ) {
        $this->coreDenormalizer = $coreDenormalizer;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->requestHelper = $requestHelper;
        $this->entityValidator = $entityValidator;
        $this->contentTypeMatcher = $contentTypeMatcher;
        $this->pathAttributeResolutionManager = $pathAttributeResolutionManager;
    }

    /**
     * Ran on kernel.controller event
     *
     * Both events are typecasted as one is deprecated from 4.3, but another not available before this version
     * @param FilterControllerEvent|ControllerEvent $event
     *
     * @throws ApiException
     * @throws InvalidDataException
     * @throws Exception
     */
    public function onKernelController($event)
    {
        $request = $event->getRequest();

        $options = $this->requestHelper->resolveRestRequestOptions($request, $event->getController());
        if ($options === null) {
            return;
        }

        $this->requestHelper->setOptionsForRequest($request, $options);

        $this->checkRequiredPermissions($options);

        $this->addAttributesFromURL($request, $options);
        $this->addAttributesFromQuery($request, $options);
        $this->addAttributesFromBody($request, $options);
    }

    private function checkRequiredPermissions(RestRequestOptions $options)
    {
        if (count($options->getRequiredPermissions()) === 0) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if ($token === null || $token instanceof AnonymousToken) {
            $exception = new ApiException(
                ApiException::UNAUTHORIZED,
                'This API endpoint requires authentication, none found'
            );
        } else {
            $exception = new ForbiddenApiException(
                'Access to this API endpoint is forbidden for current client'
            );
        }

        foreach ($options->getRequiredPermissions() as $permission) {
            if (!$this->authorizationChecker->isGranted($permission)) {
                throw $exception;
            }
        }
    }

    private function addAttributesFromURL(Request $request, RestRequestOptions $options)
    {
        foreach ($options->getPathAttributeResolverOptionsList() as $pathResolverOptions) {
            $attributeValue = $request->attributes->get($pathResolverOptions->getPathPartName());

            $value = $attributeValue !== null ? $this->pathAttributeResolutionManager->resolvePathAttribute(
                $attributeValue,
                $pathResolverOptions->getPathAttributeResolverType()
            ) : null;

            if ($value !== null) {
                $request->attributes->set($pathResolverOptions->getParameterName(), $value);
                continue;
            }

            if ($pathResolverOptions->isResolutionMandatory()) {
                throw new NotFoundApiException('Resource was not found');
            }
        }
    }

    /**
     * Handle request with request query mapper
     *
     * @param Request $request
     * @param RestRequestOptions $options
     *
     * @throws ApiException
     * @throws InvalidItemException
     */
    private function addAttributesFromQuery(Request $request, RestRequestOptions $options)
    {
        foreach ($options->getQueryResolverOptionsList() as $queryResolverOptions) {
            $context = new DenormalizationContext(
                $this->coreDenormalizer,
                $queryResolverOptions->getDenormalizationGroup()
            );
            $value = $this->coreDenormalizer->denormalize(
                $this->convertToObject($request->query->all()),
                $queryResolverOptions->getDenormalizationType(),
                $context
            );

            if ($queryResolverOptions->isValidationNeeded()) {
                $this->entityValidator->validate(
                    $value,
                    $queryResolverOptions->getValidationOptions()
                );
            }

            $request->attributes->set($queryResolverOptions->getParameterName(), $value);
        }
    }

    private function convertToObject(array $query): stdClass
    {
        return (object)json_decode(json_encode($query));
    }

    /**
     * Handles request with request mapper
     *
     * @param Request $request
     * @param RestRequestOptions $options
     *
     * @throws ApiException
     * @throws InvalidItemException
     */
    private function addAttributesFromBody(Request $request, RestRequestOptions $options)
    {
        if (!$options->hasBodyDenormalization()) {
            return;
        }

        $data = $this->decodeBody($request, $options);
        if ($data === null) {
            $this->handleEmptyRequestBody($options);
            return;
        }

        $context = new DenormalizationContext($this->coreDenormalizer, $options->getBodyDenormalizationGroup());
        $entity = $this->coreDenormalizer->denormalize(
            $data,
            $options->getBodyDenormalizationType(),
            $context
        );

        if ($options->isBodyValidationNeeded()) {
            $this->entityValidator->validate(
                $entity,
                $options->getBodyValidationOptions()
            );
        }

        $request->attributes->set($options->getBodyParameterName(), $entity);
    }

    private function decodeBody(Request $request, RestRequestOptions $options)
    {
        $content = $request->getContent();
        if ($content === '') {
            return null;
        }

        $contentType = $request->headers->get('CONTENT_TYPE', '');
        $contentTypeSupported = $this->contentTypeMatcher->isContentTypeSupported(
            $contentType,
            $options->getSupportedRequestContentTypes()
        );
        if (!$contentTypeSupported) {
            throw new ApiException(
                ApiException::INVALID_REQUEST,
                $contentType === ''
                    ? 'Content-Type must be provided'
                    : sprintf('This Content-Type (%s) is not supported', $contentType)
            );
        }

        if (!$options->isJsonEncodedBody()) {
            return $content;
        }

        $data = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                ApiException::INVALID_REQUEST,
                'Cannot decode request body to JSON'
            );
        }

        return $data;
    }

    private function handleEmptyRequestBody(RestRequestOptions $options)
    {
        if (!$options->isBodyOptional()) {
            throw new ApiException(ApiException::INVALID_REQUEST, 'Expected non-empty request body');
        }
    }
}
