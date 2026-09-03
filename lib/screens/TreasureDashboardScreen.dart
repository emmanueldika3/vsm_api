import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
// import 'package:vsm_app/provider/Treasure_dashboard_provider.dart';

class TreasurerDashboardScreen extends StatefulWidget {
  const TreasurerDashboardScreen({super.key});

  @override
  State<TreasurerDashboardScreen> createState() =>
      _TreasurerDashboardScreenState();
}

class _TreasurerDashboardScreenState extends State<TreasurerDashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      // Charger les données financières si besoin (bilan cotisations, caisse)
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
            "Tableau de bord - Trésorerie",
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E5235),
            ),
          ),
          const SizedBox(height: 16),

          // Contenu du tableau de bord Trésorier (Suivi des paiements, caisse, relances)
        ],
      ),
    );
  }
}
