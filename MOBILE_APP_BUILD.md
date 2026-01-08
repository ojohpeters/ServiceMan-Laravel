# ServiceMan Mobile App (Capacitor) - Build Guide

This guide explains how to build the Android APK for the ServiceMan mobile app using Capacitor.

## 📱 Overview

The mobile app is built using **Capacitor**, which wraps your existing React web app into a native Android application. The app connects to your Laravel backend API.

## 🚀 Prerequisites

### Required Software:
1. **Node.js** (v18+) - Already installed
2. **Java JDK 11+** - Required for Android development
   ```bash
   # Check if Java is installed
   java -version
   
   # Install on Ubuntu/Debian:
   sudo apt install openjdk-11-jdk
   ```

3. **Android Studio** - Download from https://developer.android.com/studio
   - Install Android SDK
   - Install Android SDK Build Tools
   - Install Android SDK Platform-Tools

4. **Set Android Environment Variables:**
   ```bash
   # Add to ~/.bashrc or ~/.zshrc
   export ANDROID_HOME=$HOME/Android/Sdk
   export PATH=$PATH:$ANDROID_HOME/emulator
   export PATH=$PATH:$ANDROID_HOME/platform-tools
   export PATH=$PATH:$ANDROID_HOME/tools
   export PATH=$PATH:$ANDROID_HOME/tools/bin
   ```

## 📦 Quick Start

### 1. Install Dependencies (if not already done)
```bash
npm install
```

### 2. Build Web Assets & Sync with Capacitor
```bash
npm run mobile:build
```

This command:
- Builds the React app with Vite
- Generates mobile-optimized `index.html`
- Syncs files to Android project

### 3. Open Android Studio
```bash
npm run cap:android
```

This opens the `android/` folder in Android Studio.

## 🔨 Building APK

### Option 1: Build APK in Android Studio (Recommended for first time)

1. **Open Android Studio:**
   ```bash
   npm run cap:android
   ```

2. **Wait for Gradle Sync:**
   - Android Studio will automatically sync Gradle dependencies
   - This may take a few minutes on first run

3. **Configure Signing (Release Build):**
   - Go to `Build > Generate Signed Bundle / APK`
   - Select "APK"
   - Create a new keystore or use existing
   - Fill in keystore details
   - Choose "release" build variant
   - Click "Finish"

4. **Locate APK:**
   - APK will be at: `android/app/release/app-release.apk`

### Option 2: Build APK from Command Line

#### Debug APK (for testing)
```bash
cd android
./gradlew assembleDebug
```
Output: `android/app/build/outputs/apk/debug/app-debug.apk`

#### Release APK (for distribution)
```bash
cd android
./gradlew assembleRelease
```
Output: `android/app/build/outputs/apk/release/app-release.apk`

⚠️ **Note:** Release APK requires signing configuration (see below)

## 🔐 APK Signing (Required for Release)

### Create Keystore (First Time Only)
```bash
keytool -genkey -v -keystore serviceman-release-key.jks \
  -keyalg RSA -keysize 2048 -validity 10000 \
  -alias serviceman
```

### Configure Signing in Android Project

1. **Create `android/key.properties`:**
   ```properties
   storePassword=your_keystore_password
   keyPassword=your_key_password
   keyAlias=serviceman
   storeFile=../serviceman-release-key.jks
   ```

2. **Update `android/app/build.gradle`:**
   ```gradle
   def keystoreProperties = new Properties()
   def keystorePropertiesFile = rootProject.file('key.properties')
   if (keystorePropertiesFile.exists()) {
       keystoreProperties.load(new FileInputStream(keystorePropertiesFile))
   }

   android {
       ...
       signingConfigs {
           release {
               keyAlias keystoreProperties['keyAlias']
               keyPassword keystoreProperties['keyPassword']
               storeFile keystoreProperties['storeFile'] ? file(keystoreProperties['storeFile']) : null
               storePassword keystoreProperties['storePassword']
           }
       }
       buildTypes {
           release {
               signingConfig signingConfigs.release
               minifyEnabled false
               proguardFiles getDefaultProguardFile('proguard-android.txt'), 'proguard-rules.pro'
           }
       }
   }
   ```

