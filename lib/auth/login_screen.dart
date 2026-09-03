import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:vsm_app/provider/auth_provider.dart';
import 'package:vsm_app/provider/announcement_provider.dart';
import 'package:vsm_app/screens/PlayerDashboardScreen.dart';
import 'package:vsm_app/screens/AdminDashboardScreen.dart';
import 'package:vsm_app/widgets/Main_Layout.dart';
import 'package:vsm_app/provider/admin_dashboard_provider.dart';
// import 'package:vsm_app/screens/TreasurerDashboardScreen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _isPasswordVisible = false;

  // Couleurs de la charte VSM
  static const Color greenPrimary = Color(0xFF1E5235);
  static const Color greenDark = Color(0xFF0A1E13);
  static const Color bordeauxRed = Color(0xFF6B1D2F);
  static const Color goldAccent = Color(0xFFD4AF37);

  @override
  void dispose() {
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  // --- LOGIQUE DE CONNEXION AVEC REDIRECTION PAR RÔLE ---
  void _handleLogin() async {
    // Masque le clavier virtuel
    FocusScope.of(context).unfocus();

    if (_formKey.currentState!.validate()) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);

      // 1. Envoi des identifiants au provider
      final bool success = await authProvider.login(
        phone: _phoneController.text.trim(),
        password: _passwordController.text.trim(),
      );

      // Vérification de sécurité du BuildContext après l'attente asynchrone
      if (!mounted) return;

      final messenger = ScaffoldMessenger.of(context);

      if (success) {
        // 🔑 Transmission du token au AnnouncementProvider
        final announcementProvider = Provider.of<AnnouncementProvider>(
          context,
          listen: false,
        );
        announcementProvider.setToken(authProvider.token);

        // (Optionnel) Recharger les annonces avec les droits de l'utilisateur connecté
        announcementProvider.fetchAnnouncements();

        // 2. Notification visuelle de succès
        messenger.showSnackBar(
          SnackBar(
            backgroundColor: greenPrimary,
            content: Text(
              'Connexion réussie ! Bienvenue ${authProvider.user?.fullName ?? 'au VSM'}.',
            ),
            duration: const Duration(seconds: 2),
          ),
        );

        // 3. Redirection unique vers MainLayout (il gèrera le rôle en interne)
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (context) => const MainLayout()),
        );
      } else {
        // Gestion et affichage de l'erreur
        final errorMsg =
            authProvider.errorMessage ?? 'Identifiants incorrects.';

        messenger.showSnackBar(
          SnackBar(
            backgroundColor: bordeauxRed,
            content: Row(
              children: [
                const Icon(Icons.error_outline, color: goldAccent),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    errorMsg,
                    style: const TextStyle(color: Colors.white),
                  ),
                ),
              ],
            ),
          ),
        );
      }
    }
  }

  // Méthode de routage selon le rôle
  // Méthode de routage selon le rôle avec injection du Provider pour l'Admin
  void _redirectUser() {
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (context) => const MainLayout()),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final isLoading = authProvider.isLoading;

    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [greenPrimary, greenDark],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(
                horizontal: 24.0,
                vertical: 16.0,
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // --- 1. BOUTON RETOUR & EN-TÊTE ---
                  Align(
                    alignment: Alignment.centerLeft,
                    child: IconButton(
                      icon: const Icon(
                        Icons.arrow_back_ios_new,
                        color: Colors.white,
                      ),
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                  ),

                  // Logo VSM
                  Container(
                    width: 90,
                    height: 90,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: goldAccent, width: 2),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.3),
                          blurRadius: 12,
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
                              size: 48,
                              color: greenPrimary,
                            ),
                          );
                        },
                      ),
                    ),
                  ),

                  const SizedBox(height: 16),

                  const Text(
                    'ESPACE MEMBRES',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                      letterSpacing: 1.2,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Vétérans Santé Mahèn - Douala PK11',
                    style: TextStyle(
                      fontSize: 13,
                      color: goldAccent,
                      fontWeight: FontWeight.w500,
                    ),
                  ),

                  const SizedBox(height: 32),

                  // --- 2. FORMULAIRE DE CONNEXION ---
                  Container(
                    padding: const EdgeInsets.all(20.0),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.06),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: goldAccent.withOpacity(0.3)),
                    ),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Champ Téléphone
                          const Text(
                            'Numéro de téléphone',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 8),
                          TextFormField(
                            controller: _phoneController,
                            keyboardType: TextInputType.phone,
                            enabled: !isLoading,
                            style: const TextStyle(color: Colors.white),
                            decoration: InputDecoration(
                              hintText: '6XX XX XX XX',
                              hintStyle: TextStyle(
                                color: Colors.white.withOpacity(0.4),
                              ),
                              prefixIcon: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 14,
                                ),
                                child: const Text(
                                  '+237 ',
                                  style: TextStyle(
                                    color: goldAccent,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 15,
                                  ),
                                ),
                              ),
                              filled: true,
                              fillColor: Colors.black.withOpacity(0.2),
                              contentPadding: const EdgeInsets.symmetric(
                                vertical: 16,
                              ),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: Colors.white.withOpacity(0.2),
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: Colors.white.withOpacity(0.2),
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: const BorderSide(
                                  color: goldAccent,
                                  width: 1.5,
                                ),
                              ),
                            ),
                            validator: (value) {
                              if (value == null || value.trim().isEmpty) {
                                return 'Veuillez entrer votre numéro de téléphone';
                              }
                              if (value.trim().length < 8) {
                                return 'Numéro de téléphone invalide';
                              }
                              return null;
                            },
                          ),

                          const SizedBox(height: 18),

                          // Champ Mot de passe
                          const Text(
                            'Mot de passe',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 8),
                          TextFormField(
                            controller: _passwordController,
                            obscureText: !_isPasswordVisible,
                            enabled: !isLoading,
                            style: const TextStyle(color: Colors.white),
                            decoration: InputDecoration(
                              hintText: '••••••••',
                              hintStyle: TextStyle(
                                color: Colors.white.withOpacity(0.4),
                              ),
                              prefixIcon: const Icon(
                                Icons.lock_outline,
                                color: goldAccent,
                              ),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _isPasswordVisible
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  color: Colors.white.withOpacity(0.6),
                                ),
                                onPressed: () {
                                  setState(() {
                                    _isPasswordVisible = !_isPasswordVisible;
                                  });
                                },
                              ),
                              filled: true,
                              fillColor: Colors.black.withOpacity(0.2),
                              contentPadding: const EdgeInsets.symmetric(
                                vertical: 16,
                              ),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: Colors.white.withOpacity(0.2),
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: BorderSide(
                                  color: Colors.white.withOpacity(0.2),
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(16),
                                borderSide: const BorderSide(
                                  color: goldAccent,
                                  width: 1.5,
                                ),
                              ),
                            ),
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Veuillez entrer votre mot de passe';
                              }
                              return null;
                            },
                          ),

                          const SizedBox(height: 10),

                          // Lien Mot de passe oublié
                          Align(
                            alignment: Alignment.centerRight,
                            child: TextButton(
                              onPressed: isLoading
                                  ? null
                                  : () => _showHelpBottomSheet(context),
                              style: TextButton.styleFrom(
                                padding: EdgeInsets.zero,
                                minimumSize: const Size(50, 30),
                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              ),
                              child: Text(
                                'Mot de passe oublié ?',
                                style: TextStyle(
                                  color: Colors.white.withOpacity(0.8),
                                  fontSize: 12,
                                  decoration: TextDecoration.underline,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 24),

                          // Bouton de Connexion
                          SizedBox(
                            width: double.infinity,
                            height: 52,
                            child: ElevatedButton(
                              onPressed: isLoading ? null : _handleLogin,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: bordeauxRed,
                                foregroundColor: Colors.white,
                                elevation: 3,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                  side: const BorderSide(
                                    color: goldAccent,
                                    width: 1,
                                  ),
                                ),
                              ),
                              child: isLoading
                                  ? const SizedBox(
                                      width: 22,
                                      height: 22,
                                      child: CircularProgressIndicator(
                                        color: goldAccent,
                                        strokeWidth: 2.5,
                                      ),
                                    )
                                  : const Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          Icons.login,
                                          size: 20,
                                          color: goldAccent,
                                        ),
                                        SizedBox(width: 8),
                                        Text(
                                          'SE CONNECTER',
                                          style: TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.bold,
                                            letterSpacing: 1.0,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  // --- 3. DEMANDE DE COMPTE / SECRETARIAT ---
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Pas encore de compte membre ? ',
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.7),
                          fontSize: 13,
                        ),
                      ),
                      GestureDetector(
                        onTap: isLoading
                            ? null
                            : () => _showHelpBottomSheet(context),
                        child: const Text(
                          'Contacter le bureau',
                          style: TextStyle(
                            color: goldAccent,
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            decoration: TextDecoration.underline,
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
    );
  }

  // Modal d'assistance / Contact Secrétariat
  void _showHelpBottomSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: greenDark,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        return Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.3),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Assistance & Inscription',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                'L\'accès à l\'Espace Membre est strictement réservé aux joueurs et dirigeants enregistrés au VSM PK11.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.white.withOpacity(0.8),
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 20),
              ListTile(
                leading: const CircleAvatar(
                  backgroundColor: goldAccent,
                  child: Icon(Icons.support_agent, color: greenDark),
                ),
                title: const Text(
                  'Secrétariat VSM',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                subtitle: const Text(
                  'Pour création de compte ou réinitialisation',
                  style: TextStyle(color: Colors.white60, fontSize: 12),
                ),
                onTap: () {
                  Navigator.pop(context);
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text(
                        'Ouverture du contact WhatsApp du Secrétariat...',
                      ),
                    ),
                  );
                },
              ),
              const SizedBox(height: 12),
            ],
          ),
        );
      },
    );
  }
}
