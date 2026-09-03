import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
// import 'package:vsm_app/provider/Coach_dashboard_provider.dart';

class CoachDashboardScreen extends StatefulWidget {
  const CoachDashboardScreen({super.key});

  @override
  State<CoachDashboardScreen> createState() => _CoachDashboardScreenState();
}

class _CoachDashboardScreenState extends State<CoachDashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      // Charger les données coach si besoin (effectifs, convocations, tactiques)
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
            "Tableau de bord - Encadrement Technique",
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E5235),
            ),
          ),
          const SizedBox(height: 16),

          // Contenu du tableau de bord Coach (Gestion de l'effectif, convocations)
        ],
      ),
    );
  }
}
