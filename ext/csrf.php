<?php

/**
 * Tiny: PHP Framework
 * https://github.com/ranaroussi/tiny
 *
 * Copyright 2013-2024 Ran Aroussi (@aroussi)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

declare(strict_types=1);

/**
 * # usage
 * before rendering the form
 * tiny::csrf()->generate();
 *
 * # in view:
 * tiny::csrf()->input(); // will also generate the token if not already generated
 *
 * # on submit
 * if (!tiny::csrf()->isValid()) {
 *     return $response->sendJSON(['error' => 'Invalid CSRF token'], 403);
 * }
 */

class TinyCSRF
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    private ?string $token = null;

    /**
     * Generates a new CSRF token and stores it in the session.
     *
     * This method creates a cryptographically secure random token,
     * saves it in the session, and assigns it to the class property.
     *
     * @return string The generated CSRF token.
     */
    public function generate(): string
    {
        $this->token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $this->token;
        return $this->token;
    }

    /**
     * Validates the submitted CSRF token against the stored token.
     *
     * This method checks if the submitted token matches the one stored in the session.
     * If valid, it removes the token from the session to prevent reuse.
     *
     * @return bool True if the token is valid, false otherwise.
     */
    public function isValid(): bool
    {
        $token = $_POST[self::TOKEN_NAME] ?? $_GET[self::TOKEN_NAME] ?? null;
        $storedToken = $_SESSION[self::TOKEN_NAME] ?? null;

        if (!$token || !$storedToken || !hash_equals($storedToken, $token)) {
            return false;
        }

        unset($_SESSION[self::TOKEN_NAME]);
        $this->token = null;
        return true;
    }


    /**
     * Generates an HTML input field containing the CSRF token.
     *
     * This method creates a hidden input field with the CSRF token as its value.
     * It can either echo the field directly or return it as a string.
     *
     * @param bool $echo Whether to echo the input field (true) or return it as a string (false).
     * @return string|null The HTML input field as a string if $echo is false, null otherwise.
     */
    public function input(bool $echo = true): ?string
    {
        $token = $this->token ?? $this->generate();
        $field = sprintf('<input type="hidden" name="%s" value="%s">', self::TOKEN_NAME, htmlspecialchars($token, ENT_QUOTES, 'UTF-8'));

        if ($echo) {
            echo $field;
            return null;
        }

        return $field;
    }
}
