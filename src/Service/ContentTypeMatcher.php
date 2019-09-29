<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Service;

/**
 * @internal
 */
class ContentTypeMatcher
{
    /**
     * @param string $contentType
     * @param array $supportedContentTypes Can be `something/something`, `something/*` or `*` to allow all
     * @return bool
     */
    public function isContentTypeSupported(string $contentType, array $supportedContentTypes): bool
    {
        foreach ($supportedContentTypes as $availableContentType) {
            if ($contentType === $availableContentType) {
                return true;
            }

            if ($availableContentType === '*') {
                return true;
            }

            $availableParts = explode('/', $availableContentType, 2);
            $providedParts = explode('/', $contentType, 2);

            if (count($availableParts) < 2 || count($providedParts) < 2) {
                continue;
            }

            if ($availableParts[0] === $providedParts[0] && $availableParts[1] === '*') {
                return true;
            }
        }

        return false;
    }
}
