/*
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

package example;

import com.google.gson.Gson;
import sun.misc.BASE64Encoder;
import java.net.URLEncoder;
import java.util.Calendar;
import java.util.Date;
import javax.crypto.Cipher;
import javax.crypto.spec.SecretKeySpec;
import java.util.HashMap;
import javax.crypto.spec.IvParameterSpec;

public class Test {
  public static void main(String[] args) throws Exception {
    String basepath = "https://crowdin.com/join";
    String owner_login = " -- OWNERS LOGIN -- ";
    String api_key = " -- OWNERS API KEY -- ";
    String[] projects = new String[] {"docx-project", "csv-project"};
    
    String hash = encrypt(get_user_data(projects, false), api_key);
    String link = String.format("%s?h=%s&uid=%s", basepath, hash, owner_login);
    
    if(link.length() > 2000) {
      throw new Exception("Link is too long.");
    }
  }

  private static HashMap get_user_data(String[] projects, boolean registered) {
    HashMap<String, String> data = new HashMap<String, String>();

    StringBuilder projects_str = new StringBuilder();
    for (int i = 0; i < projects.length; i++) {
      projects_str.append(projects[i]);

      if(i != projects.length - 1) {
        projects_str.append(",");
      }
    }

    Calendar cal = Calendar.getInstance();
    cal.setTime(new Date());
    cal.add(Calendar.MINUTE, 20);

    data.put("user_id", "12345678901");
    data.put("login", "johndoe");
    data.put("user_email", "john.doe@mail.com");
    data.put("display_name", "John Doe");
    data.put("locale", "en_US");
    data.put("gender", "1");
    data.put("projects", projects_str.toString());
    data.put("expiration", new Long(cal.getTime().getTime()/1000).toString());
    data.put("languages", "uk,ro,fr");
    data.put("role", "0");
    data.put("redirect_to", "https://crowdin.com/project/docx-project");

    return data;
  }

  private static String encrypt(HashMap<String, String> data, String api_key) throws Exception {
    String vi  = api_key.substring(16, 32);
    api_key = api_key.substring(0, 16);
    byte[] json = new Gson().toJson(data).getBytes("UTF-8");

    SecretKeySpec key = new SecretKeySpec(api_key.getBytes("UTF-8"), "AES");

    Cipher cipher = Cipher.getInstance("AES/CBC/PKCS5Padding");
    cipher.init(Cipher.ENCRYPT_MODE, key, new IvParameterSpec(vi.getBytes("UTF-8")));

    byte[] cipherText = new byte[cipher.getOutputSize(json.length)];

    int ctLength = cipher.update(json, 0, json.length, cipherText, 0);
    ctLength += cipher.doFinal(cipherText, ctLength);

    BASE64Encoder encoder = new BASE64Encoder();
    String base64 = new String(encoder.encode(cipherText));

    return URLEncoder.encode(base64);
  }
}
