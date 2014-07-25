# encoding: UTF-8

# Copyright 2014 Crowdin LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
# http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

require 'openssl'
require 'base64'
require 'json'
require 'cgi'

class CrowdinCustomSSO
  # Crowdin account credentials
  CROWDIN_USER_LOGIN   = "W3RWOLF"     # your login name in Crowdin
  CROWDIN_USER_API_KEY = "f2d41e481ae62f31c2874b640c1a058b"    # your account API key (can be found here https://crowdin.net/settings#api-key)
  CROWDIN_BASEPATH     = "http://crowdin.net/join"  # usually no need to change

  def get_sso_link
    hash = encrypt(get_user_data())

    link = CROWDIN_BASEPATH + "?h=" + hash + "&uid=" + CROWDIN_USER_LOGIN

    if link.length > 2000 then
      print "Link is too long"
    else
      link
    end
  end

  private

  def get_user_data
    user_data = JSON.generate(
      :user_id => "12345678901",                                   # user identifier in your system
      :login  => "johndoe",                                        # login name, should match the pattern [0-9,a-z]
      :user_email => "john.doe@mail.com",                          # valid email address
      :display_name => "John Doe",                                 # real name (optional)
      :locale => "en_US",                                          # valid locale (optional)
      :languages => "uk,ro,fr",                                     # crowdin language codes
      :role => 1,                                                  # 0 - translator, 1 - proofreader
      :gender => 1,                                                # 1 - male, 2 - female
      :projects => "docx-project,csv-project",                     # comma separated list of your projects that user should have access to
      :expiration => Time.now.to_i + (20 * 60),                    # unix timestamp
      :redirect_to => "https://crowdin.net/project/docx-project"   # where the signed in user should be redirected to
    )

    user_data
  end

  def encrypt(parameters)
    key = CROWDIN_USER_API_KEY[0, 16]
    iv = CROWDIN_USER_API_KEY[16, 16]

    encryptor = OpenSSL::Cipher.new('AES-128-CBC')
    encryptor.encrypt
    encryptor.key = key
    encryptor.iv = iv

    encrypted_data = encryptor.update(parameters) + encryptor.final

    encrypted_data = CGI.escape(Base64.encode64(encrypted_data))
  end
end
