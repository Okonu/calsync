import 'package:hive_flutter/hive_flutter.dart';

class HiveConfig {
  static const String userBox = 'user_box';
  static const String settingsBox = 'settings_box';
  static const String cacheBox = 'cache_box';

  static Future<void> init() async {
    // Register adapters here if needed
    // Hive.registerAdapter(UserAdapter());

    // Open boxes
    await Hive.openBox(userBox);
    await Hive.openBox(settingsBox);
    await Hive.openBox(cacheBox);
  }

  static Box getUserBox() => Hive.box(userBox);
  static Box getSettingsBox() => Hive.box(settingsBox);
  static Box getCacheBox() => Hive.box(cacheBox);

  static Future<void> clearAllData() async {
    await getUserBox().clear();
    await getSettingsBox().clear();
    await getCacheBox().clear();
  }
}