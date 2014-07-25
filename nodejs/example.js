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

function get_sso_link() {
  var CROWDIN_USER_API_KEY = ' -- OWNERS API KEY -- ';
  var CROWDIN_USER_LOGIN = ' -- OWNERS LOGIN -- ';
  var CROWDIN_BASEPATH = 'https://crowdin.net/join';

  var CROWDIN_LINK = CROWDIN_BASEPATH + '?h=' + encrypt(get_user_data(), CROWDIN_USER_API_KEY) + '&uid=' + CROWDIN_USER_LOGIN;
  
  if(CROWDIN_LINK.length > 2000) {
    throw Exeption('Link is to long.');
  }
  
  return CROWDIN_LINK;
}

function get_user_data() {
  var timestamp = new Date().getTime();

  return {
    user_id: "12345678901",
    login: "johndoe",
    user_email: "john.doe@mail.com",
    display_name: "John Doe",
    locale: "en_US",
    gender: 1,
    projects: "docx-project,csv-project",
    expiration: 20 * 60 + (timestamp / 1000),
    languages: "uk,ro,fr",
    role: 0,
    redirect_to: "https://crowdin.net/project/docx-project"
  };
}

function encrypt(data, api_key) {
  var input_encoding = 'utf8';
  var output_encoding = 'base64';

  var crypto = require('crypto');
  var algorithm = 'aes-128-cbc';

  var key = api_key.toString().substr(0, 16);
  var iv = api_key.toString().substr(16, 16);

  data = JSON.stringify(data).toString(input_encoding);

  var chunks = [];
  var cipher = crypto.createCipheriv(algorithm, key, iv);

  chunks.push(cipher.update(data, input_encoding, output_encoding));
  chunks.push(cipher.final(output_encoding));

  return encodeURIComponent(chunks.join());
}
