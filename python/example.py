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

# -*- coding: utf-8 -*-
import json
import base64, urllib, datetime
from time import mktime
from Crypto.Cipher import AES

def get_user_data(projects, registered):
    data = {
        'user_id': "12345678901",
        'login': "johndoe",
        'user_email': "john.doe@mail.com",
        'display_name': "John Doe",
        'locale': "en_US",
        'gender': 1,
        'projects': ",".join(projects),
        'expiration': mktime((datetime.datetime.now() + datetime.timedelta(minutes=20)).timetuple()),
        'languages': "uk,ro,fr",
        'role': 0,
        'redirect_to': "https://crowdin.com/project/docx-project"
    }

    return data

def encrypt(data, api_key):
    iv = api_key[16:32]
    api_key = api_key[0:16]
    data = json.dumps(data)
    length = 16 - (len(data) % 16)
    data += chr(length) * length

    encryptor = AES.new(api_key, AES.MODE_CBC, iv)
    d = encryptor.encrypt(data)

    base64enc = base64.b64encode(d)
    return urllib.pathname2url(base64enc)

basepath = "https://crowdin.com/join"
owner_login = " -- OWNERS LOGIN -- "
api_key = " -- OWNERS API KEY -- "
projects = ["docx-project", "csv-project"]
hash_part = encrypt(get_user_data(projects, False), api_key)
link = "%s?h=%s&uid=%s" % (basepath, hash_part, owner_login)

if len(link) > 2000:
    raise Exception("Link is too long.")
