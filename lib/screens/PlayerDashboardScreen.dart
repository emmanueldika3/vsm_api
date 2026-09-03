import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class PlayerDashboardScreen extends StatefulWidget {
  const PlayerDashboardScreen({super.key});

  @override
  State<PlayerDashboardScreen> createState() => _PlayerDashboardScreenState();
}

class _PlayerDashboardScreenState extends State<PlayerDashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      // Charger les données joueur si besoin (convocations, cotisations perso)
    });
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            "Espace Joueur",
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E5235),
            ),
          ),
          const SizedBox(height: 16),

          // Contenu du tableau de bord Joueur (Match à venir, présence, statut cotisation)
        ],
      ),
    );
  }
}
