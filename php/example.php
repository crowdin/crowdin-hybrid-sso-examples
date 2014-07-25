<?php
/**
 * Copyright 2014 Crowdin LLC
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

class crowdin_custom_sso {
  // Crowdin account credentials
  const CROWDIN_USER_LOGIN = ' -- OWNERS LOGIN -- ';      // your login name in Crowdin
  const CROWDIN_USER_API_KEY = ' -- OWNERS API KEY -- ';  // your account API key (can be found here https://crowdin.net/settings#api-key)
  const CROWDIN_BASEPATH = 'https://crowdin.net/join';    // usually no need to change

  private $cipher = MCRYPT_RIJNDAEL_128;
  private $mode = MCRYPT_MODE_CBC;

  public function get_sso_link() {
    $hash = $this->encrypt($this->get_user_data());

    $link = sprintf('%s?h=%s&uid=%s', self::CROWDIN_BASEPATH, $hash, self::CROWDIN_USER_LOGIN);

    if(strlen($link) > 2000) {
      throw new Exeption('Link is too long.');
    }

    return $link;
  }

  private function get_user_data() {
    return array(
      'user_id' => '12345678901',
      'login' => 'JohnDoe',
      'user_email' => 'john.doe@mail.com',
      'display_name' => 'John Doe',
      'locale' => 'en_US',
      'gender' => 1,
      'languages' => 'uk,ro,fr',
      'role' => 0,
      'projects' => implode(',', array('docx-project', 'csv-project')),
      'expiration' => strtotime('+20 minutes'),
      'redirect_to' => 'https://crowdin.net/project/docx-project'
    );
  }

  private function encrypt($parameters) {
    $block_size = mcrypt_get_block_size($this->cipher, $this->mode);
    $key = substr(self::CROWDIN_USER_API_KEY, 0, 16);
    $iv = substr(self::CROWDIN_USER_API_KEY, 16, 16);

    $data = json_encode($parameters);
    $padding = $block_size - (strlen($data) % $block_size);

    $encrypted_data = mcrypt_encrypt($this->cipher, $key, $data . str_repeat(chr($padding), $padding), $this->mode, $iv);

    return urlencode(base64_encode($encrypted_data));
  }
}
