# Temperature-and-Humidity-with-ESP32-and-DHT11-using-GET-Requests
This task involves creating a system that uses an ESP32 microcontroller and a DHT11 sensor to measure temperature and humidity in the robot. The measured data is sent to a server using HTTP GET requests. The server-side PHP script receives the data and inserts it into a MySQL database.


**Hardware Components:**
1. ESP32 microcontroller
2. DHT11 temperature and humidity sensor




**Steps:**

**Hardware Setup:**
1. Connect the DHT11 sensor to the ESP32:
   - Connect the sensor's VCC pin to a 3.3V power source on the ESP32.
   - Connect the sensor's GND pin to the GND pin on the ESP32.
   - Connect the sensor's DATA pin to a digital pin (e.g., pin 21) on the ESP32.

**Software Setup:**

**1. PHP Script (Save_data.php):**
- This script is responsible for receiving GET requests and saving the data into a MySQL database.

```php
<?php
$conn = mysqli_connect("localhost", "root", '', "iot");

if (mysqli_connect_errno()) {
    die('Unable to connect to database ' . mysqli_connect_error());
}

$temperature = $_GET['temperature'];
$humidity = $_GET['humidity'];

$qry = $conn->prepare("INSERT INTO dht_sensor (temperature, humidity) VALUES (?, ?)");
$qry->bind_param("ss", $temperature, $humidity);

if ($qry->execute()) {
    echo "Operation Successful";
} else {
    echo "Operation Failed";
}

$qry->close();
$conn->close();
?>
```

**2. Arduino Code for ESP32:**
- This code reads the temperature and humidity from the DHT11 sensor and sends the data to the PHP script using HTTP GET requests.

```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include "DHT.h"

#define DHTPIN 21
#define DHTTYPE DHT11

const char ssid[] = "your_wifi_ssid";
const char password[] = "your_wifi_password";

String HOST_NAME = "http://your_server_ip_or_domain";
String PATH_NAME = "/path_to/Save_data.php";

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(115200);

  connectToWifi();

  dht.begin();
}

void loop() {
  delay(60000); // Send data every 60 seconds

  float humidity = dht.readHumidity();
  float temperature = dht.readTemperature();

  String strTemp = String(temperature, 2);
  String strHumid = String(humidity, 2);

  HTTPClient http;
  String server = HOST_NAME + PATH_NAME + "?temperature=" + strTemp + "&humidity=" + strHumid;
  http.begin(server);
  int httpCode = http.GET();

  if (httpCode > 0) {
    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println(payload);
    } else {
      Serial.printf("[HTTP] GET... code: %d\n", httpCode);
    }
  } else {
    Serial.printf("[HTTP] GET... failed, error: %s\n", http.errorToString(httpCode).c_str());
  }

  http.end();

  if (WiFi.status() != WL_CONNECTED) {
    connectToWifi();
  }
}

void connectToWifi() {
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
    WiFi.begin(ssid, password);

    if (connectingCounter > 8) {
      connectingCounter = 0;
      Serial.println(F("Unable to connect to WiFi"));
      Serial.println(F("Restarting ESP32"));
      ESP.restart();
    }
  }
  
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
}
```
