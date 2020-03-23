<?php
/**
 * Copyright 2019 Crowdin
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class CrowdinCustomSso
{
    // Crowdin account credentials
    private const CROWDIN_USER_LOGIN = ' -- OWNERS LOGIN -- ';      // your login name in Crowdin
    private const CROWDIN_USER_API_KEY = ' -- OWNERS API KEY -- ';  // your account API key (can be found here https://crowdin.com/settings#api-key)
    private const CROWDIN_BASEPATH = 'https://crowdin.com/join';    // usually no need to change

    private static $cipher = 'aes-128-cbc';

    public function getSsoLink(): string
    {
        $hash = $this->encrypt($this->getUserData());

        $link = sprintf('%s?h=%s&uid=%s', self::CROWDIN_BASEPATH, $hash, self::CROWDIN_USER_LOGIN);

        if (strlen($link) > 2000) {
            throw new Exception('Link is too long.');
        }

        return $link;
    }

    private function getUserData(): array
    {
        return [
            'user_id' => '12345678901',
            'login' => 'JohnDoe',
            'user_email' => 'john.doe@mail.com',
            'display_name' => 'John Doe',
            'locale' => 'en_US',
            'gender' => 1,
            'languages' => 'uk,ro,fr',
            'role' => 0,
            'projects' => implode(',', ['docx-project', 'csv-project']),
            'expiration' => strtotime('+20 minutes'),
            'redirect_to' => 'https://crowdin.com/project/docx-project'
        ];
    }

    private function encrypt(array $parameters): string
    {
        $key = substr(self::CROWDIN_USER_API_KEY, 0, 16);
        $iv = substr(self::CROWDIN_USER_API_KEY, 16, 16);

        $data = json_encode($parameters);

        $encryptedData = openssl_encrypt(
            $data,
            self::$cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return urlencode(base64_encode($encryptedData));
    }
}
