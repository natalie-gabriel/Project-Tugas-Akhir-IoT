#include <ESP8266WiFi.h>
#include <PubSubClient.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include "DHT.h"

// --- KONFIGURASI ---
const char* ssid = "Redmi 13";        
const char* password = "password123";    

// MQTT Server
const char* mqtt_server = "x2.revolusi-it.com";
const int mqtt_port = 1883;
const char* mqtt_user = "usm";
const char* mqtt_pass = "usmjaya25";

// Identitas (SESUAIKAN NIM)
const char* NIM_MHS = "G.231.23.0050"; 
const char* mqtt_topic = "iot/G.231.23.0050"; 

// Pin Definisi
#define DHTPIN D5
#define DHTTYPE DHT11
#define LED1 D6
#define LED2 D7
#define LED3 D8

// Inisialisasi Objek
DHT dht(DHTPIN, DHTTYPE);
LiquidCrystal_I2C lcd(0x27, 16, 2); // Cek alamat I2C (biasanya 0x27 atau 0x3F)
WiFiClient espClient;
PubSubClient client(espClient);

unsigned long lastMsg = 0;
#define MSG_BUFFER_SIZE  (150)
char msg[MSG_BUFFER_SIZE];

void setup_wifi() {
  delay(10);
  lcd.clear();
  lcd.setCursor(0,0);
  lcd.print("Konek WiFi...");
  
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
  }
  lcd.setCursor(0,1);
  lcd.print("WiFi OK!");
  delay(1000);
}

void callback(char* topic, byte* payload, unsigned int length) {
  String message;
  for (int i = 0; i < length; i++) {
    message += (char)payload[i];
  }
  
  // LOGIKA LED (ACTIVE LOW / HIGH SESUAIKAN)
  // Kalau pakai LED biasa: ON=HIGH
  if (message == "LED1=1") digitalWrite(LED1, HIGH);
  if (message == "LED1=0") digitalWrite(LED1, LOW);
  
  if (message == "LED2=1") digitalWrite(LED2, HIGH);
  if (message == "LED2=0") digitalWrite(LED2, LOW);
  
  if (message == "LED3=1") digitalWrite(LED3, HIGH);
  if (message == "LED3=0") digitalWrite(LED3, LOW);
}

void reconnect() {
  while (!client.connected()) {
    lcd.clear();
    lcd.setCursor(0,0);
    lcd.print("Konek MQTT...");
    
    if (client.connect(NIM_MHS, mqtt_user, mqtt_pass)) {
      lcd.setCursor(0,1);
      lcd.print("MQTT OK!");
      client.subscribe(mqtt_topic);
      delay(1000);
      lcd.clear();
    } else {
      lcd.setCursor(0,1);
      lcd.print("Gagal, Retrying");
      delay(2000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  
  // Siapkan Pin LED
  pinMode(LED1, OUTPUT);
  pinMode(LED2, OUTPUT);
  pinMode(LED3, OUTPUT);
  digitalWrite(LED1, LOW);
  digitalWrite(LED2, LOW);
  digitalWrite(LED3, LOW);

  // Siapkan LCD
  Wire.begin(D2, D1); // SDA=D2, SCL=D1
  lcd.init();
  lcd.backlight();
  
  // Tampilan Awal
  lcd.setCursor(0, 0);
  lcd.print("Project IoT");
  lcd.setCursor(0, 1);
  lcd.print(NIM_MHS);
  delay(2000);

  dht.begin();
  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  unsigned long now = millis();
  if (now - lastMsg > 2000) { 
    lastMsg = now;
    
    float h = dht.readHumidity();
    float t = dht.readTemperature();

    if (isnan(h) || isnan(t)) {
      lcd.setCursor(0,0);
      lcd.print("Sensor Error!");
      return;
    }

    // --- TAMPILKAN DI LCD ---
    lcd.setCursor(0, 0);
    lcd.print("Suhu  : ");
    lcd.print(t, 1); // 1 angka belakang koma
    lcd.print((char)223); // Simbol derajat
    lcd.print("C");

    lcd.setCursor(0, 1);
    lcd.print("Lembab: ");
    lcd.print(h, 0); // Bulatkan
    lcd.print(" %   ");

    // --- KIRIM KE MQTT ---
    snprintf(msg, MSG_BUFFER_SIZE, "{\"nim\":\"%s\", \"t\":%.1f, \"h\":%.1f}", NIM_MHS, t, h);
    client.publish(mqtt_topic, msg);
  }
}