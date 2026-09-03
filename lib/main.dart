// lib/main.dart

import 'package:flutter/material.dart';
import 'package:flutter_web_plugins/url_strategy.dart'; // 👈 Configuration URL PWA
import 'package:provider/provider.dart';

// Modèles et Services
import 'services/api_service.dart';
import 'services/dashboardService.dart';

// Providers
import 'provider/auth_provider.dart';
import 'provider/UserProvider.dart';
import 'provider/MatchProvider.dart';
import 'provider/event_provider.dart';
import 'provider/announcement_provider.dart';
import 'provider/player_dashboard_provider.dart';
import 'provider/admin_dashboard_provider.dart';

// Écrans
import 'screens/homeScreen.dart';

void main() async {
  // Initialisation des liaisons Flutter avant tout appel asynchrone
  WidgetsFlutterBinding.ensureInitialized();

  // Active la navigation Web par URL propre (sans le symbol # dans la barre d'adresse)
  usePathUrlStrategy();

  runApp(
    MultiProvider(
      providers: [
        // Injection de l'ApiService unique
        Provider<ApiService>(create: (_) => ApiService()),

        // State Management (Providers)
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => UserProvider()),
        ChangeNotifierProvider(create: (_) => MatchProvider()),
        ChangeNotifierProvider(create: (_) => EventProvider()),
        ChangeNotifierProvider(create: (_) => AnnouncementProvider()),
        ChangeNotifierProvider(create: (_) => PlayerDashboardProvider()),
        ChangeNotifierProvider(create: (_) => AdminDashboardProvider()),

        // Services
        ChangeNotifierProvider(create: (_) => DashboardService()),
      ],
      child: const VsmApp(),
    ),
  );
}

/// Convention Nom de classe en UpperCamelCase : VsmApp
class VsmApp extends StatelessWidget {
  const VsmApp({super.key});

  @override
  Widget build(BuildContext context) {
    // Charte Graphique VSM PK11 (Vert & Or)
    const Color greenPrimary = Color(0xFF1B5E20);
    const Color goldAccent = Color(0xFFFFD700);

    return MaterialApp(
      title: 'VSM PK11',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        primaryColor: greenPrimary,
        colorScheme: ColorScheme.fromSeed(
          seedColor: greenPrimary,
          primary: greenPrimary,
          secondary: goldAccent,
        ),
        scaffoldBackgroundColor: const Color(0xFFF5F5F5),
      ),
      home: const HomeScreen(),
    );
  }
}
