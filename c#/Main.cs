/*
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

using System;
using System.Security.Cryptography;
using System.Web.Script.Serialization;
using System.Web;
using System.Runtime.InteropServices;
using System.Text;
using System.Collections.Generic;
using System.IO;
using System.Diagnostics;

namespace test
{
  class MainClass
  {
    public static void Main (string[] args)
    {
      String basepath = "https://crowdin.net/join";
      String owner_login = " -- OWNERS LOGIN -- ";
      String api_key = " -- OWNERS API KEY -- ";
      string[] projects = new string[] {"docx-project", "csv-project"};
      String hash = encrypt(get_user_data(projects, false), api_key);
      String link = String.Format("{0}?h={1}&uid={2}", basepath, hash, owner_login);
      
      if (link.Length > 2000) {
	throw new Exception("Link is too long.");
      }
    }

    private static Dictionary<String, String> get_user_data (string[] projects, bool registered = false)
    {
      double a = (DateTime.Now.AddMinutes(20).ToUniversalTime().Ticks - 621355968000000000)/10000000;
      Dictionary<String, String> data = new Dictionary<String, String> {
	{"user_id", "12345678901"},
	{"login", "johndoe"},
	{"user_email", "john.doe@mail.com"},
	{"display_name", "John Doe"},
	{"locale", "en_US"},
	{"gender", "1"},
	{"projects", String.Join(",", projects)},
	{"expiration", ((DateTime.Now.AddMinutes(20).ToUniversalTime().Ticks - 621355968000000000)/10000000).ToString()},
	{"role", "0"},
        {"languages", "uk,ro,fr"},
	{"redirect_to", "https://crowdin.net/project/docx-project"}
      };

      return data;
    }

    private static String encrypt (Dictionary<String, String> data, String api_key)
    {
      JavaScriptSerializer serializer = new JavaScriptSerializer();
      String json_data = serializer.Serialize(data);

      String iv = api_key.Substring(16, 16);
      api_key = api_key.Substring(0, 16);

      byte[] data_bytes = Encoding.UTF8.GetBytes(json_data);
      byte[] api_bytes = Encoding.ASCII.GetBytes(api_key);
      byte[] iv_bytes = Encoding.ASCII.GetBytes(iv);

      RijndaelManaged AES = new RijndaelManaged();

      AES.Padding = PaddingMode.PKCS7;
      AES.Mode = CipherMode.CBC;
      AES.BlockSize = 128;
      AES.KeySize = 128;

      MemoryStream memStream = new MemoryStream();
      CryptoStream cryptoStream = new CryptoStream(memStream, AES.CreateEncryptor(api_bytes, iv_bytes), CryptoStreamMode.Write);
      cryptoStream.Write(data_bytes, 0, data_bytes.Length);
      cryptoStream.FlushFinalBlock();

      byte[] encryptedMessageBytes = new byte[memStream.Length];
      memStream.Position = 0;
      memStream.Read(encryptedMessageBytes, 0, encryptedMessageBytes.Length);

      string encryptedMessage = System.Convert.ToBase64String(encryptedMessageBytes);

      return HttpUtility.UrlEncode(encryptedMessage);
    }
  }
}
