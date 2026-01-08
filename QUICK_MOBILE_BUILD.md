# Quick Mobile App Build Guide

## ✅ Setup Complete!

Your Capacitor mobile app is ready. Here's how to build the APK:

## 🚀 Quick Steps to Build APK

### 1. Install Prerequisites

**Java JDK:**
```bash
java -version  # Check if installed
# If not: sudo apt install openjdk-11-jdk
```

**Android Studio:**
- Download from: https://developer.android.com/studio
- Install Android SDK during setup

### 2. Build and Sync

```bash
npm run mobile:build
```

This builds the web app and syncs with Android project.

### 3. Open in Android Studio

```bash
npm run cap:android
```

### 4. Build APK in Android Studio

1. Wait for Gradle sync to complete
2. Click **Build > Build Bundle(s) / APK(s) > Build APK(s)**
3. Wait for build to finish
4. Click **locate** to find your APK

**APK Location:** `android/app/build/outputs/apk/debug/app-debug.apk`

## 📦 Build Release APK (Signed)

For distribution, you need a signed APK:

1. **Create Keystore:**
   ```bash
   keytool -genkey -v -keystore serviceman-release-key.jks \
     -keyalg RSA -keysize 2048 -validity 10000 \
     -alias serviceman
   ```

2. **Configure in Android Studio:**
   - Build > Generate Signed Bundle / APK
   - Select APK
   - Choose your keystore
   - Select "release" build variant

## 📝 Important Files

- **API Config:** `resources/js/services/api.js` (update base URL if needed)
- **Capacitor Config:** `capacitor.config.json`
- **Build Script:** `scripts/generate-mobile-index.js`
- **Full Guide:** See `MOBILE_APP_BUILD.md`

## 🔧 Troubleshooting

**Gradle Sync Failed:**
```bash
cd android && ./gradlew clean
```

**Rebuild Everything:**
```bash
npm run mobile:build
npm run cap:sync
```

**API Connection Issues:**
- Check API URL in `resources/js/services/api.js`
- Ensure Laravel CORS allows Capacitor origins (already configured)

## 📱 Test on Device

1. Enable USB Debugging on Android device
2. Connect via USB
3. Install APK: `adb install android/app/build/outputs/apk/debug/app-debug.apk`

Or transfer APK to device and install manually.

---

**Your mobile app is ready!** 🎉

