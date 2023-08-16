# Temperature-and-Humidity-with-ESP32-and-DHT11-using-GET-Requests
The objective of this task is to develop a temperature and humidity measuring system for the robot utilizing an ESP32 microcontroller and a DHT11 sensor. The collected data is transmitted to a server through HTTP GET requests, and a server-side PHP script receives and records the data into a MySQL database.

### Steps:

#### 1. Hardware Setup:
- ESP32 microcontroller
- DHT11 temperature and humidity sensor
- Then, Connect the DHT11 sensor to the ESP32:
   - Connect the sensor's VCC pin to a 3.3V power source on the ESP32.
   - Connect the sensor's GND pin to the GND pin on the ESP32.
   - Connect the sensor's DATA pin to a digital pin (e.g., pin 21) on the ESP32.
  
![1](https://github.com/LatifahAbuhamamah/Temperature-and-Humidity-with-ESP32-and-DHT11-using-GET-Requests/blob/main/imagesss/Dht11-1.jpg)

![2](https://github.com/LatifahAbuhamamah/Temperature-and-Humidity-with-ESP32-and-DHT11-using-GET-Requests/blob/main/imagesss/Dht11-2.jpg)

#### 2. Software Setup:


 2.1 Arduino Code for ESP32:
*This code reads the temperature and humidity from the DHT11 sensor and sends the data to the PHP script using HTTP GET requests.*

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include "DHT.h"

#define DHTPIN 21
#define DHTTYPE DHT11

const char ssid[] = "xxxxxxxx";
const char password[] = "xxxxxxx";

String HOST_NAME = "http://192.168.0.222"; 
String PATH_NAME = "/IOT/Save_data.php?";
// String queryString = "temperature=45&humidity=20";

byte connectingCounter = 0;

DHT dht(DHTPIN, DHTTYPE);

void setup()
{
  Serial.begin(115200);

  connectToWifi();

  dht.begin();
}

void loop()
{

  delay(60000); // send data every 60 sec

  float humidity = dht.readHumidity();
  float temperature = dht.readTemperature();

  String strTemp = String(temperature, 2);
  String strHumid = String(humidity, 2);

  HTTPClient http;
  String server = HOST_NAME + PATH_NAME + "temperature=" + strTemp + "&humidity=" + strHumid;
  http.begin(server); // HTTP
  int httpCode = http.GET();

  if (httpCode > 0)
  {
    // file found at server
    if (httpCode == HTTP_CODE_OK)
    {
      String payload = http.getString();
      Serial.println(payload);
    }
    else
    {
      // HTTP header has been send and Server response header has been handled
      Serial.printf("[HTTP] GET... code: %d\n", httpCode);
    }
  }
  else
  {
    Serial.printf("[HTTP] GET... failed, error: %s\n", http.errorToString(httpCode).c_str());
  }

  http.end();

  if (WiFi.status() != WL_CONNECTED)
  {
    connectToWifi();
  }
}

void connectToWifi()
{
  WiFi.begin(ssid, password);
  Serial.print("Connecting to Wifi");
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(1000);
    Serial.print(".");
    connectingCounter++;
    WiFi.begin(ssid, password);
    if (connectingCounter > 8)
    {
      connectingCounter = 0;
      Serial.println(F("Unable to connect to the Wifi"));
      Serial.println(F("Restarting ESP32"));
      ESP.restart();
    }
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
}
```
- The code includes the necessary libraries for Wi-Fi, HTTP communication, and interfacing with the DHT11 sensor.
- The DHT11 sensor is initialized and configured to read temperature and humidity data.
- The ESP32 connects to the Wi-Fi network using the provided SSID and password.
- Inside the loop, the code reads temperature and humidity data from the DHT11 sensor.
- The data is converted into strings with a specified number of decimal places.
- An HTTPClient is used to create an HTTP GET request with the temperature and humidity data as parameters.
- The request is sent to the specified server and path.
- If the server responds with a valid HTTP status code, the payload (response content) is printed to the Serial Monitor.
- If there is a connection error, an error message is printed to the Serial Monitor.
- If the Wi-Fi connection is lost, the ESP32 attempts to reconnect.


 2.2 PHP Script (Save_data.php):
*This script is responsible for receiving GET requests and saving the data into a MySQL database*.

```php
<?php

$conn = mysqli_connect("localhost", "root", '', "iot");

if (mysqli_connect_errno()) {
    die('Unable to connect to database ' . mysqli_connect_error());
}

$temperature = $_GET['temperature'];
$humidity = $_GET['humidity'];

$qry = $conn->prepare("INSERT INTO dht_sensor (temperature, humidity) VALUES ('" . $temperature . "','" . $humidity . "')");

If ($qry->execute()) {
    echo "Operation Successful";
    } else {
    echo "Operation Failed";
    }
?>
```
- The PHP script connects to a MySQL database to establish a connection for data storage.
- It receives temperature and humidity data through HTTP GET requests.
- The received data is sanitized and prepared for insertion into the database.
- An SQL query is prepared to insert the data into the designated table.
- If the data insertion is successful, the script echoes "Operation Successful." Otherwise, it echoes "Operation Failed."

![3](https://github.com/LatifahAbuhamamah/Temperature-and-Humidity-with-ESP32-and-DHT11-using-GET-Requests/blob/main/imagesss/URL.png)

![4](https://github.com/LatifahAbuhamamah/Temperature-and-Humidity-with-ESP32-and-DHT11-using-GET-Requests/blob/main/imagesss/database.png)
