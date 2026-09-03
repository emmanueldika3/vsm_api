import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:vsm_app/provider/auth_provider.dart';

import 'package:vsm_app/widgets/vsm_app_bar.dart';
import 'package:vsm_app/widgets/MemberProfileCard.dart';
import 'package:vsm_app/widgets/custom_bottom_navigation_bar.dart';

// Écrans d'accueil par rôle
import 'package:vsm_app/screens/AdminDashboardScreen.dart';
import 'package:vsm_app/screens/CoachDashboardScreen.dart';
import 'package:vsm_app/screens/PlayerDashboardScreen.dart';
import 'package:vsm_app/screens/homeScreen.dart';

// Autres onglets
import 'package:vsm_app/screens/CotisationsScreen.dart';
import 'package:vsm_app/screens/annuaireScreen.dart';
import 'package:vsm_app/screens/matchScreen.dart';
import 'package:vsm_app/screens/galerieScreen.dart';

class MainLayout extends StatefulWidget {
  final int initialIndex;

  const MainLayout({super.key, this.initialIndex = 0});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> {
  late int _currentIndex;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
  }

  // Affiche le bon tableau de bord sur l'onglet 0 selon le rôle
  // 1. Méthode blindée pour mapper le rôle vers le bon écran
  Widget _getHomeWidgetByRole(dynamic rawRole) {
    if (rawRole == null) return const HomeScreen();

    // Convertit n'importe quel type (String, Enum, Map) en String propre
    final String roleStr = rawRole
        .toString()
        .split('.')
        .last
        .toLowerCase()
        .trim();

    // Détection par mots-clés (plus flexible qu'un switch exact)
    if (roleStr.contains('admin') || roleStr.contains('president')) {
      return const AdminDashboardScreen();
    }

    if (roleStr.contains('coach') ||
        roleStr.contains('encadreur') ||
        roleStr.contains('entraineur')) {
      return const CoachDashboardScreen();
    }

    if (roleStr.contains('player') ||
        roleStr.contains('joueur') ||
        roleStr.contains('veteran') ||
        roleStr.contains('treasurer') ||
        roleStr.contains('tresorier')) {
      return const PlayerDashboardScreen();
    }

    return const HomeScreen();
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final dynamic user = authProvider.user;

    // Récupération sécurisée du rôle
    final String userRole = user is Map
        ? (user['role']?.toString() ?? 'joueur')
        : (user?.role?.toString() ?? 'joueur');

    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F7),
      appBar: VsmAppBar(
        userRole: userRole,
        hasUnreadNotifications: true,
        onRefresh: () {},
        onNotificationPressed: () {},
        onSettingsPressed: () {},
        onLogoutPressed: () {},
      ),
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16.0, 16.0, 16.0, 8.0),
              child: MemberProfileCard(
                onViewFullProfile: () {
                  Navigator.of(context).pushNamed('/profile');
                },
                onEditPhoto: () {},
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0),
                child: IndexedStack(
                  index: _currentIndex,
                  children: [
                    _getHomeWidgetByRole(userRole),
                    const CotisationsScreen(),
                    const MatchsScreen(),
                    const AnnuaireScreen(),
                    const GalerieScreen(),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: CustomBottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
    );
  }
}
