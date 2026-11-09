#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

const char* ssid = "untan";
const char* password = "";
const char* deviceApiToken = "replace-with-order-device-token";
const String edgeServerAddress = "http://192.168.1.50:8000";
const String cloudServerAddress = "https://your-cloud-domain.example";
String activeServerAddress;

const int PIN_KOPI = 14;
const int PIN_GULA = 12;
const int PIN_SUSU = 13;
const int PIN_AIR = 27;
const int PIN_TRIG = 5;
const int PIN_ECHO = 18;
const int PIN_BUZZER = 19;

LiquidCrystal_I2C lcd(0x27, 16, 2);
bool isProcessing = false;

void beep(int duration, int repetitions) {
  for (int index = 0; index < repetitions; index++) {
    digitalWrite(PIN_BUZZER, HIGH);
    delay(duration);
    digitalWrite(PIN_BUZZER, LOW);

    if (repetitions > 1) {
      delay(duration);
    }
  }
}

float checkDistance() {
  digitalWrite(PIN_TRIG, LOW);
  delayMicroseconds(2);
  digitalWrite(PIN_TRIG, HIGH);
  delayMicroseconds(10);
  digitalWrite(PIN_TRIG, LOW);

  long duration = pulseIn(PIN_ECHO, HIGH, 30000);
  return duration == 0 ? 999 : duration * 0.034 / 2;
}

bool sendStatusToServer(const String& baseAddress, int orderId, const char* status) {
  if (WiFi.status() != WL_CONNECTED) {
    return false;
  }

  HTTPClient http;
  String endpoint = baseAddress + "/api/update-status/" + String(orderId);
  http.begin(endpoint);
  http.setTimeout(10000);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  http.addHeader("Authorization", String("Bearer ") + deviceApiToken);

  int responseCode = http.POST(String("status=") + status);
  http.end();

  Serial.printf("Status order %d -> %s via %s (HTTP %d)\n",
                orderId, status, baseAddress.c_str(), responseCode);
  return responseCode >= 200 && responseCode < 300;
}

bool updateStatus(int orderId, const char* status) {
  if (sendStatusToServer(activeServerAddress, orderId, status)) {
    return true;
  }

  if (activeServerAddress != cloudServerAddress) {
    Serial.println("Status update via active node failed; trying cloud.");
    return sendStatusToServer(cloudServerAddress, orderId, status);
  }

  return false;
}

bool fetchNextOrder(DynamicJsonDocument& document) {
  const String candidates[] = {edgeServerAddress, cloudServerAddress};

  for (const String& baseAddress : candidates) {
    HTTPClient http;
    http.begin(baseAddress + "/api/next-order");
    http.setTimeout(10000);
    http.addHeader("Authorization", String("Bearer ") + deviceApiToken);

    int responseCode = http.GET();
    if (responseCode == HTTP_CODE_OK) {
      String payload = http.getString();
      document.clear();
      DeserializationError error = deserializeJson(document, payload);

      if (!error && document.is<JsonArray>()) {
        activeServerAddress = baseAddress;
        if (baseAddress == cloudServerAddress) {
          Serial.println("Edge unavailable; using cloud fallback.");
        }
        http.end();
        return true;
      }

      if (error) {
        Serial.printf("JSON error from %s: %s\n", baseAddress.c_str(), error.c_str());
      } else {
        Serial.printf("Unexpected response from %s; trying next node.\n", baseAddress.c_str());
      }
    } else {
      Serial.printf("GET %s/api/next-order failed: HTTP %d\n",
                    baseAddress.c_str(), responseCode);
    }

    http.end();
  }

  return false;
}

void executePump(int pin, int seconds, const char* item) {
  if (seconds <= 0) {
    return;
  }

  Serial.printf("Pouring %s for %d seconds...\n", item, seconds);
  lcd.setCursor(0, 1);
  lcd.print("Mixing: ");
  lcd.print(item);
  lcd.print("       ");

  digitalWrite(pin, LOW);
  for (int index = 0; index < seconds * 10; index++) {
    delay(100);
    yield();
  }
  digitalWrite(pin, HIGH);
  delay(300);
}

bool connectWifi() {
  if (WiFi.status() == WL_CONNECTED) {
    return true;
  }

  lcd.clear();
  lcd.print("Connecting WiFi");
  WiFi.begin(ssid, password);

  for (int attempt = 0; attempt < 30 && WiFi.status() != WL_CONNECTED; attempt++) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  return WiFi.status() == WL_CONNECTED;
}

