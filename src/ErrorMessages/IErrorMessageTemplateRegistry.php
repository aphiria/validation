<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\ErrorMessages;

/**
 * Defines the interface for error message template registries to implement
 */
interface IErrorMessageTemplateRegistry
{
    /**
     * Gets the error message template associated with a particular ID
     *
     * @param string $errorMessageId The ID of the error message template we are looking for
     * @param string|null $locale The optional locale
     * @return string The error message template
     */
    public function getErrorMessageTemplate(string $errorMessageId, string $locale = null): string;
}