## ⚙️ Configuration

### API Base URL

The mobile app connects to your Laravel backend. Update the API URL in:

**File:** `resources/js/services/api.js`

```javascript
const getApiBaseURL = () => {
    if (Capacitor.isNativePlatform()) {
        return 'https://serviceman.sekimbi.com/api'; // Update this
    }
    return '/api';
};
```

### App Configuration

**File:** `capacitor.config.json`

```json
{
  "appId": "com.serviceman.app",
  "appName": "ServiceMan",
  "webDir": "public/build",
  "server": {
    "androidScheme": "https"
  }
}
```

## 📝 Available NPM Scripts

```bash
# Build web assets and sync with Capacitor
npm run mobile:build

# Build web assets only
npm run build

# Sync Capacitor (after manual changes)
npm run cap:sync

# Open Android Studio
npm run cap:android

# Copy web assets only
npm run cap:copy
```

## 🔄 Development Workflow

### 1. Make Changes to React Code
Edit files in `resources/js/`

### 2. Rebuild and Sync
```bash
npm run mobile:build
```

### 3. Test in Android Studio
```bash
npm run cap:android
# Then click "Run" in Android Studio
```

## 🌐 API Connection

### CORS Configuration

The Laravel backend is already configured to allow Capacitor requests. Check `config/cors.php`:

```php
'allowed_origins' => [
    'capacitor://localhost',
    'ionic://localhost',
    'http://localhost',
    // ...
],
```

### Network Security

For production, ensure your API uses HTTPS. The app is configured to use `https://` scheme.

## 📱 Testing the APK

### Install on Device

1. **Enable Developer Options** on Android device:
   - Go to Settings > About Phone
   - Tap "Build Number" 7 times

2. **Enable USB Debugging:**
   - Settings > Developer Options > USB Debugging

3. **Connect Device and Install:**
   ```bash
   adb install android/app/build/outputs/apk/debug/app-debug.apk
   ```

   Or transfer APK to device and install manually.

### Test on Emulator

1. Open Android Studio
2. Tools > Device Manager
3. Create Virtual Device
4. Run app from Android Studio

## 🐛 Troubleshooting

### Build Errors

1. **Gradle Sync Failed:**
   ```bash
   cd android
   ./gradlew clean
   ```

2. **Missing Dependencies:**
   ```bash
   cd android
   ./gradlew --refresh-dependencies
   ```

3. **Capacitor Sync Issues:**
   ```bash
   npm run cap:sync
   ```

### App Won't Connect to API

1. Check API URL in `resources/js/services/api.js`
2. Verify CORS settings in `config/cors.php`
3. Check device/emulator internet connection
4. Test API in browser first

### App Crashes on Launch

1. Check Android Studio Logcat for errors
2. Verify `index.html` exists in `public/build/`
3. Rebuild assets: `npm run mobile:build`

## 📦 Distribution

### Google Play Store

1. Build signed App Bundle:
   ```bash
   cd android
   ./gradlew bundleRelease
   ```
   Output: `android/app/build/outputs/bundle/release/app-release.aab`

2. Upload `app-release.aab` to Google Play Console

### Direct Distribution

Share the signed `app-release.apk` file directly to users.

⚠️ **Security Note:** Keep your keystore file (`*.jks`) secure and backed up. If lost, you cannot update your app on Google Play Store.

## 🔄 Updating the App

1. Make code changes
2. Update version in:
   - `android/app/build.gradle` (versionCode, versionName)
   - `capacitor.config.json` (optional)
3. Rebuild: `npm run mobile:build`
4. Build new APK

## 📚 Additional Resources

- Capacitor Docs: https://capacitorjs.com/docs
- Android Developer Guide: https://developer.android.com/
- Laravel API Documentation: Check `routes/api.php`

## ✅ Checklist Before Release

- [ ] API URL is correct and uses HTTPS
- [ ] App is tested on multiple Android versions
- [ ] All features work (login, requests, payments)
- [ ] Push notifications configured (if needed)
- [ ] App icon and splash screen customized
- [ ] Version number updated
- [ ] APK is signed
- [ ] Tested on real device

---

**Need Help?** Check Capacitor and Android Studio documentation or Laravel API routes.