void waitForCup(const String& customer) {
  while (checkDistance() > 10.0) {
    lcd.clear();
    lcd.print("Order For:");
    lcd.setCursor(0, 1);
    lcd.print(customer.substring(0, 16));
    beep(50, 1);
    delay(1000);

    if (checkDistance() <= 10.0) {
      break;
    }

    lcd.clear();
    lcd.print("PLEASE PLACE");
    lcd.setCursor(0, 1);
    lcd.print("YOUR CUP NOW!");
    delay(1000);
    yield();
  }
}

void setup() {
  Serial.begin(115200);
  lcd.init();
  lcd.backlight();

  pinMode(PIN_KOPI, OUTPUT);
  pinMode(PIN_GULA, OUTPUT);
  pinMode(PIN_SUSU, OUTPUT);
  pinMode(PIN_AIR, OUTPUT);
  pinMode(PIN_TRIG, OUTPUT);
  pinMode(PIN_ECHO, INPUT);
  pinMode(PIN_BUZZER, OUTPUT);

  digitalWrite(PIN_KOPI, HIGH);
  digitalWrite(PIN_GULA, HIGH);
  digitalWrite(PIN_SUSU, HIGH);
  digitalWrite(PIN_AIR, HIGH);

  beep(100, 2);
  lcd.setCursor(0, 0);
  lcd.print("Booting Up...");

  if (connectWifi()) {
    beep(500, 1);
    lcd.clear();
    lcd.print("WiFi Connected!");
    delay(1500);
  }

  lcd.clear();
  lcd.print("Ready to Brew!");
}

void loop() {
  if (!connectWifi() || isProcessing) {
    delay(1000);
    return;
  }

  DynamicJsonDocument document(2048);
  if (fetchNextOrder(document)) {
    if (document.size() > 0) {
      JsonObject order = document[0].as<JsonObject>();
      int orderId = order["id"] | 0;
      String customer = order["user_name"] | "Customer";
      int coffeeSeconds = order["pump_kopi"] | 0;
      int sugarSeconds = order["pump_gula"] | 0;
      int milkSeconds = order["pump_susu"] | 0;
      int waterSeconds = order["pump_air"] | 0;
      float finishTime = order["wfq_finish_time"] | 0.0;

      if (orderId > 0) {
        isProcessing = true;

        Serial.println("\n=============================================");
        Serial.println("  ORDER DIPILIH SERVER DENGAN WFQ");
        Serial.println("=============================================");
        Serial.printf("Order ID: %d | Pelanggan: %s\n", orderId, customer.c_str());
        Serial.printf("WFQ finish time: %.2f\n", finishTime);
        Serial.println("ESP32 tidak menghitung WFQ; hanya mengeksekusi order.\n");

        beep(100, 3);
        waitForCup(customer);
        updateStatus(orderId, "proses");
        beep(600, 1);

        lcd.clear();
        lcd.print("Brewing...");
        lcd.setCursor(0, 1);
        lcd.print(customer.substring(0, 16));

        executePump(PIN_KOPI, coffeeSeconds, "Kopi");
        executePump(PIN_GULA, sugarSeconds, "Gula");
        executePump(PIN_SUSU, milkSeconds, "Susu");
        executePump(PIN_AIR, waterSeconds, "Air");

        bool statusUpdated = updateStatus(orderId, "success");
        Serial.println(statusUpdated ? "Order selesai dan status dikirim." : "Gagal mengirim status selesai.");

        lcd.clear();
        lcd.print("DONE! For:");
        lcd.setCursor(0, 1);
        lcd.print(customer.substring(0, 16));

        Serial.println("Menunggu gelas diambil...");
        while (checkDistance() < 10.0) {
          digitalWrite(PIN_BUZZER, HIGH);
          delay(200);
          digitalWrite(PIN_BUZZER, LOW);
          delay(200);
          yield();
        }

        beep(100, 2);
        lcd.clear();
        lcd.print("Thank You!");
        lcd.setCursor(0, 1);
        lcd.print("Enjoy your coffee");
        delay(3000);

        isProcessing = false;
        lcd.clear();
        lcd.print("Ready to Brew!");
      }
    }
  } else {
    Serial.println("Both edge and cloud are unavailable.");
  }

  delay(15000);
}
