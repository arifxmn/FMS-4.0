//======================================== Including the libraries
#include <WiFi.h>
#include <HTTPClient.h>
#include <Arduino_JSON.h>
#include "DHT.h"
//======================================== 

//======================================== DHT sensor settings
#define DHTPIN 15 //--> Defines the Digital Pin connected to the DHT sensor
#define DHTTYPE DHT11 //--> Defines the type of DHT sensor used
DHT dht11_sensor(DHTPIN, DHTTYPE); //--> Initialize DHT sensor
//========================================

#define ON_Board_LED 2 

// Defines GPIO 13 as LED_1
#define LED_01 13 

// Defines GPIO 12 as LED_2
#define LED_02 12 

//======================================== SSID and Password of your WiFi
const char* ssid = "SSID";
const char* password = "PASSWORD";
//======================================== 

//========================================
String postData = ""; //--> Variables sent for HTTP POST request data
String payload = "";  //--> Variable for receiving response from HTTP POST
//======================================== 

//======================================== Variables for DHT sensor data.
float send_Temp;
int send_Humd;
String DHT_Sensor_Status = "";
//======================================== 

//________________________________________________________________________________ Subroutine to control LEDs after successfully fetching data from database
void control_LEDs() {
  Serial.println();
  Serial.println("----- Control LEDs ------");
  JSONVar myObject = JSON.parse(payload);

  // JSON.typeof(jsonVar) can be used to get the type of the var
  if (JSON.typeof(myObject) == "undefined") {
    Serial.println("Parsing input failed!");
    Serial.println("---------------");
    return;
  }

  if (myObject.hasOwnProperty("LED_01")) {
    Serial.print("myObject[\"LED_01\"] = ");
    Serial.println(myObject["LED_01"]);
  }

  if (myObject.hasOwnProperty("LED_02")) {
    Serial.print("myObject[\"LED_02\"] = ");
    Serial.println(myObject["LED_02"]);
  }

  if(strcmp(myObject["LED_01"], "ON") == 0)   {digitalWrite(LED_01, HIGH);  Serial.println("LED 01 ON"); }
  if(strcmp(myObject["LED_01"], "OFF") == 0)  {digitalWrite(LED_01, LOW);   Serial.println("LED 01 OFF");}
  if(strcmp(myObject["LED_02"], "ON") == 0)   {digitalWrite(LED_02, HIGH);  Serial.println("LED 02 ON"); }
  if(strcmp(myObject["LED_02"], "OFF") == 0)  {digitalWrite(LED_02, LOW);   Serial.println("LED 02 OFF");}

  Serial.println("-----------------------");
}
//________________________________________________________________________________ 

// ________________________________________________________________________________ Subroutine to read and get data from the DHT sensor.
void get_sensor_data() {
  Serial.println();
  Serial.println("----- Getting Sensor Data -----");
  
  // Read temperature as Celsius
  send_Temp = dht11_sensor.readTemperature();
  
  // Read Humidity
  send_Humd = dht11_sensor.readHumidity();
  
  // Check if any reads failed.
  if (isnan(send_Temp) || isnan(send_Humd)) {
    Serial.println("Failed to read from DHT sensor!");
    send_Temp = 0.00;
    send_Humd = 0;
    DHT_Sensor_Status = "FAILED";
  } else {
    DHT_Sensor_Status = "SUCCEED";
  }
  
  Serial.printf("Temperature : %.2f °C\n", send_Temp);
  Serial.printf("Humidity : %d %%\n", send_Humd);
  Serial.printf("DHT11 Status : %s\n", DHT_Sensor_Status);
  Serial.println("--------------------------------");
}

//________________________________________________________________________________

