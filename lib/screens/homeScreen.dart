import 'dart:async';
import 'package:flutter/material.dart';
import 'package:vsm_app/auth/login_screen.dart';
// TODO: Décommentez et ajustez l'import vers votre écran de Tableau de Bord / Profil
// import 'package:vsm_app/screens/dashboard_screen.dart';

/// Service d'authentification réutilisable
class AuthService {
  // Remplacez cette méthode par votre vérification réelle (Token, SharedPreferences, etc.)
  static Future<bool> isLoggedIn() async {
    await Future.delayed(const Duration(milliseconds: 300));
    return false; // Passez à true pour tester la redirection d'un utilisateur connecté
  }
}

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  final PageController _pageController = PageController();
  int _currentPage = 0;
  Timer? _sliderTimer;

  // --- LOGIQUE DE REDIRECTION DYNAMIQUE ---
  bool _isAuthenticated = false;
  bool _isLoadingAuth = true;

  final List<Map<String, dynamic>> _slides = [
    {
      'title': 'Saison 2026/2027',
      'subtitle':
          'Retrouvez le calendrier officiel des matchs et entraînements.',
      'icon': Icons.sports_soccer,
      'badge': 'ACTUALITÉ',
    },
    {
      'title': 'Vie du Club & Caisse',
      'subtitle':
          'Suivez l\'état de la caisse et réglez vos cotisations en toute transparence.',
      'icon': Icons.account_balance_wallet_outlined,
      'badge': 'TRANSPARENCE',
    },
    {
      'title': 'Esprit VSM Douala',
      'subtitle': 'Santé, cohésion et passion du jeu au PK11.',
      'icon': Icons.groups_outlined,
      'badge': 'VALEURS',
    },
  ];

  @override
  void initState() {
    super.initState();

    // Vérification dynamique du statut d'authentification au démarrage
    _checkAuthStatus();

    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    );

    _fadeAnimation = CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOut,
    );

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.1),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOutCubic));

    _controller.forward();

    _sliderTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (_pageController.hasClients && mounted) {
        _currentPage = (_currentPage + 1) % _slides.length;
        _pageController.animateToPage(
          _currentPage,
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOutCubic,
        );
      }
    });
  }

  /// Vérifie le statut utilisateur de manière asynchrone
  Future<void> _checkAuthStatus() async {
    final loggedIn = await AuthService.isLoggedIn();
    if (mounted) {
      setState(() {
        _isAuthenticated = loggedIn;
        _isLoadingAuth = false;
      });
    }
  }

  /// Méthode de redirection dynamique au clic sur le bouton principal
  void _handleAuthNavigation() {
    if (_isAuthenticated) {
      // REDIRECTION SI CONNECTÉ : vers le Dashboard
      // Navigator.of(context).pushReplacement(
      //   MaterialPageRoute(builder: (context) => const DashboardScreen()),
      // );
    } else {
      // REDIRECTION SI NON CONNECTÉ : vers l'écran de Login
      Navigator.of(context)
          .push(MaterialPageRoute(builder: (context) => const LoginScreen()))
          .then((_) {
            // Rafraîchir l'état si l'utilisateur revient en arrière
            _checkAuthStatus();
          });
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _pageController.dispose();
    _sliderTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    const Color greenPrimary = Color(0xFF1E5235);
    const Color bordeauxRed = Color(0xFF6B1D2F);
    const Color goldAccent = Color(0xFFD4AF37);

    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [greenPrimary, Color(0xFF0A1E13)],
          ),
        ),
        child: SafeArea(
          child: FadeTransition(
            opacity: _fadeAnimation,
            child: SlideTransition(
              position: _slideAnimation,
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20.0,
                  vertical: 12.0,
                ),
                child: Column(
                  children: [
                    // --- 1. EN-TÊTE COMPACT (LOGO & TITRE) ---
                    Row(
                      children: [
                        Container(
                          width: 65,
                          height: 65,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(color: goldAccent, width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.3),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: ClipOval(
                            child: Image.asset(
                              'assets/images/logo_vsm.png',
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) {
                                return Container(
                                  color: Colors.white,
                                  child: const Icon(
                                    Icons.sports_soccer,
                                    size: 36,
                                    color: greenPrimary,
                                  ),
                                );
                              },
                            ),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'VÉTÉRANS SANTÉ MAHÈN',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                                  letterSpacing: 0.8,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Row(
                                children: const [
                                  Icon(
                                    Icons.location_on,
                                    color: goldAccent,
                                    size: 14,
                                  ),
                                  SizedBox(width: 4),
                                  Text(
                                    'DOUALA PK11',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                      color: goldAccent,
                                      letterSpacing: 1.1,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 16),

                    // --- 2. CONTENU PRINCIPAL : SLIDER DYNAMIQUE ---
                    Expanded(
                      flex: 5,
                      child: Column(
                        children: [
                          Expanded(
                            child: PageView.builder(
                              controller: _pageController,
                              onPageChanged: (index) {
                                setState(() {
                                  _currentPage = index;
                                });
                              },
                              itemCount: _slides.length,
                              itemBuilder: (context, index) {
                                final slide = _slides[index];
                                return Container(
                                  margin: const EdgeInsets.symmetric(
                                    horizontal: 4.0,
                                  ),
                                  padding: const EdgeInsets.all(20.0),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withOpacity(0.08),
                                    borderRadius: BorderRadius.circular(24),
                                    border: Border.all(
                                      color: goldAccent.withOpacity(0.35),
                                    ),
                                  ),
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 12,
                                          vertical: 4,
                                        ),
                                        decoration: BoxDecoration(
                                          color: goldAccent.withOpacity(0.2),
                                          borderRadius: BorderRadius.circular(
                                            12,
                                          ),
                                        ),
                                        child: Text(
                                          slide['badge'],
                                          style: const TextStyle(
                                            color: goldAccent,
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            letterSpacing: 1.2,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(height: 14),
                                      Icon(
                                        slide['icon'],
                                        size: 48,
                                        color: Colors.white,
                                      ),
                                      const SizedBox(height: 14),
                                      Text(
                                        slide['title'],
                                        textAlign: TextAlign.center,
                                        style: const TextStyle(
                                          fontSize: 20,
                                          fontWeight: FontWeight.bold,
                                          color: Colors.white,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      Text(
                                        slide['subtitle'],
                                        textAlign: TextAlign.center,
                                        style: TextStyle(
                                          fontSize: 13,
                                          color: Colors.white.withOpacity(0.8),
                                          height: 1.4,
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              },
                            ),
                          ),
                          const SizedBox(height: 10),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: List.generate(
                              _slides.length,
                              (index) => AnimatedContainer(
                                duration: const Duration(milliseconds: 300),
                                margin: const EdgeInsets.symmetric(
                                  horizontal: 4,
                                ),
                                width: _currentPage == index ? 20 : 6,
                                height: 6,
                                decoration: BoxDecoration(
                                  color: _currentPage == index
                                      ? goldAccent
                                      : Colors.white.withOpacity(0.3),
                                  borderRadius: BorderRadius.circular(3),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 14),

                    // --- 3. PETIT TABLEAU DE BORD (STATS COMPACTES) ---
                    Expanded(
                      flex: 3,
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildStatItem(
                              Icons.badge_outlined,
                              '45+',
                              'Membres',
                              goldAccent,
                            ),
                            VerticalDivider(
                              color: Colors.white.withOpacity(0.15),
                              width: 1,
                            ),
                            _buildStatItem(
                              Icons.emoji_events_outlined,
                              '100%',
                              'Engagement',
                              goldAccent,
                            ),
                            VerticalDivider(
                              color: Colors.white.withOpacity(0.15),
                              width: 1,
                            ),
                            _buildStatItem(
                              Icons.event_available,
                              '2x / sem',
                              'Matchs',
                              goldAccent,
                            ),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 16),

                    // --- 4. BARRE DE BOUTONS AVEC REDIRECTION DYNAMIQUE ---
                    Row(
                      children: [
                        Expanded(
                          child: SizedBox(
                            height: 48,
                            child: OutlinedButton(
                              onPressed: () {
                                if (_pageController.hasClients) {
                                  _pageController.nextPage(
                                    duration: const Duration(milliseconds: 500),
                                    curve: Curves.easeOut,
                                  );
                                }
                              },
                              style: OutlinedButton.styleFrom(
                                side: BorderSide(
                                  color: Colors.white.withOpacity(0.4),
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(24),
                                ),
                              ),
                              child: Text(
                                'Découvrir',
                                style: TextStyle(
                                  color: Colors.white.withOpacity(0.9),
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: SizedBox(
                            height: 48,
                            child: ElevatedButton(
                              onPressed: _isLoadingAuth
                                  ? null
                                  : _handleAuthNavigation,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: bordeauxRed,
                                foregroundColor: Colors.white,
                                elevation: 4,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(24),
                                  side: const BorderSide(
                                    color: goldAccent,
                                    width: 1,
                                  ),
                                ),
                              ),
                              child: _isLoadingAuth
                                  ? const SizedBox(
                                      width: 20,
                                      height: 20,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        color: goldAccent,
                                      ),
                                    )
                                  : Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          _isAuthenticated
                                              ? Icons.dashboard_rounded
                                              : Icons.login_rounded,
                                          size: 18,
                                          color: goldAccent,
                                        ),
                                        const SizedBox(width: 6),
                                        Text(
                                          _isAuthenticated
                                              ? 'Mon Espace'
                                              : 'Connexion',
                                          style: const TextStyle(
                                            fontSize: 13,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatItem(
    IconData icon,
    String value,
    String label,
    Color accentColor,
  ) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(icon, color: accentColor, size: 22),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
            fontSize: 15,
          ),
        ),
        Text(
          label,
          style: TextStyle(color: Colors.white.withOpacity(0.6), fontSize: 11),
        ),
      ],
    );
  }
}