void setup() {
 
  Serial.begin(115200);

  pinMode(ON_Board_LED,OUTPUT);
  pinMode(LED_01,OUTPUT);
  pinMode(LED_02,OUTPUT);
  
  digitalWrite(ON_Board_LED, HIGH);
  digitalWrite(LED_01, HIGH); 
  digitalWrite(LED_02, HIGH);

  delay(2000);

  digitalWrite(ON_Board_LED, LOW); 
  digitalWrite(LED_01, LOW); 
  digitalWrite(LED_02, LOW);

  //---------------------------------------- Make WiFi on ESP32 in "STA/Station" mode and start connecting to WiFi
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  //---------------------------------------- 
  
  Serial.println();
  Serial.println("-------------");
  Serial.print("Connecting");

  int connecting_process_timed_out = 20; //--> 20 = 20 seconds.
  connecting_process_timed_out = connecting_process_timed_out * 2;
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    //........................................ Make the On Board Flashing LED on the process of connecting to the wifi
    digitalWrite(ON_Board_LED, HIGH);
    delay(250);
    digitalWrite(ON_Board_LED, LOW);
    delay(250);
    //........................................ 

    //........................................
    if(connecting_process_timed_out > 0) connecting_process_timed_out--;
    if(connecting_process_timed_out == 0) {
      delay(1000);
      ESP.restart();
    }
    //........................................ 
  }
  //---------------------------------------- 
  
  digitalWrite(ON_Board_LED, LOW);
  
  //---------------------------------------- 
  Serial.println();
  Serial.print("Successfully connected to : ");
  Serial.println(ssid);
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  Serial.println("-------------");
  //---------------------------------------- 

  // Setting up the DHT sensor (DHT11).
  dht11_sensor.begin();

  delay(2000);
}

//________________________________________________________________________________ 

void loop() {
  // put your main code here, to run repeatedly

  //----------------------------------------
  if(WiFi.status()== WL_CONNECTED) {
    HTTPClient http;  //--> Declare object of class HTTPClient.
    int httpCode;     //--> Variables for HTTP return code.
    
    //........................................ Process to get LEDs data from database to control LEDs.
    
    postData = "id=esp32_data";
    payload = "";
  
    digitalWrite(ON_Board_LED, HIGH);
    Serial.println();
    Serial.println("----- Getting Data -----");

    http.begin("http://SERVER_IP/getData.php");  //--> Specify request destination
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");  //--> Specify content-type header
   
    httpCode = http.POST(postData); //--> Send the request
    payload = http.getString();     //--> Get the response payload
  
    Serial.print("httpCode : ");
    Serial.println(httpCode); //--> Print HTTP return code
    Serial.print("payload  : ");
    Serial.println(payload);  //--> Print request response payload
    
    http.end();  //--> Close connection
    Serial.println("-----------------------");
    digitalWrite(ON_Board_LED, LOW);

    //........................................ 

    // Calls the control_LEDs() subroutine
    control_LEDs();
    
    delay(1000);

    // Calls the get_sensor_data() subroutine
    get_sensor_data();
  
    //........................................ The process of sending the sensor data & the state of the LEDs
    String LED_01_State = "";
    String LED_02_State = "";

    if (digitalRead(LED_01) == 1) LED_01_State = "ON";
    if (digitalRead(LED_01) == 0) LED_01_State = "OFF";
    if (digitalRead(LED_02) == 1) LED_02_State = "ON";
    if (digitalRead(LED_02) == 0) LED_02_State = "OFF";
    
    postData = "id=esp32_data";
    postData += "&temperature=" + String(send_Temp);
    postData += "&humidity=" + String(send_Humd);
    postData += "&sensor_status=" + DHT_Sensor_Status;
    postData += "&led_01=" + LED_01_State;
    postData += "&led_02=" + LED_02_State;
    payload = "";
  
    digitalWrite(ON_Board_LED, HIGH);
    Serial.println();
    Serial.println("----- Updating Data ------");
    http.begin("http://SERVER_IP/updateData.php");  //--> Specify request destination
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");  //--> Specify content-type header
   
    httpCode = http.POST(postData); //--> Send the request
    payload = http.getString();  //--> Get the response payload
  
    Serial.print("httpCode : ");
    Serial.println(httpCode); //--> Print HTTP return code
    Serial.print("payload  : ");
    Serial.println(payload);  //--> Print request response payload
    
    http.end();  //Close connection
    Serial.println("--------------------------");
    digitalWrite(ON_Board_LED, LOW);

    //........................................ 
    
    delay(4000);
  }
}